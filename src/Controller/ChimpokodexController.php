<?php

namespace App\Controller;

use PHPUnit\Util\Json;
use App\Entity\Chimpokodex;
use OpenApi\Annotations as OA;
use Lcobucci\JWT\Validation\Validator;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ChimpokodexRepository;
use App\Repository\ChimpokomonRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChimpokodexController extends AbstractController
{



  #[IsGranted("ROLE_ADMIN", statusCode: 403, message: "Access denied")]
  #[Route('/api/chimpokodexs', name: 'app_chimpokodex_getAll', methods: ['GET'])]
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

    return new JsonResponse($cachedChimpokos, Response::HTTP_OK, [], true);
  }

  #[Route('/api/chimpokodexs/{chimpokodex}', name: 'app_chimpokodex_get', methods: ['GET'])]
  public function getChimpokodex(
    Chimpokodex $chimpokodex,
    SerializerInterface $serializer
  ): JsonResponse {

    $jsonChimpokodex = $serializer->serialize($chimpokodex, 'json', ["groups" => "chimpokodex", DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s']);

    return new JsonResponse($jsonChimpokodex, Response::HTTP_OK, [], true);
  }

  /**
   * Create a new Chimpokodex
   *
   * @param Request $request
   * @param EntityManagerInterface $entityManager
   * @param TagAwareCacheInterface $cache
   * @param SerializerInterface $serializer
   * @param ValidatorInterface $validator
   * @return JsonResponse
   */
  #[OA\Response(
    response: 201,
    description: "Chimpokodex created",
    content: new Model(type: Chimpokodex::class, groups: ["chimpokodex"])
  )]

  #[Route('/api/chimpokodexs', name: 'app_chimpokodex_create', methods: ["POST"])]
  public function createChimpokodex(
    Request $request,
    EntityManagerInterface $entityManager,
    TagAwareCacheInterface $cache,
    SerializerInterface $serializer,
    ValidatorInterface $validator
  ): JsonResponse {
    $newChimpo = $serializer->deserialize($request->getContent(), Chimpokodex::class, 'json');
    $newChimpo->setStatus("on");

    $newChimpo->setIdDad(random_int(1, 151));
    $newChimpo->setIdMom(random_int(1, 151));
    $pvMin = random_int(1, 151);
    $newChimpo->setPvMin($pvMin);
    $newChimpo->setPvMax(random_int($pvMin, 151));

    $errors = $validator->validate($newChimpo);
    // dd($errors);
    if (count($errors) > 0) {
      $messages = [];
      foreach ($errors as $error) {
        $messages[] = $error->getMessage();
      }
      return new JsonResponse($serializer->serialize($messages, 'json'), Response::HTTP_BAD_REQUEST, [], true);
    }

    $entityManager->persist($newChimpo);
    $entityManager->flush();

    $cache->invalidateTags(['chimpokodexCache']);

    $jsonChimpo = $serializer->serialize($newChimpo, 'json', ["groups" => "chimpokodex"]);
    return new JsonResponse($jsonChimpo, Response::HTTP_CREATED, [], true);
  }

  #[Route('/api/chimpokodexs/{chimpokodex}', name: 'app_chimpokodex_update', methods: ["PUT"])]
  public function updateChimpokodex(
    Request $request,
    Chimpokodex $chimpokodex,
    EntityManagerInterface $entityManager,
    TagAwareCacheInterface $cache,
    SerializerInterface $serializer
  ): JsonResponse {
    $chimpokodex = $serializer->deserialize($request->getContent(), Chimpokodex::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $chimpokodex]);

    $entityManager->persist($chimpokodex);
    $entityManager->flush();

    $cache->invalidateTags(['chimpokodexCache']);

    $jsonChimpo = $serializer->serialize($chimpokodex, 'json', ["groups" => "chimpokodex"]);
    return new JsonResponse($jsonChimpo, Response::HTTP_OK, [], true);
  }

  #[Route('/api/chimpokodexs/{chimpokodex}', name: 'app_chimpokodex_delete', methods: ["DELETE"])]
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
