import './styles/app.css';

import $ from 'jquery'
import 'bootstrap'
import 'bs-custom-file-input'
import bsCustomFileInput from 'bs-custom-file-input'
import flatpickr from "flatpickr";
import {French} from "flatpickr/dist/l10n/fr"
import './ls-sidebar/ls-sidebar'
import './js/ls-file'
import './js/comelus'
import './js/lsmessage'
import './js/expandable'
import './js/refreshApiKey'

$(document).ready(function () {
    let config = {
        enableTime: true,
        altInput: true,
        altFormat: "d/m/Y : H:i",
        "locale": French,
        allowInput: true
    };
    flatpickr($('input[type=datetime-local]'), config);

    bsCustomFileInput.init()
})




//const getMessage = require('./getMessage');
//import getMessage from './getMessage'
//console.log(getMessage(5));
global.$ = $;
