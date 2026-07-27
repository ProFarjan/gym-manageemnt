import './bootstrap';
import * as Bootstrap from 'bootstrap';
import $ from 'jquery';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

window.Bootstrap = Bootstrap;
window.$ = window.jQuery = $;
window.Chart = Chart;
