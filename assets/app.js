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
import './js/lsvote'
import './js/expandable'
import './js/refreshApiKey'
import './js/showPassword'
import './js/utils/dropdownOptionCleaner'
import './js/utils/retrieveFromUrl'
import './js/utils/ajaxListGenerator'
import './js/utils/showHide'
import './js/attendance'

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

