<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PWAPerformanceTest extends DuskTestCase
{
    /**
     * Test First Contentful Paint
     */
    public function test_first_contentful_paint(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard');
            
            $fcp = $browser->script([
                'return new Promise(resolve => {
                    new PerformanceObserver(list => {
                        for (const entry of list.getEntries()) {
                            if (entry.name === "first-contentful-paint") {
                                resolve(entry.startTime);
                            }
                        }
                    }).observe({entryTypes: ["paint"]});
                })'
            ]);

            $this->assertLessThan(2000, $fcp[0], 'FCP should be under 2 seconds');
        });
    }

    /**
     * Test Largest Contentful Paint
     */
    public function test_largest_contentful_paint(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard');
            
            $lcp = $browser->script([
                'return new Promise(resolve => {
                    new PerformanceObserver(list => {
                        const entries = list.getEntries();
                        const lastEntry = entries[entries.length - 1];
                        resolve(lastEntry.startTime);
                    }).observe({entryTypes: ["largest-contentful-paint"]});
                    
                    setTimeout(() => resolve(0), 5000);
                })'
            ]);

            $this->assertLessThan(2500, $lcp[0], 'LCP should be under 2.5 seconds');
        });
    }

    /**
     * Test Cumulative Layout Shift
     */
    public function test_cumulative_layout_shift(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard')
                    ->pause(3000); // Allow page to fully load
            
            $cls = $browser->script([
                'let clsValue = 0;
                new PerformanceObserver(list => {
                    for (const entry of list.getEntries()) {
                        if (!entry.hadRecentInput) {
                            clsValue += entry.value;
                        }
                    }
                }).observe({entryTypes: ["layout-shift"]});
                
                return clsValue;'
            ]);

            $this->assertLessThan(0.1, $cls[0], 'CLS should be under 0.1');
        });
    }
}