<?php

namespace App\Controller;

use App\Repository\RestaurantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class PublicRestaurantController extends AbstractController
{
    #[Route('/api/public/restaurants', methods:['GET'])]
    public function list(RestaurantRepository $repo): JsonResponse
    {
        $restaurants = $repo->findBy(['isActive' => true]);

        $data = [];

        foreach ($restaurants as $restaurant) {
            $data[] = [
                'id'=>$restaurant->getId(),
                'name'=>$restaurant->getName(),
                'slug'=>$restaurant->getSlug(),
                'image'=>$restaurant->getLogo(),
                'categories'=>[]
            ];
        }

        return $this->json($data);
    }

    #[Route('/api/public/restaurants/{slug}/menu', methods:['GET'])]
    public function menu(string $slug, RestaurantRepository $repo): JsonResponse
    {
        $restaurant = $repo->findOneBy(['slug'=>$slug]);

        if(!$restaurant){
            return $this->json(['message'=>'Restaurant non trouvé'],404);
        }

        $data = [
            'name'=>$restaurant->getName(),
            'slug'=>$restaurant->getSlug(),
            'categories'=>[]
        ];

        foreach($restaurant->getCategories() as $category){

            $cat = [
                'id'=>$category->getId(),
                'name'=>$category->getName(),
                'dishes'=>[]
            ];

            foreach($category->getDishes() as $dish){

                if(!$dish->isAvailable()){
                    continue;
                }

                $cat['dishes'][] = [
                    'id'=>$dish->getId(),
                    'name'=>$dish->getName(),
                    'price'=>$dish->getPrice(),
                    'description'=>$dish->getDescription(),
                    'image'=>$dish->getImage()
                ];

            }

            $data['categories'][] = $cat;
        }

        return $this->json($data);
    }
}