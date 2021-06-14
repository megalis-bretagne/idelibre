import './styles/app.css';

import $ from 'jquery'
import 'bootstrap'
import bsCustomFileInput from 'bs-custom-file-input'
import flatpickr from "flatpickr";
import {French} from "flatpickr/dist/l10n/fr"
import './ls-sidebar/ls-sidebar'
import './js/ls-file'
import './js/comelus'
import './js/lsmessage'
import './js/expandable'

$(document).ready(function () {
    let config = {
        enableTime: true,
      //  altInput: true,
        altFormat: "d/m/y : H:i",
        "locale": French
    };
    flatpickr($('input[type=datetime-local]'), config);
})

//const getMessage = require('./getMessage');
//import getMessage from './getMessage'
//console.log(getMessage(5));
global.$ = $;
