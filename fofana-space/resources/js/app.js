import './bootstrap';
import { initOrdersChart, initStockChart, initLowStockChart } from './charts';
import { showDrilldownModal, closeModal, announce } from './charts';

// Make functions globally available
window.initOrdersChart = initOrdersChart;
window.initStockChart = initStockChart;
window.initLowStockChart = initLowStockChart;
window.showDrilldownModal = showDrilldownModal;
window.closeModal = closeModal;
window.announce = announce;
