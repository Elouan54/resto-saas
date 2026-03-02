<?php

namespace App\Controller;

use App\Entity\ContactMessage;
use App\Repository\ContactMessageRepository;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/contact-messages', name:'api_contact_')]
class ContactMessageController extends AbstractController
{
    private $em;
    private $repo;
    private $restaurantRepo;

    public function __construct(EntityManagerInterface $em, ContactMessageRepository $repo, RestaurantRepository $restaurantRepo)
    {
        $this->em = $em;
        $this->repo = $repo;
        $this->restaurantRepo = $restaurantRepo;
    }

    #[Route('', name:'list', methods:['GET'])]
    public function list(): JsonResponse
    {
        $messages = $this->repo->findAll();
        $data = [];
        foreach($messages as $m){
            $data[] = [
                'id'=>$m->getId(),
                'name'=>$m->getName(),
                'email'=>$m->getEmail(),
                'message'=>$m->getMessage(),
                'createdAt'=>$m->getCreatedAt()->format('Y-m-d H:i'),
                'restaurantId'=>$m->getRestaurant()->getId()
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

        $msg = new ContactMessage();
        $msg->setName($data['name']);
        $msg->setEmail($data['email']);
        $msg->setMessage($data['message']);
        $msg->setCreatedAt(new \DateTime());
        $msg->setRestaurant($restaurant);

        $this->em->persist($msg);
        $this->em->flush();

        return $this->json(['message'=>'Message créé','id'=>$msg->getId()]);
    }

    #[Route('/{id}', name:'show', methods:['GET'])]
    public function show(int $id): JsonResponse
    {
        $msg = $this->repo->find($id);
        if(!$msg) return $this->json(['message'=>'Message non trouvé'],404);

        return $this->json([
            'id'=>$msg->getId(),
            'name'=>$msg->getName(),
            'email'=>$msg->getEmail(),
            'message'=>$msg->getMessage(),
            'createdAt'=>$msg->getCreatedAt()->format('Y-m-d H:i'),
            'restaurantId'=>$msg->getRestaurant()->getId()
        ]);
    }

    #[Route('/{id}', name:'delete', methods:['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $msg = $this->repo->find($id);
        if(!$msg) return $this->json(['message'=>'Message non trouvé'],404);

        $this->em->remove($msg);
        $this->em->flush();
        return $this->json(['message'=>'Message supprimé']);
    }
}