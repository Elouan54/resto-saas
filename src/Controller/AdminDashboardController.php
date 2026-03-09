<?php

namespace App\Controller;

use App\Repository\RestaurantRepository;
use App\Repository\UserRepository;
use App\Repository\DishRepository;
use App\Repository\ContactMessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/dashboard', name: 'api_admin_dashboard_')]
class AdminDashboardController extends AbstractController
{
    private RestaurantRepository $restaurantRepo;
    private UserRepository $userRepo;
    private DishRepository $dishRepo;
    private ContactMessageRepository $messageRepo;

    public function __construct(
        RestaurantRepository $restaurantRepo,
        UserRepository $userRepo,
        DishRepository $dishRepo,
        ContactMessageRepository $messageRepo
    ) {
        $this->restaurantRepo = $restaurantRepo;
        $this->userRepo = $userRepo;
        $this->dishRepo = $dishRepo;
        $this->messageRepo = $messageRepo;
    }

    #[Route('', name: 'stats', methods: ['GET'])]
    public function stats(): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Utilisateurs
        $users = $this->userRepo->findAll();
        $totalUsers = count($users);
        $adminUsers = 0;

        foreach ($users as $user) {
            if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                $adminUsers++;
            }
        }

        $ownerUsers = $totalUsers - $adminUsers;

        // Restaurants
        $totalRestaurants = $this->restaurantRepo->count([]);
        $activeRestaurants = $this->restaurantRepo->count(['isActive' => true]);
        $inactiveRestaurants = $totalRestaurants - $activeRestaurants;

        // Plats
        $totalDishes = $this->dishRepo->count([]);

        // Messages de contact
        $totalMessages = $this->messageRepo->count([]);
        // $unreadMessages = $this->messageRepo->count(['isRead' => false]);

        return $this->json([
            'users' => [
                'total' => $totalUsers,
                'admins' => $adminUsers,
                'owners' => $ownerUsers
            ],
            'restaurants' => [
                'total' => $totalRestaurants,
                'active' => $activeRestaurants,
                'inactive' => $inactiveRestaurants
            ],
            'dishes' => [
                'total' => $totalDishes
            ],
            'contactMessages' => [
                'total' => $totalMessages,
                //'unread' => $unreadMessages
            ]
        ]);
    }
}