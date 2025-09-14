import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */
import bsCustomFileInput from 'bs-custom-file-input';
import 'jquery';
// any CSS you import will output into a single css file (app.css in this case)
const $ = require('jquery');
global.$ = global.jQuery = $;

bsCustomFileInput.init();

import './styles/potins.scss';
import './styles/calendar.css';
import './styles/potins_mob.css';
import './styles/media.css';
import './styles/form.css';
import './styles/menupotins.css';
import './styles/articles.css';
import './styles/bouton.css';
import './styles/potin.css';
