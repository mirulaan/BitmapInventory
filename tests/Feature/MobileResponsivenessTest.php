<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileResponsivenessTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_authenticated_view_includes_mobile_meta_viewport(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">', false);
    }

    public function test_layout_renders_mobile_hamburger_button_and_off_canvas_drawer(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        // Hamburger trigger button
        $response->assertSee('aria-label="Open mobile navigation menu"', false);
        $response->assertSee('toggleMobileNav(true)', false);

        // Backdrop & Drawer container
        $response->assertSee('id="mobile-nav-backdrop"', false);
        $response->assertSee('id="mobile-nav-drawer"', false);
        $response->assertSee('aria-label="Mobile Navigation Drawer"', false);

        // Drawer navigation links
        $response->assertSee(route('dashboard'));
        $response->assertSee(route('inventory.index'));
        $response->assertSee(route('products.index'));
        $response->assertSee(route('purchase-orders.index'));
        $response->assertSee(route('stock-counts.index'));
        $response->assertSee(route('inventory.valuation'));
        $response->assertSee(route('inventory.audit'));
    }

    public function test_layout_renders_mobile_bottom_navigation_bar(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('aria-label="Mobile Quick Navigation"', false);
        $response->assertSee('Command');
        $response->assertSee('Assets');
        $response->assertSee('Catalog');
        $response->assertSee('Audits');
        $response->assertSee('More');
    }

    public function test_mobile_navigation_controller_javascript_is_present(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('function toggleMobileNav(open)', false);
        $response->assertSee('mobile-nav-backdrop', false);
        $response->assertSee('mobile-nav-drawer', false);
    }
}
