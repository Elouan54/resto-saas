<?php

namespace App\Controller;

use App\Entity\Restaurant;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/api/restaurants', name: 'api_restaurant_')]
class RestaurantController extends AbstractController
{
    private EntityManagerInterface $em;
    private RestaurantRepository $repo;
    private SluggerInterface $slugger;
    private FileUploader $fileUploader;

    public function __construct(
        EntityManagerInterface $em,
        RestaurantRepository $repo,
        SluggerInterface $slugger,
        FileUploader $fileUploader
    ) {
        $this->em = $em;
        $this->repo = $repo;
        $this->slugger = $slugger;
        $this->fileUploader = $fileUploader;
    }

    // LISTE tous les restaurants de l'utilisateur
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) return $this->json(['message' => 'Non authentifié'], 401);

        $restaurants = $this->repo->findBy(['owner' => $user]);
        $data = [];

        foreach ($restaurants as $r) {
            $data[] = [
                'id' => $r->getId(),
                'name' => $r->getName(),
                'slug' => $r->getSlug(),
                'address' => $r->getAddress(),
                'isActive' => $r->isActive(),
                'image' => $r->getImage(),
                'createdAt' => $r->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }

        return $this->json($data);
    }

    // CRÉER un restaurant
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) return $this->json(['message' => 'Non authentifié'], 401);

        // Récupération champs multipart/form-data
        $name = $request->request->get('name');
        $address = $request->request->get('address');
        $primaryColor = $request->request->get('primaryColor') ?? '#ffffff';

        $restaurant = new Restaurant();
        $restaurant->setName($name);
        $restaurant->setAddress($address);
        $restaurant->setPrimaryColor($primaryColor);
        $restaurant->setIsActive(true);
        $restaurant->setCreatedAt(new \DateTime());
        $restaurant->setUpdateAt(new \DateTime());
        $restaurant->setOwner($user);

        // Génération slug
        $slug = strtolower($this->slugger->slug($restaurant->getName()));
        $restaurant->setSlug($slug);

        // Upload image principale si fournie
        /** @var UploadedFile $file */
        $file = $request->files->get('image');
        if ($file) {
            $fileName = $this->fileUploader->upload($file);
            $restaurant->setImage('/uploads/restaurants/' . $fileName);
        }

        $this->em->persist($restaurant);
        $this->em->flush();

        return $this->json(['message' => 'Restaurant créé', 'id' => $restaurant->getId()]);
    }

    // AFFICHER un restaurant
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $restaurant = $this->repo->find($id);
        if (!$restaurant) return $this->json(['message' => 'Restaurant non trouvé'], 404);

        $this->denyAccessUnlessGranted('RESOURCE_VIEW', $restaurant);

        return $this->json([
            'id' => $restaurant->getId(),
            'name' => $restaurant->getName(),
            'slug' => $restaurant->getSlug(),
            'address' => $restaurant->getAddress(),
            'primaryColor' => $restaurant->getPrimaryColor(),
            'isActive' => $restaurant->isActive(),
            'image' => $restaurant->getImage(),
            'createdAt' => $restaurant->getCreatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    // MODIFIER un restaurant
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $restaurant = $this->repo->find($id);
        if (!$restaurant) return $this->json(['message' => 'Restaurant non trouvé'], 404);

        $this->denyAccessUnlessGranted('RESOURCE_EDIT', $restaurant);

        $name = $request->request->get('name');
        $address = $request->request->get('address');
        $primaryColor = $request->request->get('primaryColor');

        if ($name) {
            $restaurant->setName($name);
            $restaurant->setSlug(strtolower($this->slugger->slug($name)));
        }
        if ($address) $restaurant->setAddress($address);
        if ($primaryColor) $restaurant->setPrimaryColor($primaryColor);

        $restaurant->setUpdateAt(new \DateTime());

        /** @var UploadedFile $file */
        $file = $request->files->get('image');
        if ($file) {
            $fileName = $this->fileUploader->upload($file);
            $restaurant->setImage('/uploads/restaurants/' . $fileName);
        }

        $this->em->flush();

        return $this->json(['message' => 'Restaurant mis à jour']);
    }

    // SUPPRIMER un restaurant
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $restaurant = $this->repo->find($id);
        if (!$restaurant) return $this->json(['message' => 'Restaurant non trouvé'], 404);

        $this->denyAccessUnlessGranted('RESOURCE_DELETE', $restaurant);

        $this->em->remove($restaurant);
        $this->em->flush();

        return $this->json(['message' => 'Restaurant supprimé']);
    }
}