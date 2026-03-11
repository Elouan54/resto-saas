<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class ApiTestCase extends WebTestCase
{
    protected function createAuthenticatedClient(string $role = 'ROLE_OWNER'): KernelBrowser
    {
        $client = static::createClient();

        // Idéal : récupérer le token via l'endpoint login_check
        $email = $role === 'ROLE_ADMIN' ? 'test@admin.com' : 'test@owner.com';
        $password = 'test123';

        $client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $email,
                'password' => $password
            ])
        );

        $data = json_decode($client->getResponse()->getContent(), true);
        $token = $data['token'] ?? null;

        if (!$token) {
            throw new \Exception('Impossible de récupérer le token JWT pour ' . $email);
        }

        $client->setServerParameter('HTTP_Authorization', 'Bearer ' . $token);

        return $client;
    }
}