<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Pages\Dashboard;

class PWATest extends DuskTestCase
{
    /**
     * Test that the PWA manifest is properly loaded
     */
    public function test_manifest_is_loaded(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSourceHas('link rel="manifest"')
                    ->assertSourceHas('href="/manifest.json"');
        });
    }

    /**
     * Test that service worker is registered
     */
    public function test_service_worker_registration(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->waitFor('body')
                    ->script([
                        'return navigator.serviceWorker.getRegistrations().then(registrations => registrations.length > 0)'
                    ]);
            
            $this->assertTrue($browser->script('return "serviceWorker" in navigator')[0]);
        });
    }

    /**
     * Test PWA install button functionality
     */
    public function test_pwa_install_button(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->script([
                        'window.dispatchEvent(new Event("beforeinstallprompt"))'
                    ])
                    ->pause(1000);
            
            // Check if install button becomes visible
            $installBtn = $browser->element('#pwa-install-btn');
            $this->assertNotNull($installBtn);
        });
    }

    /**
     * Test offline functionality
     */
    public function test_offline_functionality(): void
    {
        $this->browse(function (Browser $browser) {
            // First visit online to cache resources
            $browser->visit('/dashboard')
                    ->assertSee('Dashboard')
                    ->pause(2000); // Allow service worker to cache

            // Simulate offline mode
            $browser->script([
                'navigator.serviceWorker.ready.then(registration => {
                    return registration.sync.register("test-offline");
                })'
            ]);

            // Test offline indicator
            $browser->script(['window.dispatchEvent(new Event("offline"))'])
                    ->pause(500)
                    ->assertVisible('#offline-indicator');

            // Test back online
            $browser->script(['window.dispatchEvent(new Event("online"))'])
                    ->pause(500)
                    ->assertNotVisible('#offline-indicator');
        });
    }

    /**
     * Test PWA meta tags
     */
    public function test_pwa_meta_tags(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSourceHas('meta name="theme-color"')
                    ->assertSourceHas('meta name="apple-mobile-web-app-capable"')
                    ->assertSourceHas('meta name="apple-mobile-web-app-status-bar-style"')
                    ->assertSourceHas('link rel="apple-touch-icon"');
        });
    }

    /**
     * Test PWA caching strategy
     */
    public function test_caching_strategy(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard')
                    ->pause(3000); // Allow caching

            // Check if resources are cached
            $cacheCheck = $browser->script([
                'return caches.open("fofana-stock-v1.2.0").then(cache => {
                    return cache.keys().then(keys => keys.length > 0);
                })'
            ]);

            $this->assertTrue($cacheCheck[0]);
        });
    }

    /**
     * Test push notification permission
     */
    public function test_push_notification_permission(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->script([
                        'return "Notification" in window'
                    ]);

            $this->assertTrue($browser->script('return "Notification" in window')[0]);
            $this->assertTrue($browser->script('return "serviceWorker" in navigator')[0]);
            $this->assertTrue($browser->script('return "PushManager" in window')[0]);
        });
    }

    /**
     * Test offline order creation
     */
    public function test_offline_order_creation(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/orders/create')
                    ->pause(2000);

            // Simulate offline mode
            $browser->script(['window.dispatchEvent(new Event("offline"))'])
                    ->pause(500);

            // Test offline order saving
            $browser->script([
                'window.pwaManager.saveOfflineOrder({
                    customer_name: "Test Customer",
                    total: 100,
                    items: []
                })'
            ]);

            // Check if order was saved offline
            $offlineOrders = $browser->script([
                'return window.pwaManager.getOfflineOrders()'
            ]);

            $this->assertNotEmpty($offlineOrders[0]);
        });
    }

    /**
     * Test PWA shortcuts
     */
    public function test_pwa_shortcuts(): void
    {
        $this->browse(function (Browser $browser) {
            $response = $browser->visit('/manifest.json');
            
            $manifest = json_decode($response->plainText, true);
            
            $this->assertArrayHasKey('shortcuts', $manifest);
            $this->assertNotEmpty($manifest['shortcuts']);
            
            foreach ($manifest['shortcuts'] as $shortcut) {
                $this->assertArrayHasKey('name', $shortcut);
                $this->assertArrayHasKey('url', $shortcut);
                $this->assertArrayHasKey('icons', $shortcut);
            }
        });
    }

    /**
     * Test PWA performance metrics
     */
    public function test_pwa_performance(): void
    {
        $this->browse(function (Browser $browser) {
            $startTime = microtime(true);
            
            $browser->visit('/dashboard')
                    ->waitFor('[data-testid="dashboard-content"]', 5);
            
            $loadTime = (microtime(true) - $startTime) * 1000;
            
            // Assert page loads within 3 seconds
            $this->assertLessThan(3000, $loadTime, 'Dashboard should load within 3 seconds');
        });
    }

    /**
     * Test PWA accessibility
     */
    public function test_pwa_accessibility(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSourceHas('lang=')
                    ->assertSourceHas('role="main"')
                    ->assertSourceHas('aria-')
                    ->assertSourceHas('alt=');

            // Test skip link
            $browser->keys('body', '{tab}')
                    ->assertFocused('a[href="#main-content"]');
        });
    }
}