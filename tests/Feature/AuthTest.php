<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuthTest extends TestCase
{
    /**
     * test case for required fields for registration
     *
     * @return void
     */
    public function test_required_fields_for_registration()
    {
        $this->json('POST', route('api.register'))
        ->assertJson([
            "success" => false,
            "message" => "Error",
            "data" => [
                "name" => ["The name field is required."],
                "email" => ["The email field is required."],
                "password" => ["The password field is required."],
                "confirm_password" => ["The confirm password field is required."]
            ]
        ]);
    }

    /**
     * test case for registration email validation
     *
     * @return void
     */
    public function test_registration_email_validate()
    {
        $this->postJson(route('api.register'), [
            "name" => "Demo 3",
            "email" => "de@",
            "password" => "password",
            "confirm_password" => "password"
        ])->assertJson([
            "success" => false,
            "message" => "Error",
            "data" => [
                "email" => [
                    "The email must be a valid email address."
                ]
            ]
        ]);
    }

    /**
     * test case for registration password mis matched validation
     *
     * @return void
     */
    public function test_registration_mis_matched_password()
    {
        $this->postJson(route('api.register'), [
            "name" => "Demo 3",
            "email" => "de@",
            "password" => "password",
            "confirm_password" => "password123"
        ])->assertJson([
            "success" => false,
            "message" => "Error",
            "data" => [
                "confirm_password" => [
                    "The confirm password and password must match."
                ]
            ]
        ]);
    }

    /**
     * test case for registration successfully
     *
     * @return void
     */
    public function test_registration_successfully()
    {
        DB::table('users')->where('email','sample@example.com')->delete();

        $this->postJson(route('api.register'), [
            "name" => "Sample user",
            "email" => "sample@example.com",
            "password" => "password",
            "confirm_password" => "password"
        ])->assertJson([
            "success" => true,
            "data" => [],
            "message" => "User created successfully."
        ]);
    }

    /**
     * test case for registration email already exist
     *
     * @return void
     */
    public function test_registration_email_already_exist()
    {
        $this->postJson(route('api.register'), [
            "name" => "Demo 3",
            "email" => "sample@example.com",
            "password" => "password",
            "confirm_password" => "password"
        ])->assertJson([
            "success" => false,
            "message" => "Error",
            "data" => [
                "email" => [
                    "The email has already been taken."
                ]
            ]
        ]);
    }

    /**
     * test case for login required fields
     *
     * @return void
     */
    public function test_required_fields_for_login()
    {
        $this->json('POST', route('api.login'))
        ->assertJson([
            "success" => false,
            "message" => "Error",
            "data" => [
                "email" => [
                    "The email field is required."
                ],
                "password" => [
                    "The password field is required."
                ]
            ]
        ]);
    }

    /**
     * test case for login wrong email id
     *
     * @return void
     */
    public function test_wrong_email()
    {
        $this->postJson(route('api.login'), [
            'email' => 'sample.com',
            'password' => '12345678',
        ])->assertJson([
            "success" => false,
            "message" => "Error",
            "data" => [
                "email" => ["The email must be a valid email address."]
            ]
        ]);
    }

    /**
     * test case for login with minimum length password
     *
     * @return void
     */
    public function test_wrong_min_length_password()
    {
        $this->postJson(route('api.login'), [
            'email' => 'sample@grr.la',
            'password' => '12345',
        ])->assertJson([
            "success" => false,
            "message" => "Error",
            "data" => [
                "password" => [
                    "The password must be at least 8 characters."
                ]
            ]
        ]);
    }

    /**
     * test case for login with wrong details
     *
     * @return void
     */
    public function test_wrong_login_details()
    {
        $this->postJson(route('api.login'), [
            'email' => 'demo1@example.com',
            'password' => '12345678',
        ])->assertJson([
            "success" => false,
            "message" => "User is unauthorised.",
            "data" => [
                "error" => "User is unauthorised."
            ]
        ]);
    }

    /**
     * test case for login successfully
     *
     * @return void
     */
    public function test_login_successfully()
    {
        $this->postJson(route('api.login'), [
            'email' => 'sample@example.com',
            'password' => 'password',
        ])->assertJson([
            "success" => true,
            "data" => [],
            "message" => "User authenticated successfully."
        ]);
    }
}
