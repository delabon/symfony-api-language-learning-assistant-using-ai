<?php

namespace App\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Faker\Generator;
use ReflectionClass;
use RuntimeException;

use function Symfony\Component\String\u;

abstract class Factory implements FactoryInterface
{
    protected string $entityClass = '';

    public function __construct(protected readonly Generator $faker, protected readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return array<string, mixed>
     */
    abstract protected function defaults(): array;

    /**
     * @param object $object
     * @return string[]
     */
    protected function getClassProps(object $object): array
    {
        $reflectionClass = new ReflectionClass($object);
        $properties = $reflectionClass->getProperties();
        $props = [];

        foreach ($properties as $property) {
            $props[] = $property->getName();
        }

        return $props;
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    protected function overrideDefaults(array $overrides): array
    {
        $defaults = $this->defaults();

        foreach ($defaults as $key => $value) {
            $key = u($key)->camel()->toString();

            foreach ($overrides as $overrideKey => $overrideValue) {
                $overrideKey = u($overrideKey)->camel()->toString();

                if ($key === $overrideKey) {
                    $defaults[$key] = $overrideValue;
                }
            }
        }

        return $defaults;
    }

    /**
     * @param array<string, mixed> $overrides
     * @return object
     */
    public function make(array $overrides = []): object
    {
        if (!class_exists($this->entityClass)) {
            throw new RuntimeException('Entity class "' . $this->entityClass . '" does not exist.');
        }

        $object = new $this->entityClass;
        $props = $this->getClassProps($object);
        $data = $this->overrideDefaults($overrides);

        foreach ($data as $key => $value) {
            $key = u($key)->camel()->toString();

            if (in_array($key, $props)) {
                $method = 'set' . u($key)->camel()->toString();

                if (method_exists($object, $method)) {
                    $object->$method($value);
                }
            }
        }

        return $object;
    }

    /**
     * @param array<string, mixed> $overrides
     * @return object
     */
    public function create(array $overrides = []): object
    {
        $object = $this->make($overrides);
        $this->entityManager->persist($object);
        $this->entityManager->flush();

        return $object;
    }
}