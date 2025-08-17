import './bootstrap';
import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import { initOrdersChart, initStockChart, initLowStockChart } from './charts';
import { showDrilldownModal, closeModal, announce } from './charts';

// Alpine plugins
Alpine.plugin(focus);

// Custom Alpine directives for accessibility
Alpine.directive('roving-tabindex', (el, { expression }, { evaluateLater, cleanup }) => {
    const items = Array.from(el.querySelectorAll('[data-roving-item]'));
    let currentIndex = 0;

    const updateTabIndex = (newIndex) => {
        items.forEach((item, idx) => {
            item.setAttribute('tabindex', idx === newIndex ? '0' : '-1');
        });
        currentIndex = newIndex;
    };

    // Initialize
    updateTabIndex(0);

    items.forEach((item, idx) => {
        const keydownHandler = (e) => {
            let newIndex = currentIndex;
            
            switch (e.key) {
                case 'ArrowDown':
                case 'ArrowRight':
                    e.preventDefault();
                    newIndex = (currentIndex + 1) % items.length;
                    break;
                case 'ArrowUp':
                case 'ArrowLeft':
                    e.preventDefault();
                    newIndex = (currentIndex - 1 + items.length) % items.length;
                    break;
                case 'Home':
                    e.preventDefault();
                    newIndex = 0;
                    break;
                case 'End':
                    e.preventDefault();
                    newIndex = items.length - 1;
                    break;
                default:
                    return;
            }
            
            updateTabIndex(newIndex);
            items[newIndex].focus();
        };

        const focusHandler = () => {
            updateTabIndex(idx);
        };

        item.addEventListener('keydown', keydownHandler);
        item.addEventListener('focus', focusHandler);

        cleanup(() => {
            item.removeEventListener('keydown', keydownHandler);
            item.removeEventListener('focus', focusHandler);
        });
    });
});

// Announce messages to screen readers
Alpine.directive('announce', (el, { expression }, { evaluateLater }) => {
    const evaluate = evaluateLater(expression);
    
    const announce = (message) => {
        const statusEl = document.getElementById('status-messages');
        if (statusEl) {
            statusEl.textContent = '';
            setTimeout(() => {
                statusEl.textContent = message;
            }, 100);
        }
    };

    el._x_announce = announce;
});

// Custom Alpine directives for mobile
Alpine.directive('swipe', (el, { expression }, { evaluateLater, cleanup }) => {
    let startX = 0;
    let startY = 0;
    let threshold = 50;
    
    const handleTouchStart = (e) => {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
    };
    
    const handleTouchEnd = (e) => {
        if (!startX || !startY) return;
        
        let endX = e.changedTouches[0].clientX;
        let endY = e.changedTouches[0].clientY;
        
        let diffX = startX - endX;
        let diffY = startY - endY;
        
        // Only trigger if horizontal swipe is dominant
        if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > threshold) {
            const direction = diffX > 0 ? 'left' : 'right';
            evaluateLater(expression)(direction);
        }
        
        startX = 0;
        startY = 0;
    };
    
    el.addEventListener('touchstart', handleTouchStart, { passive: true });
    el.addEventListener('touchend', handleTouchEnd, { passive: true });
    
    cleanup(() => {
        el.removeEventListener('touchstart', handleTouchStart);
        el.removeEventListener('touchend', handleTouchEnd);
    });
});

// Touch feedback directive
Alpine.directive('touch-feedback', (el) => {
    const addFeedback = () => {
        el.classList.add('bg-gray-100', 'scale-95');
    };
    
    const removeFeedback = () => {
        el.classList.remove('bg-gray-100', 'scale-95');
    };
    
    el.addEventListener('touchstart', addFeedback, { passive: true });
    el.addEventListener('touchend', removeFeedback, { passive: true });
    el.addEventListener('touchcancel', removeFeedback, { passive: true });
});

// Global functions
window.Alpine = Alpine;
window.initOrdersChart = initOrdersChart;
window.initStockChart = initStockChart;
window.initLowStockChart = initLowStockChart;
window.showDrilldownModal = showDrilldownModal;
window.closeModal = closeModal;
window.announce = announce;

Alpine.start();



