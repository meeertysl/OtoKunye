<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityValidationAndCsrfTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_rejects_invalid_payload(): void
    {
        $response = $this->from('/register')
            ->post('/register', [
                'name' => '',
                'email' => 'not-an-email',
                'password' => '123',
                'password_confirmation' => '456',
            ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertGuest();
    }

    public function test_vehicle_create_rejects_invalid_input_values(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => 'usta']);

        $response = $this->actingAs($user)
            ->from('/vehicles/create')
            ->post('/vehicles', [
                'brand' => 'Test',
                'model' => 'Model',
                'license_plate' => 'INVALID-PLATE',
                'chassis_number' => 'SHORTVIN',
                'fuel_type' => 'Benzin',
                'transmission_type' => 'Manuel',
                'current_km' => -5,
                'customer_name' => 'Customer Name',
                'customer_phone' => '05000000000',
            ]);

        $response->assertRedirect('/vehicles/create');
        $response->assertSessionHasErrors([
            'license_plate',
            'chassis_number',
            'current_km',
        ]);
        $this->assertDatabaseCount('vehicles', 0);
    }

    public function test_logout_rotates_csrf_token_and_ends_session(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => 'usta']);

        $this->actingAs($user)->get('/vehicles')->assertOk();
        $oldToken = session()->token();

        $this->post('/logout', [
            '_token' => $oldToken,
        ])->assertRedirect('/login');

        $this->assertGuest();
        $this->assertNotSame($oldToken, session()->token());
    }
}
