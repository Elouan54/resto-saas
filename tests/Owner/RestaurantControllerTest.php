<?php

namespace App\Tests\Owner;

use App\Tests\ApiTestCase;

class RestaurantControllerTest extends ApiTestCase
{
    public function testRestaurantsRequireAuthentication(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/restaurants');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testOwnerCanListRestaurants(): void
    {
        $client = $this->createAuthenticatedClient('ROLE_OWNER');

        $client->request('GET', '/api/restaurants');

        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');
    }

    public function testOwnerCanCreateRestaurant(): void
    {
        $client = $this->createAuthenticatedClient('ROLE_OWNER');

        $client->request(
            'POST',
            '/api/restaurants',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Test Restaurant',
                'description' => 'Restaurant de test',
                'address' => '1 rue du test',
                'phone' => '0102030405'
            ])
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseFormatSame('json');
    }

    public function testOwnerCannotAccessAdminEndpoint(): void
    {
        $client = $this->createAuthenticatedClient('ROLE_OWNER');

        $client->request('GET', '/api/admin/dashboard');

        $this->assertResponseStatusCodeSame(403);
    }
}