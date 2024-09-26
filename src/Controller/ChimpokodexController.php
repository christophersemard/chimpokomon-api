<?php

namespace App\Controller;

use App\Entity\Chimpokodex;
use App\Repository\ChimpokodexRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ChimpokomonRepository;
use PHPUnit\Util\Json;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ChimpokodexController extends AbstractController
{

  #[Route('/chimpokodexs', name: 'app_chimpokodex_getAll', methods: ['GET'])]
  public function getAllChimpokodex(
    ChimpokodexRepository $chimpokodexRepository,
    SerializerInterface $serializer,
    TagAwareCacheInterface $cache
  ): JsonResponse {

    $idCache = 'chimpokodex.all';
    $cachedChimpokos = $cache->get($idCache, function (ItemInterface $item) use ($chimpokodexRepository, $serializer): string {
      $item->tag('chimpokodexCache');
      $chimpokoList = $serializer->serialize($chimpokodexRepository->findAll(), 'json', ["groups" => "chimpokodex"]);
      return $chimpokoList;
    });

    // dd($cachedChimpokos);

    return new JsonResponse($cachedChimpokos, Response::HTTP_OK, [], true);
  }

  #[Route('/chimpokodexs/{chimpokodex}', name: 'app_chimpokodex_get', methods: ['GET'])]
  public function getChimpokodex(
    Chimpokodex $chimpokodex,
    SerializerInterface $serializer
  ): JsonResponse {
    $jsonChimpokodex = $serializer->serialize($chimpokodex, 'json', ["groups" => "chimpokodex"]);
    return new JsonResponse($jsonChimpokodex, Response::HTTP_OK, [], true);
  }

  #[Route('/chimpokodexs', name: 'app_chimpokodex_create', methods: ["POST"])]
  public function createChimpokodex(
    Request $request,
    EntityManagerInterface $entityManager,
    TagAwareCacheInterface $cache,
    SerializerInterface $serializer
  ): JsonResponse {
    $requestData = $request->toArray();
    $newChimpo = $serializer->deserialize($request->getContent(), Chimpokodex::class, 'json');

    $newChimpo->setStatus("on");

    $newChimpo->setIdDad(random_int(1, 151));
    $newChimpo->setIdMom(random_int(1, 151));
    $pvMin = random_int(1, 151);
    $newChimpo->setPvMin($pvMin);
    $newChimpo->setPvMax(random_int($pvMin, 151));

    $entityManager->persist($newChimpo);
    $entityManager->flush();

    $cache->invalidateTags(['chimpokodexCache']);

    $jsonChimpo = $serializer->serialize($newChimpo, 'json', ["groups" => "chimpokodex"]);
    return new JsonResponse($jsonChimpo, Response::HTTP_CREATED, [], true);
  }

  #[Route('/chimpokodexs/{chimpokodex}', name: 'app_chimpokodex_update', methods: ["PUT"])]
  public function updateChimpokodex(
    Request $request,
    Chimpokodex $chimpokodex,
    EntityManagerInterface $entityManager,
    TagAwareCacheInterface $cache,
    SerializerInterface $serializer
  ): JsonResponse {
    $requestData = $request->toArray();
    $chimpokodex = $serializer->deserialize($request->getContent(), Chimpokodex::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $chimpokodex]);

    // dd($chimpokodex);

    $entityManager->persist($chimpokodex);
    $entityManager->flush();

    $cache->invalidateTags(['chimpokodexCache']);

    $jsonChimpo = $serializer->serialize($chimpokodex, 'json', ["groups" => "chimpokodex"]);
    return new JsonResponse($jsonChimpo, Response::HTTP_OK, [], true);
  }

  #[Route('/chimpokodexs/{chimpokodex}', name: 'app_chimpokodex_delete', methods: ["DELETE"])]
  public function deleteChimpokodex(
    Chimpokodex $chimpokodex,
    EntityManagerInterface $entityManager,
    TagAwareCacheInterface $cache
  ): JsonResponse {
    $entityManager->remove($chimpokodex);
    $entityManager->flush();

    $cache->invalidateTags(['chimpokodexCache']);

    return new JsonResponse(null, Response::HTTP_NO_CONTENT);
  }
}
