<?php

namespace App\Serializer\Normalizer;

use App\Entity\Conversation;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ConversationNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private NormalizerInterface $normalizer
    ) {
    }

    /**
     * @param Conversation $object
     * @param string|null $format
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     * @throws ExceptionInterface
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        unset($data['createdAt']);
        unset($data['updatedAt']);

        $data['created_at'] = $object->getCreatedAt()->format('Y-m-d H-i-s');
        $data['updated_at'] = $object->getUpdatedAt()->format('Y-m-d H-i-s');

        return $data;
    }

    /**
     * @param mixed $data
     * @param string|null $format
     * @param array<string, mixed> $context
     * @return bool
     */
    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Conversation;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Conversation::class => true];
    }
}
