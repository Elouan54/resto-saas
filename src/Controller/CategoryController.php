<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/categories', name: 'api_category_')]
class CategoryController extends AbstractController
{
    private $em;
    private $repo;
    private $restaurantRepo;

    public function __construct(EntityManagerInterface $em, CategoryRepository $repo, RestaurantRepository $restaurantRepo)
    {
        $this->em = $em;
        $this->repo = $repo;
        $this->restaurantRepo = $restaurantRepo;
    }

    // LISTE toutes les catégories
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $categories = $this->repo->findAll();
        $data = [];
        foreach ($categories as $c) {
            $data[] = [
                'id' => $c->getId(),
                'name' => $c->getName(),
                'restaurantId' => $c->getRestaurant()?->getId(), // sécurité si restaurant null
            ];
        }
        return $this->json($data);
    }

    // CRÉER une catégorie
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // sécuriser les clés
        $restaurantId = $data['restaurantId'] ?? null;
        $name = $data['name'] ?? null;

        if (!$restaurantId || !$name) {
            return $this->json(['message' => 'Champs "name" et "restaurantId" requis'], 400);
        }

        $restaurant = $this->restaurantRepo->find($restaurantId);
        if (!$restaurant) {
            return $this->json(['message' => 'Restaurant non trouvé'], 404);
        }

        $category = new Category();
        $category->setName($name);
        $category->setRestaurant($restaurant);

        $this->em->persist($category);
        $this->em->flush();

        return $this->json([
            'message' => 'Catégorie créée',
            'id' => $category->getId(),
            'name' => $category->getName(),
            'restaurantId' => $category->getRestaurant()->getId()
        ]);
    }

    // AFFICHER une catégorie
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $category = $this->repo->find($id);
        if (!$category) {
            return $this->json(['message' => 'Catégorie non trouvée'], 404);
        }

        return $this->json([
            'id' => $category->getId(),
            'name' => $category->getName(),
            'restaurantId' => $category->getRestaurant()?->getId(),
        ]);
    }

    // MODIFIER une catégorie
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $category = $this->repo->find($id);
        if (!$category) {
            return $this->json(['message' => 'Catégorie non trouvée'], 404);
        }

        $data = json_decode($request->getContent(), true);

        // mise à jour du nom
        if (isset($data['name'])) {
            $category->setName($data['name']);
        }

        // mise à jour du restaurant si fourni
        if (isset($data['restaurantId'])) {
            $restaurant = $this->restaurantRepo->find($data['restaurantId']);
            if ($restaurant) {
                $category->setRestaurant($restaurant);
            } else {
                return $this->json(['message' => 'Restaurant non trouvé'], 404);
            }
        }

        $this->em->flush();

        return $this->json(['message' => 'Catégorie mise à jour']);
    }

    // SUPPRIMER une catégorie
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $category = $this->repo->find($id);
        if (!$category) {
            return $this->json(['message' => 'Catégorie non trouvée'], 404);
        }

        $this->em->remove($category);
        $this->em->flush();

        return $this->json(['message' => 'Catégorie supprimée']);
    }
}