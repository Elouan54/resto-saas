<?php

namespace App\Controller;

use App\Entity\OpeningHours;
use App\Repository\OpeningHoursRepository;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/opening-hours', name:'api_opening_hours_')]
class OpeningHoursController extends AbstractController
{
    private $em;
    private $repo;
    private $restaurantRepo;

    public function __construct(
        EntityManagerInterface $em,
        OpeningHoursRepository $repo,
        RestaurantRepository $restaurantRepo
    ){
        $this->em = $em;
        $this->repo = $repo;
        $this->restaurantRepo = $restaurantRepo;
    }

    // LISTE des horaires (uniquement ceux du OWNER connecté)
    #[Route('', name:'list', methods:['GET'])]
    public function list(): JsonResponse
    {
        $user = $this->getUser();

        $hours = $this->repo->createQueryBuilder('oh')
            ->join('oh.restaurant', 'r')
            ->where('r.owner = :owner')
            ->setParameter('owner', $user)
            ->getQuery()
            ->getResult();

        $data = [];

        foreach($hours as $h){
            $data[] = [
                'id'=>$h->getId(),
                'dayOfWeek'=>$h->getDayOfWeek(),
                'openingTime'=>$h->getOpeningTime()->format('H:i'),
                'closingTime'=>$h->getClosingTime()->format('H:i'),
                'restaurantId'=>$h->getRestaurant()->getId()
            ];
        }

        return $this->json($data);
    }

    // CRÉER un horaire
    #[Route('', name:'create', methods:['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $restaurant = $this->restaurantRepo->find($data['restaurantId']);

        if(!$restaurant){
            return $this->json(['message'=>'Restaurant non trouvé'],404);
        }

        // vérifie que le restaurant appartient à l'utilisateur
        if($restaurant->getOwner() !== $this->getUser()){
            return $this->json(['message'=>'Accès refusé'],403);
        }

        $oh = new OpeningHours();
        $oh->setDayOfWeek($data['dayOfWeek']);
        $oh->setOpeningTime(new \DateTime($data['openingTime']));
        $oh->setClosingTime(new \DateTime($data['closingTime']));
        $oh->setRestaurant($restaurant);

        $this->em->persist($oh);
        $this->em->flush();

        return $this->json([
            'message'=>'Horaire ajouté',
            'id'=>$oh->getId()
        ]);
    }

    // AFFICHER un horaire
    #[Route('/{id}', name:'show', methods:['GET'])]
    public function show(int $id): JsonResponse
    {
        $oh = $this->repo->find($id);

        if(!$oh){
            return $this->json(['message'=>'Horaire non trouvé'],404);
        }

        $this->denyAccessUnlessGranted('RESOURCE_VIEW', $oh);

        return $this->json([
            'id'=>$oh->getId(),
            'dayOfWeek'=>$oh->getDayOfWeek(),
            'openingTime'=>$oh->getOpeningTime()->format('H:i'),
            'closingTime'=>$oh->getClosingTime()->format('H:i'),
            'restaurantId'=>$oh->getRestaurant()->getId()
        ]);
    }

    // MODIFIER un horaire
    #[Route('/{id}', name:'update', methods:['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $oh = $this->repo->find($id);

        if(!$oh){
            return $this->json(['message'=>'Horaire non trouvé'],404);
        }

        $this->denyAccessUnlessGranted('RESOURCE_EDIT', $oh);

        $data = json_decode($request->getContent(), true);

        $oh->setDayOfWeek($data['dayOfWeek'] ?? $oh->getDayOfWeek());

        if(isset($data['openingTime'])){
            $oh->setOpeningTime(new \DateTime($data['openingTime']));
        }

        if(isset($data['closingTime'])){
            $oh->setClosingTime(new \DateTime($data['closingTime']));
        }

        $this->em->flush();

        return $this->json(['message'=>'Horaire mis à jour']);
    }

    // SUPPRIMER un horaire
    #[Route('/{id}', name:'delete', methods:['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $oh = $this->repo->find($id);

        if(!$oh){
            return $this->json(['message'=>'Horaire non trouvé'],404);
        }

        $this->denyAccessUnlessGranted('RESOURCE_DELETE', $oh);

        $this->em->remove($oh);
        $this->em->flush();

        return $this->json(['message'=>'Horaire supprimé']);
    }
}