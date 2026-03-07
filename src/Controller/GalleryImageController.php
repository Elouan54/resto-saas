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
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\FileUploader;

#[Route('/api/gallery-images', name:'api_gallery_')]
class GalleryImageController extends AbstractController
{
    private EntityManagerInterface $em;
    private GalleryImageRepository $repo;
    private RestaurantRepository $restaurantRepo;
    private FileUploader $galleryFileUploader;

    public function __construct(
        EntityManagerInterface $em,
        GalleryImageRepository $repo,
        RestaurantRepository $restaurantRepo,
        FileUploader $galleryFileUploader
    ) {
        $this->em = $em;
        $this->repo = $repo;
        $this->restaurantRepo = $restaurantRepo;
        $this->fileUploader = $galleryFileUploader;
    }

    #[Route('', name:'list', methods:['GET'])]
    public function list(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) return $this->json(['message' => 'Non authentifié'], 401);

        $images = $this->repo->findAll();
        $data = [];

        foreach($images as $img){
            if ($img->getRestaurant()->getOwner() !== $user) continue;

            $data[] = [
                'id' => $img->getId(),
                'imagePath' => $img->getImagePath(),
                'restaurantId' => $img->getRestaurant()->getId()
            ];
        }

        return $this->json($data);
    }

    #[Route('', name:'create', methods:['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) return $this->json(['message' => 'Non authentifié'], 401);

        $restaurantId = $request->request->get('restaurantId');
        $restaurant = $this->restaurantRepo->find($restaurantId);
        if (!$restaurant) return $this->json(['message'=>'Restaurant non trouvé'],404);

        $this->denyAccessUnlessGranted('RESOURCE_EDIT', $restaurant);

        /** @var UploadedFile $file */
        $file = $request->files->get('image');
        if (!$file) {
            return $this->json(['message' => 'Aucun fichier uploadé'], 400);
        }

        $fileName = $this->fileUploader->upload($file);
        $imagePath = '/uploads/gallery/' . $fileName;

        $img = new GalleryImage();
        $img->setImagePath($imagePath);
        $img->setRestaurant($restaurant);

        $this->em->persist($img);
        $this->em->flush();

        return $this->json([
            'message' => 'Image ajoutée',
            'id' => $img->getId(),
            'imagePath' => $img->getImagePath()
        ]);
    }

    #[Route('/{id}', name:'show', methods:['GET'])]
    public function show(int $id): JsonResponse
    {
        $img = $this->repo->find($id);
        if (!$img) return $this->json(['message'=>'Image non trouvée'],404);

        $this->denyAccessUnlessGranted('RESOURCE_VIEW', $img->getRestaurant());

        return $this->json([
            'id' => $img->getId(),
            'imagePath' => $img->getImagePath(),
            'restaurantId' => $img->getRestaurant()->getId()
        ]);
    }

    #[Route('/{id}', name:'update', methods:['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $img = $this->repo->find($id);
        if (!$img) return $this->json(['message'=>'Image non trouvée'],404);

        $this->denyAccessUnlessGranted('RESOURCE_EDIT', $img->getRestaurant());

        /** @var UploadedFile $file */
        $file = $request->files->get('image');
        if ($file) {
            $fileName = $this->fileUploader->upload($file);
            $img->setImagePath('/uploads/gallery/' . $fileName);
        }

        $this->em->flush();

        return $this->json(['message'=>'Image mise à jour','imagePath'=>$img->getImagePath()]);
    }

    #[Route('/{id}', name:'delete', methods:['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $img = $this->repo->find($id);
        if (!$img) return $this->json(['message'=>'Image non trouvée'],404);

        $this->denyAccessUnlessGranted('RESOURCE_DELETE', $img->getRestaurant());

        $this->em->remove($img);
        $this->em->flush();

        return $this->json(['message'=>'Image supprimée']);
    }
}