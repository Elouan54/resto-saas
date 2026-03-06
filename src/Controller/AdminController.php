<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin', name:'api_admin_')]
class AdminController extends AbstractController
{
    #[Route('/test', name:'test', methods:['GET'])]
    public function test(): JsonResponse
    {
        // Vérifie que l'utilisateur a le rôle ADMIN
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->json([
            'message' => 'Hello Admin!',
            'user' => $this->getUser()->getEmail(),
            'roles' => $this->getUser()->getRoles()
        ]);
    }
}