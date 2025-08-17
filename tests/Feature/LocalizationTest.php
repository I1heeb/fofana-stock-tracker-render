<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_locale_is_french()
    {
        $this->assertEquals('fr', config('app.locale'));
    }

    public function test_locale_can_be_switched()
    {
        $response = $this->get('/locale/en');
        
        $response->assertRedirect();
        $this->assertEquals('en', Session::get('locale'));
    }

    public function test_invalid_locale_is_ignored()
    {
        $response = $this->get('/locale/invalid');
        
        $response->assertRedirect();
        $this->assertNull(Session::get('locale'));
    }

    public function test_middleware_sets_locale_from_session()
    {
        Session::put('locale', 'ar');
        
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/dashboard');
        
        $this->assertEquals('ar', App::getLocale());
    }

    public function test_rtl_direction_is_set_for_arabic()
    {
        Session::put('locale', 'ar');
        
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertViewHas('direction', 'rtl');
    }

    public function test_ltr_direction_is_set_for_other_languages()
    {
        Session::put('locale', 'en');
        
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertViewHas('direction', 'ltr');
    }

    public function test_translations_are_loaded_correctly()
    {
        App::setLocale('fr');
        $this->assertEquals('Commandes', __('orders.title'));
        
        App::setLocale('en');
        $this->assertEquals('Orders', __('orders.title'));
        
        App::setLocale('ar');
        $this->assertEquals('الطلبات', __('orders.title'));
    }

    public function test_language_selector_shows_current_locale()
    {
        Session::put('locale', 'fr');
        
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertSee('Français');
    }

    public function test_pluralization_works_with_parameters()
    {
        App::setLocale('en');
        $message = __('orders.order_summary', ['count' => 3, 'total' => '75.00']);
        $this->assertStringContainsString('3 items', $message);
        $this->assertStringContainsString('$75.00', $message);
    }
}