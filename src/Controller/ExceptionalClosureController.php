<?php

namespace App\Controller;

use App\Entity\ExceptionalClosure;
use App\Repository\ExceptionalClosureRepository;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/closures', name:'api_closure_')]
class ExceptionalClosureController extends AbstractController
{
    private $em;
    private $repo;
    private $restaurantRepo;

    public function __construct(EntityManagerInterface $em, ExceptionalClosureRepository $repo, RestaurantRepository $restaurantRepo)
    {
        $this->em = $em;
        $this->repo = $repo;
        $this->restaurantRepo = $restaurantRepo;
    }

    #[Route('', name:'list', methods:['GET'])]
    public function list(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['message'=>'Non authentifié'],401);
        }

        $closures = $this->repo->findAll();
        $data = [];

        foreach($closures as $c){

            if($c->getRestaurant()->getOwner() !== $user){
                continue;
            }

            $data[] = [
                'id'=>$c->getId(),
                'date'=>$c->getDate()->format('Y-m-d'),
                'reason'=>$c->getReason(),
                'restaurantId'=>$c->getRestaurant()->getId()
            ];
        }

        return $this->json($data);
    }

    #[Route('', name:'create', methods:['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['message'=>'Non authentifié'],401);
        }

        $data = json_decode($request->getContent(), true);

        $restaurant = $this->restaurantRepo->find($data['restaurantId']);

        if(!$restaurant){
            return $this->json(['message'=>'Restaurant non trouvé'],404);
        }

        $this->denyAccessUnlessGranted('RESOURCE_EDIT', $restaurant);

        $ec = new ExceptionalClosure();
        $ec->setDate(new \DateTime($data['date']));
        $ec->setReason($data['reason'] ?? null);
        $ec->setRestaurant($restaurant);

        $this->em->persist($ec);
        $this->em->flush();

        return $this->json([
            'message'=>'Fermeture ajoutée',
            'id'=>$ec->getId()
        ]);
    }

    #[Route('/{id}', name:'show', methods:['GET'])]
    public function show(int $id): JsonResponse
    {
        $ec = $this->repo->find($id);

        if(!$ec){
            return $this->json(['message'=>'Fermeture non trouvée'],404);
        }

        $this->denyAccessUnlessGranted('RESOURCE_VIEW', $ec->getRestaurant());

        return $this->json([
            'id'=>$ec->getId(),
            'date'=>$ec->getDate()->format('Y-m-d'),
            'reason'=>$ec->getReason(),
            'restaurantId'=>$ec->getRestaurant()->getId()
        ]);
    }

    #[Route('/{id}', name:'update', methods:['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $ec = $this->repo->find($id);

        if(!$ec){
            return $this->json(['message'=>'Fermeture non trouvée'],404);
        }

        $this->denyAccessUnlessGranted('RESOURCE_EDIT', $ec->getRestaurant());

        $data = json_decode($request->getContent(), true);

        if(isset($data['date'])){
            $ec->setDate(new \DateTime($data['date']));
        }

        $ec->setReason($data['reason'] ?? $ec->getReason());

        $this->em->flush();

        return $this->json(['message'=>'Fermeture mise à jour']);
    }

    #[Route('/{id}', name:'delete', methods:['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $ec = $this->repo->find($id);

        if(!$ec){
            return $this->json(['message'=>'Fermeture non trouvée'],404);
        }

        $this->denyAccessUnlessGranted('RESOURCE_DELETE', $ec->getRestaurant());

        $this->em->remove($ec);
        $this->em->flush();

        return $this->json(['message'=>'Fermeture supprimée']);
    }
}