<?php

namespace App\Controller;

use App\Repository\RestaurantRepository;
use App\Repository\CategoryRepository;
use App\Repository\DishRepository;
use App\Repository\GalleryImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/dashboard')]
class DashboardController extends AbstractController
{
    #[Route('', methods:['GET'])]
    public function stats(
        RestaurantRepository $restaurantRepo,
        CategoryRepository $categoryRepo,
        DishRepository $dishRepo,
        GalleryImageRepository $galleryRepo
    ): JsonResponse {

        $user = $this->getUser();

        if(!$user){
            return $this->json(['message'=>'Non authentifié'],401);
        }

        $restaurants = $restaurantRepo->findBy(['owner'=>$user]);

        $restaurantIds = array_map(fn($r)=>$r->getId(), $restaurants);

        $totalRestaurants = count($restaurants);

        $totalCategories = $categoryRepo->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->join('c.restaurant','r')
            ->where('r.owner = :owner')
            ->setParameter('owner',$user)
            ->getQuery()
            ->getSingleScalarResult();

        $totalDishes = $dishRepo->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->join('d.restaurant','r')
            ->where('r.owner = :owner')
            ->setParameter('owner',$user)
            ->getQuery()
            ->getSingleScalarResult();

        $totalImages = $galleryRepo->createQueryBuilder('g')
            ->select('COUNT(g.id)')
            ->join('g.restaurant','r')
            ->where('r.owner = :owner')
            ->setParameter('owner',$user)
            ->getQuery()
            ->getSingleScalarResult();

        return $this->json([
            'restaurants'=>$totalRestaurants,
            'categories'=>(int)$totalCategories,
            'dishes'=>(int)$totalDishes,
            'galleryImages'=>(int)$totalImages
        ]);
    }
}