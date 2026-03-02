<?php

namespace App\Controller;

use App\Entity\Dish;
use App\Repository\DishRepository;
use App\Repository\CategoryRepository;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/dishes', name: 'api_dish_')]
class DishController extends AbstractController
{
    private $em;
    private $repo;
    private $categoryRepo;
    private $restaurantRepo;

    public function __construct(EntityManagerInterface $em, DishRepository $repo, CategoryRepository $categoryRepo, RestaurantRepository $restaurantRepo)
    {
        $this->em = $em;
        $this->repo = $repo;
        $this->categoryRepo = $categoryRepo;
        $this->restaurantRepo = $restaurantRepo;
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $dishes = $this->repo->findAll();
        $data = [];
        foreach ($dishes as $d) {
            $data[] = [
                'id' => $d->getId(),
                'name' => $d->getName(),
                'description' => $d->getDescription(),
                'price' => $d->getPrice(),
                'isAvailable' => $d->isAvailable(),
                'categoryId' => $d->getCategory()->getId(),
                'restaurantId' => $d->getRestaurant()->getId()
            ];
        }
        return $this->json($data);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $restaurant = $this->restaurantRepo->find($data['restaurantId']);
        if (!$restaurant) return $this->json(['message'=>'Restaurant non trouvé'], 404);

        $category = $this->categoryRepo->find($data['categoryId']);
        if (!$category) return $this->json(['message'=>'Catégorie non trouvée'], 404);

        $dish = new Dish();
        $dish->setName($data['name']);
        $dish->setDescription($data['description'] ?? null);
        $dish->setPrice($data['price']);
        $dish->setIsAvailable($data['isAvailable'] ?? true);
        $dish->setCategory($category);
        $dish->setRestaurant($restaurant);
        $dish->setImage($data['image'] ?? null);

        $this->em->persist($dish);
        $this->em->flush();

        return $this->json(['message'=>'Plat créé','id'=>$dish->getId()]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $dish = $this->repo->find($id);
        if (!$dish) return $this->json(['message'=>'Plat non trouvé'], 404);

        return $this->json([
            'id'=>$dish->getId(),
            'name'=>$dish->getName(),
            'description'=>$dish->getDescription(),
            'price'=>$dish->getPrice(),
            'isAvailable'=>$dish->isAvailable(),
            'categoryId'=>$dish->getCategory()->getId(),
            'restaurantId'=>$dish->getRestaurant()->getId(),
            'image'=>$dish->getImage()
        ]);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $dish = $this->repo->find($id);
        if (!$dish) return $this->json(['message'=>'Plat non trouvé'], 404);

        $data = json_decode($request->getContent(), true);

        $dish->setName($data['name'] ?? $dish->getName());
        $dish->setDescription($data['description'] ?? $dish->getDescription());
        $dish->setPrice($data['price'] ?? $dish->getPrice());
        $dish->setIsAvailable($data['isAvailable'] ?? $dish->isAvailable());
        $dish->setImage($data['image'] ?? $dish->getImage());

        if(isset($data['categoryId'])){
            $category = $this->categoryRepo->find($data['categoryId']);
            if($category) $dish->setCategory($category);
        }

        $this->em->flush();

        return $this->json(['message'=>'Plat mis à jour']);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $dish = $this->repo->find($id);
        if (!$dish) return $this->json(['message'=>'Plat non trouvé'], 404);

        $this->em->remove($dish);
        $this->em->flush();

        return $this->json(['message'=>'Plat supprimé']);
    }
}