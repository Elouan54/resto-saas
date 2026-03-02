<?php

namespace App\Controller;

use App\Entity\Restaurant;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/restaurants', name: 'api_restaurant_')]
class RestaurantController extends AbstractController
{
    private $em;
    private $repo;

    public function __construct(EntityManagerInterface $em, RestaurantRepository $repo)
    {
        $this->em = $em;
        $this->repo = $repo;
    }

    // LISTE tous les restaurants
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $restaurants = $this->repo->findAll();
        $data = [];

        foreach ($restaurants as $r) {
            $data[] = [
                'id' => $r->getId(),
                'name' => $r->getName(),
                'slug' => $r->getSlug(),
                'address' => $r->getAddress(),
                'isActive' => $r->isActive(),
                'createdAt' => $r->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }

        return $this->json($data);
    }

    // CRÉER un restaurant
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $restaurant = new Restaurant();
        $restaurant->setName($data['name']);
        $restaurant->setSlug($data['slug']);
        $restaurant->setAddress($data['address']);
        $restaurant->setPrimaryColor($data['primaryColor']);
        $restaurant->setIsActive(true);
        $restaurant->setCreatedAt(new \DateTime());
        $restaurant->setUpdateAt(new \DateTime());

        $this->em->persist($restaurant);
        $this->em->flush();

        return $this->json(['message' => 'Restaurant créé', 'id' => $restaurant->getId()]);
    }

    // AFFICHER un restaurant
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $restaurant = $this->repo->find($id);

        if (!$restaurant) {
            return $this->json(['message' => 'Restaurant non trouvé'], 404);
        }

        $data = [
            'id' => $restaurant->getId(),
            'name' => $restaurant->getName(),
            'slug' => $restaurant->getSlug(),
            'address' => $restaurant->getAddress(),
            'isActive' => $restaurant->isActive(),
            'createdAt' => $restaurant->getCreatedAt()->format('Y-m-d H:i:s')
        ];

        return $this->json($data);
    }

    // MODIFIER un restaurant
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $restaurant = $this->repo->find($id);

        if (!$restaurant) {
            return $this->json(['message' => 'Restaurant non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $restaurant->setName($data['name'] ?? $restaurant->getName());
        $restaurant->setSlug($data['slug'] ?? $restaurant->getSlug());
        $restaurant->setAddress($data['address'] ?? $restaurant->getAddress());
        $restaurant->setPrimaryColor($data['primaryColor'] ?? $restaurant->getPrimaryColor());
        $restaurant->setUpdateAt(new \DateTime());

        $this->em->flush();

        return $this->json(['message' => 'Restaurant mis à jour']);
    }

    // SUPPRIMER un restaurant
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $restaurant = $this->repo->find($id);

        if (!$restaurant) {
            return $this->json(['message' => 'Restaurant non trouvé'], 404);
        }

        $this->em->remove($restaurant);
        $this->em->flush();

        return $this->json(['message' => 'Restaurant supprimé']);
    }
}