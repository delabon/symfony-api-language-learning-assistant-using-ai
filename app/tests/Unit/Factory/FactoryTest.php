<?php

namespace App\Tests\Unit\Factory;

use App\Factory\Factory;
use App\Factory\FactoryInterface;
use App\Tests\Fake\FakeEntity;
use App\Tests\Fake\FakeEntityRepository;
use App\Tests\Fake\FakeFactory;
use App\Tests\Fake\FakeFactoryWithNonExistentEntity;
use App\Tests\UnitTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory as Faker;
use Faker\Generator;
use ReflectionClass;
use RuntimeException;

class FactoryTest extends UnitTestCase
{
    public function testMakesEntityCorrectly(): void
    {
        $factory = new FakeFactory(Faker::create(), $this->createStub(EntityManagerInterface::class));
        $entity = $factory->make();

        $this->assertInstanceOf(Factory::class, $factory);
        $this->assertInstanceOf(FactoryInterface::class, $factory);
        $this->assertInstanceOf(FakeEntity::class, $entity);
        $this->assertNull($entity->getId());
        $this->assertNotEmpty($entity->getName());
        $this->assertLessThanOrEqual(255, strlen($entity->getName()));
        $this->assertNotEmpty($entity->getApiKey());
        $this->assertLessThanOrEqual(255, strlen($entity->getApiKey()));
    }

    public function testMethodMakeTransformsSnakeCaseDefaultsToCamelCaseSuccessfully(): void
    {
        $factoryMock = $this->createPartialMock(FakeFactory::class, ['defaults']);
        $factoryMock->expects($this->once())
            ->method('defaults')
            ->willReturn([
                'name' => 'John Doe',
                'api_key' => str_repeat('a', 118),
            ]);
        $entity = $factoryMock->make();

        $this->assertInstanceOf(FakeEntity::class, $entity);
        $this->assertNull($entity->getId());
        $this->assertSame('John Doe', $entity->getName());
        $this->assertSame(str_repeat('a', 118), $entity->getApiKey());
    }

    public function testMethodMakeOverridesDefaultsCorrectly(): void
    {
        $factory = new FakeFactory(Faker::create(), $this->createStub(EntityManagerInterface::class));
        $entity = $factory->make([
            'id' => 123,
            'name' => 'Sami Kim',
            'apiKey' => str_repeat('k', 240),
        ]);

        $this->assertInstanceOf(FakeEntity::class, $entity);
        $this->assertNull($entity->getId());
        $this->assertSame('Sami Kim', $entity->getName());
        $this->assertSame(str_repeat('k', 240), $entity->getApiKey());
    }

    public function testMethodMakeTransformsSnakeCaseOverridesToCamelCaseSuccessfully(): void
    {
        $factory = new FakeFactory(Faker::create(), $this->createStub(EntityManagerInterface::class));
        $entity = $factory->make([
            'id' => 23423,
            'name' => 'Ahmed Tibar',
            'api_key' => str_repeat('e', 100),
        ]);

        $this->assertInstanceOf(FakeEntity::class, $entity);
        $this->assertNull($entity->getId());
        $this->assertSame('Ahmed Tibar', $entity->getName());
        $this->assertSame(str_repeat('e', 100), $entity->getApiKey());
    }

    public function testThrowsExceptionWhenEntityDoesNotExist(): void
    {
        $factory = new FakeFactoryWithNonExistentEntity(Faker::create(), $this->createStub(EntityManagerInterface::class));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Entity class "DoesNotExist" does not exist.');

        $factory->make();
    }

    public function testCreateMethodSuccessfullyCreatesAnEntityAndSaveItIntoDatabase(): void
    {
        $fakeEntity = new FakeEntity();
        $fakeEntity->setName('Ahmed Time');
        $fakeEntity->setApiKey(str_repeat('a', 118));

        // Mock the repository
        $fakeEntityRepository = $this->createMock(FakeEntityRepository::class);
        $fakeEntityRepository->expects($this->once())
            ->method('findAll')
            ->willReturnCallback(function () use ($fakeEntity) {
                $reflectionClass = new ReflectionClass($fakeEntity);
                $reflectionProperty = $reflectionClass->getProperty('id');
                $reflectionProperty->setValue($fakeEntity, 1); // Set the id to 123

                return [
                    $fakeEntity
                ];
            });

        // Mock the entity manager
        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->expects($this->once())
            ->method('getRepository')
            ->with(FakeEntity::class)
            ->willReturn($fakeEntityRepository);

        $entityManagerMock->expects($this->once())
            ->method('persist')
            ->with($fakeEntity)
            ->willReturnCallback(function ($entity) {
                $reflectionClass = new ReflectionClass($entity);
                $reflectionProperty = $reflectionClass->getProperty('id');
                $reflectionProperty->setValue($entity, 1); // Set the id to 123
            });

        $entityManagerMock->expects($this->once())
            ->method('flush');

        // Mock the faker to get similar data
        $fakerMock = $this->createPartialMock(Generator::class, ['format']);
        $fakerMock->expects($this->exactly(2))
            ->method('format')
            ->willReturnOnConsecutiveCalls($fakeEntity->getName(), $fakeEntity->getApiKey());

        $factory = new FakeFactory($fakerMock, $entityManagerMock);

        /** @var FakeEntity $entity */
        $entity = $factory->create();

        $entities = $entityManagerMock->getRepository(FakeEntity::class)->findAll();

        $this->assertCount(1, $entities);
        $this->assertInstanceOf(FakeEntity::class, $entities[0]);
        $this->assertEquals($entity, $entities[0]);
        $this->assertSame(1, $entity->getId());
    }

    public function testMethodMakeReturnsNullIdEventWhenDefaultsHasPositiveId(): void
    {
        $fakeFactoryMock = $this->createPartialMock(FakeFactory::class, ['defaults']);
        $fakeFactoryMock->method('defaults')
            ->willReturn([
                'id' => 123,
                'name' => 'Sara Doe',
                'apiKey' => str_repeat('a', 118),
            ]);

        /** @var FakeEntity $fakeEntity */
        $fakeEntity = $fakeFactoryMock->make();

        $this->assertNull($fakeEntity->getId());
        $this->assertSame('Sara Doe', $fakeEntity->getName());
        $this->assertSame(str_repeat('a', 118), $fakeEntity->getApiKey());
    }

    public function testMethodMakeReturnsNullIdEventWhenOverridesHasPositiveId(): void
    {
        $fakeFactory = new FakeFactory(Faker::create(), $this->createStub(EntityManagerInterface::class));

        /** @var FakeEntity $fakeEntity */
        $fakeEntity = $fakeFactory->make([
            'id' => 3423,
            'name' => 'Sara Doe',
            'apiKey' => str_repeat('a', 118),
        ]);

        $this->assertNull($fakeEntity->getId());
        $this->assertSame('Sara Doe', $fakeEntity->getName());
        $this->assertSame(str_repeat('a', 118), $fakeEntity->getApiKey());
    }

    public function testMethodCreateMustUseMethodMakeSuccessfully(): void
    {
        $fakeEntity = new FakeEntity();
        $fakeEntity->setName('John Doe');
        $fakeEntity->setApiKey(str_repeat('q', 118));

        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->expects($this->once())
            ->method('persist')
            ->with($fakeEntity)
            ->willReturnCallback(function ($fakeEntity) {
                $reflectionClass = new ReflectionClass($fakeEntity);
                $prop = $reflectionClass->getProperty('id');
                $prop->setValue($fakeEntity, 1);
            });
        $entityManagerMock->expects($this->once())
            ->method('flush');

        $fakeFactoryMock = $this->createPartialMock(FakeFactory::class, ['create', 'make']);
        $fakeFactoryMock->expects($this->once())
            ->method('make')
            ->with([])
            ->willReturn($fakeEntity);
        $fakeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturnCallback(function ($overrides) use ($fakeFactoryMock, $entityManagerMock) {
                $fakeEntity = $fakeFactoryMock->make($overrides);
                $entityManagerMock->persist($fakeEntity);
                $entityManagerMock->flush();

                return $fakeEntity;
            });

        $fakeFactoryMock->create();

        $this->assertSame(1, $fakeEntity->getId());
        $this->assertSame('John Doe', $fakeEntity->getName());
        $this->assertSame(str_repeat('q', 118), $fakeEntity->getApiKey());
    }
}
