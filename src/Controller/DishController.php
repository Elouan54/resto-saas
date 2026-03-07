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
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\FileUploader;

#[Route('/api/dishes', name: 'api_dish_')]
class DishController extends AbstractController
{
    private $em;
    private $repo;
    private $categoryRepo;
    private $restaurantRepo;
    private FileUploader $fileUploader;

    public function __construct(
        EntityManagerInterface $em,
        DishRepository $repo,
        CategoryRepository $categoryRepo,
        RestaurantRepository $restaurantRepo,
        FileUploader $fileUploader
    ) {
        $this->em = $em;
        $this->repo = $repo;
        $this->categoryRepo = $categoryRepo;
        $this->restaurantRepo = $restaurantRepo;
        $this->fileUploader = $fileUploader;
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $user = $this->getUser();

        $dishes = $this->repo->createQueryBuilder('d')
            ->join('d.restaurant', 'r')
            ->where('r.owner = :owner')
            ->setParameter('owner', $user)
            ->getQuery()
            ->getResult();

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
        // Récupération des champs depuis multipart/form-data
        $name = $request->request->get('name');
        $description = $request->request->get('description');
        $price = (float) $request->request->get('price');
        $isAvailable = $request->request->get('isAvailable') ?? true;
        $categoryId = $request->request->get('categoryId');
        $restaurantId = $request->request->get('restaurantId');

        // Vérification restaurant
        $restaurant = $this->restaurantRepo->find($restaurantId);
        if (!$restaurant) {
            return $this->json(['message' => 'Restaurant non trouvé'], 404);
        }
        $this->denyAccessUnlessGranted('RESTAURANT_ACCESS', $restaurant);

        // Vérification catégorie
        $category = $this->categoryRepo->find($categoryId);
        if (!$category) {
            return $this->json(['message' => 'Catégorie non trouvée'], 404);
        }

        // Création plat
        $dish = new Dish();
        $dish->setName($name);
        $dish->setDescription($description);
        $dish->setPrice($price);
        $dish->setIsAvailable($isAvailable);
        $dish->setCategory($category);
        $dish->setRestaurant($restaurant);

        // Upload image si fournie
        /** @var UploadedFile $file */
        $file = $request->files->get('image');
        if ($file) {
            $fileName = $this->fileUploader->upload($file);
            $dish->setImage('/uploads/dishes/' . $fileName);
        }

        $this->em->persist($dish);
        $this->em->flush();

        return $this->json([
            'message' => 'Plat créé',
            'id' => $dish->getId(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $dish = $this->repo->find($id);

        if (!$dish) {
            return $this->json(['message' => 'Plat non trouvé'], 404);
        }

        $this->denyAccessUnlessGranted('RESTAURANT_ACCESS', $dish->getRestaurant());

        return $this->json([
            'id' => $dish->getId(),
            'name' => $dish->getName(),
            'description' => $dish->getDescription(),
            'price' => $dish->getPrice(),
            'isAvailable' => $dish->isAvailable(),
            'categoryId' => $dish->getCategory()->getId(),
            'restaurantId' => $dish->getRestaurant()->getId(),
            'image' => $dish->getImage()
        ]);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $dish = $this->repo->find($id);
        if (!$dish) {
            return $this->json(['message' => 'Plat non trouvé'], 404);
        }

        $this->denyAccessUnlessGranted('RESTAURANT_ACCESS', $dish->getRestaurant());

        // Récupération champs multipart/form-data
        $name = $request->request->get('name');
        $description = $request->request->get('description');
        $price = $request->request->get('price');
        $isAvailable = $request->request->get('isAvailable');
        $categoryId = $request->request->get('categoryId');

        if ($name) $dish->setName($name);
        if ($description) $dish->setDescription($description);
        if ($price !== null) $dish->setPrice((float) $price);
        if ($isAvailable !== null) $dish->setIsAvailable($isAvailable);

        if ($categoryId) {
            $category = $this->categoryRepo->find($categoryId);
            if ($category) {
                $dish->setCategory($category);
            }
        }

        /** @var UploadedFile $file */
        $file = $request->files->get('image');
        if ($file) {
            $fileName = $this->fileUploader->upload($file);
            $dish->setImage('/uploads/dishes/' . $fileName);
        }

        $this->em->flush();

        return $this->json(['message' => 'Plat mis à jour']);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $dish = $this->repo->find($id);

        if (!$dish) {
            return $this->json(['message' => 'Plat non trouvé'], 404);
        }

        $this->denyAccessUnlessGranted('RESTAURANT_ACCESS', $dish->getRestaurant());

        $this->em->remove($dish);
        $this->em->flush();

        return $this->json(['message' => 'Plat supprimé']);
    }
}