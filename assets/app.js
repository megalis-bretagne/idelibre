import './styles/app.css';

import $ from 'jquery'

import '@popperjs/core'
import '@libriciel/ls-bootstrap-5/il-bootstrap'
import "bootstrap"

import 'bs-custom-file-input'
import bsCustomFileInput from 'bs-custom-file-input'
import flatpickr from "flatpickr";
import {French} from "flatpickr/dist/l10n/fr"

import './ls-sidebar/ls-sidebar'
import './js/ls-file'
import './js/comelus'
import './js/lsmessage'
import './js/lsvote'
import './js/expandable'
import './js/refreshApiKey'
import './js/password/showPassword'
import './js/utils/retrieveFromUrl'
import './js/utils/showHide'
import './js/_navbarToggleColor'

$(document).ready(function () {
    let config = {
        enableTime: true,
        altInput: true,
        altFormat: "d/m/Y : H:i",
        "locale": French,
        allowInput: true
    };
    flatpickr($('input[type=datetime-local]'), config);

    bsCustomFileInput.init();


    $('.active_column').hide();
    $('.active_column').each(function () {
        if ($(this).html() == '') {
            $(this).parents('tr').addClass('inactiveLine');
        }
    });
})

global.$ = $;

