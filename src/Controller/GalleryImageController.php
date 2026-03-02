<?php

namespace App\Controller;

use App\Entity\GalleryImage;
use App\Repository\GalleryImageRepository;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/gallery-images', name:'api_gallery_')]
class GalleryImageController extends AbstractController
{
    private $em;
    private $repo;
    private $restaurantRepo;

    public function __construct(EntityManagerInterface $em, GalleryImageRepository $repo, RestaurantRepository $restaurantRepo)
    {
        $this->em = $em;
        $this->repo = $repo;
        $this->restaurantRepo = $restaurantRepo;
    }

    #[Route('', name:'list', methods:['GET'])]
    public function list(): JsonResponse
    {
        $images = $this->repo->findAll();
        $data = [];
        foreach($images as $img){
            $data[] = [
                'id'=>$img->getId(),
                'imagePath'=>$img->getImagePath(),
                'restaurantId'=>$img->getRestaurant()->getId()
            ];
        }
        return $this->json($data);
    }

    #[Route('', name:'create', methods:['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $restaurant = $this->restaurantRepo->find($data['restaurantId']);
        if(!$restaurant) return $this->json(['message'=>'Restaurant non trouvé'],404);

        $img = new GalleryImage();
        $img->setImagePath($data['imagePath']);
        $img->setRestaurant($restaurant);

        $this->em->persist($img);
        $this->em->flush();

        return $this->json(['message'=>'Image ajoutée','id'=>$img->getId()]);
    }

    #[Route('/{id}', name:'show', methods:['GET'])]
    public function show(int $id): JsonResponse
    {
        $img = $this->repo->find($id);
        if(!$img) return $this->json(['message'=>'Image non trouvée'],404);

        return $this->json([
            'id'=>$img->getId(),
            'imagePath'=>$img->getImagePath(),
            'restaurantId'=>$img->getRestaurant()->getId()
        ]);
    }

    #[Route('/{id}', name:'update', methods:['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $img = $this->repo->find($id);
        if(!$img) return $this->json(['message'=>'Image non trouvée'],404);

        $data = json_decode($request->getContent(), true);
        $img->setImagePath($data['imagePath'] ?? $img->getImagePath());

        $this->em->flush();
        return $this->json(['message'=>'Image mise à jour']);
    }

    #[Route('/{id}', name:'delete', methods:['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $img = $this->repo->find($id);
        if(!$img) return $this->json(['message'=>'Image non trouvée'],404);

        $this->em->remove($img);
        $this->em->flush();
        return $this->json(['message'=>'Image supprimée']);
    }
}