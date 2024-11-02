import './bootstrap.js';
//import './js/alpine.min.js';
//import './js/init-alpine.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';
// <!-- Nucleo Icons -->
import './css/nucleo-icons.css';
import './css/nucleo-svg.css';
// <!-- CSS Files -->
import './css/material-dashboard.css';
//import './styles/global.scss';

// import './styles/tailwind.output.css';

//const $ = require('jquery');
// this "modifies" the jquery module: adding behavior to it
// the bootstrap module doesn't export/return anything
//require('bootstrap');

// or you can include specific pieces
// require('bootstrap/js/dist/tooltip');
// require('bootstrap/js/dist/popover');

// $(document).ready(function () {
//     $('[data-toggle="popover"]').popover();
// });

import './js/material-dashboard.js';