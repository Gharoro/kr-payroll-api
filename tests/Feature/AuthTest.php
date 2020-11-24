<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AuthTest extends TestCase
{
    // use RefreshDatabase;
    use WithFaker;
    
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testRegistration()
    {
        $response = $this->json('POST', '/api/register', [
            'company_name' => $this->faker->firstName(),
            'email' => $this->faker->unique()->email(),
            'password' => 'secret',
            'company_address' => $this->faker->address(),
            'company_contact' => $this->faker->phoneNumber()
        ]);

        $response->assertStatus(201);

    }

    public function testInvalidRegistration()
    {
        $response = $this->json('POST', '/api/register', [
            'company_name' => $this->faker->firstName(),
            'email' => 'ghudson@mckenzie.com',
            'password' => 'secret',
            'company_address' => $this->faker->address(),
            'company_contact' => $this->faker->phoneNumber()
        ]);

        $response->assertStatus(400);

    }

    public function testLogin(){
        $response = $this->json('POST', '/api/login', [
            'email' => 'ghudson@mckenzie.com',
            'password' => 'secret'
        ]);

        $response->assertStatus(200);
    }

    public function testInvalidLogin(){
        $response = $this->json('POST', '/api/login', [
            'email' => 'ghudson@mckenzie.com',
            'password' => 'secretsss'
        ]);

        $response->assertStatus(401);
    }
}
