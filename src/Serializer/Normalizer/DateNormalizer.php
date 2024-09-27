<?php

namespace App\Serializer\Normalizer;

use DateTime;
use App\Entity\Chimpokodex;
use App\Entity\Chimpokomon;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DateNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private NormalizerInterface $normalizer,
        private UrlGeneratorInterface $router
    ) {}

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        $data["date"]["server"] = new DateTime();

        $data["_links"] = [];
        $data["_links"]["self"] = $this->router->generate('app_' . strtolower((new \ReflectionClass($object))->getShortName()) . "s/" . 'getAll', [], 0);
        $data["_links"]["getAll"] = $this->router->generate('app_' . strtolower((new \ReflectionClass($object))->getShortName()) . "s/" . 'get', ["id" => $object->getId()], 0);
        $data["_links"]["create"] = $this->router->generate('app_' . strtolower((new \ReflectionClass($object))->getShortName()) . "s/" . 'create', [], 0);
        $data["_links"]["update"] = $this->router->generate('app_' . strtolower((new \ReflectionClass($object))->getShortName()) . "s/" . 'update', ["id" => $object->getId()], 0);
        $data["_links"]["delete"] = $this->router->generate('app_' . strtolower((new \ReflectionClass($object))->getShortName()) . "s/" . 'delete', ["id" => $object->getId()], 0);

        return $data;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        // TODO: return $data instanceof Object
        return $data instanceof Chimpokodex || $data instanceof Chimpokomon;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Chimpokodex::class => true, Chimpokomon::class => true];
    }
}
