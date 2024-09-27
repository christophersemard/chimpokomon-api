<?php

namespace App\Controller;

use App\Entity\Chimpokomon;
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

class ChimpokomonController extends AbstractController
{
    #[Route('/api/chimpokomons', name: 'app_chimpokomon_getAll', methods: ['GET'])]
    public function getAllChimpokomons(
        ChimpokomonRepository $chimpokomonRepository,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache
    ): JsonResponse {

        $idCache = 'chimpokomon.all';
        $cachedChimpokos = $cache->get($idCache, function (ItemInterface $item) use ($chimpokomonRepository, $serializer): string {
            $item->tag('chimpokomonCache');
            $chimpokoList = $serializer->serialize($chimpokomonRepository->findStatusOn(), 'json', ["groups" => "chimpokomon"]);
            return $chimpokoList;
        });


        return new JsonResponse($cachedChimpokos, Response::HTTP_OK, [], true);
        // return $this->json($jsonChimpokos);
    }


    #[Route('/api/chimpokomons/{chimpokomon}', name: 'app_chimpokomon_get', methods: ['GET'])]
    public function getChimpokomon(
        Chimpokomon $chimpokomon,
        SerializerInterface $serializer
    ): JsonResponse {
        $jsonChimpokomon = $serializer->serialize($chimpokomon, 'json', ["groups" => "chimpokomon"]);
        return new JsonResponse($jsonChimpokomon, Response::HTTP_OK, [], true);
    }

    #[Route('/api/chimpokomons', name: 'app_chimpokon_create', methods: ["POST"])]
    public function createChimpokomon(
        Request $request,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache,
        SerializerInterface $serializer,
        ChimpokodexRepository $chimpokodexRepository
    ): JsonResponse {
        $requestData = $request->toArray();
        $newChimpo = $serializer->deserialize($request->getContent(), Chimpokomon::class, 'json');
        $newChimpo->setStatus('on');
        $chimpokedex = $chimpokodexRepository->find($requestData['chimpokodex']);
        $newChimpo->setChimpokodex($chimpokedex);

        // check les pv max 
        if ($newChimpo->getPvMax() > $chimpokedex->getPvMax()) {
            $newChimpo->setPvMax($chimpokedex->getPvMax());
        }

        $entityManager->persist($newChimpo);
        $entityManager->flush();

        $cache->invalidateTags(['chimpokomonCache']);

        $location = $this->generateUrl('app_chimpokomon_get', ['chimpokomon' => $newChimpo->getId()], 0);

        $chimpoData = $serializer->serialize($newChimpo, 'json', ["groups" => "chimpokomon"]);

        // return $this->json($newChimpo);
        return new JsonResponse($chimpoData, Response::HTTP_CREATED, ["Location" => $location], true);
    }
    #[Route('/api/chimpokomons/{chimpokomon}', name: 'app_chimpokomon_update', methods: ['PUT', 'PATCH'])]
    public function updateChimpokomon(
        Chimpokomon $chimpokomon,
        Request $request,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache,
        SerializerInterface $serializer,
    ): JsonResponse {

        $requestData = $request->toArray();
        $newChimpo = $serializer->deserialize($request->getContent(), Chimpokomon::class, "json", [AbstractNormalizer::OBJECT_TO_POPULATE => $chimpokomon]);
        $newChimpo->setStatus('on');
        $chimpokodex = $newChimpo->getChimpokodex();
        if ($requestData["pvMax"] > $chimpokodex->getPvMax()) {
            $newChimpo->setPvMax($chimpokodex->getPvMax());
        }
        $entityManager->persist($newChimpo);
        $entityManager->flush();
        $cache->invalidateTags(['chimpokomonCache']);


        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }




    #[Route('/api/chimpokomons/{chimpokomon}', name: 'app_chimpokomon_delete', methods: ['DELETE'])]
    public function deleteChimpokomon(
        Chimpokomon $chimpokomon,
        EntityManagerInterface $entityManager,
        Request $request,
        TagAwareCacheInterface $cache
    ): JsonResponse {
        $force = $request->toArray()["force"] ?? false;
        if ($force) {
            $entityManager->remove($chimpokomon);
        } else {
            $chimpokomon->setStatus('off');
            $entityManager->persist($chimpokomon);
        }

        $entityManager->flush();

        $cache->invalidateTags(['chimpokomonCache']);


        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
