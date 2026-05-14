<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_authenticated_routes(): void
    {
        $this->get('/vehicles')->assertRedirect('/login');
        $this->post('/notifications/read-all')->assertRedirect('/login');
    }

    public function test_non_admin_user_cannot_access_admin_routes(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => 'usta']);

        $this->actingAs($user)
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_admin_user_can_access_admin_users_page(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk();
    }

    public function test_customer_portal_vehicle_page_requires_valid_session_vehicle_id(): void
    {
        $vehicle = Vehicle::create([
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'license_plate' => '34 ABC 123',
            'chassis_number' => 'WVWZZZ1JZXW000001',
            'fuel_type' => 'Benzin',
            'transmission_type' => 'Manuel',
            'current_km' => 120000,
            'customer_name' => 'Test Customer',
            'customer_phone' => '05000000000',
        ]);

        // Session has portal context, but does not include this vehicle.
        $this->withSession([
            'customer_portal.vehicle_ids' => [$vehicle->id + 1],
        ])
            ->get(route('customer.portal.vehicle.show', $vehicle))
            ->assertForbidden();
    }
}
