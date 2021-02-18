import './styles/app.css';

import $ from 'jquery'
import 'bootstrap'
import bsCustomFileInput from 'bs-custom-file-input'
import flatpickr from "flatpickr";
import {French} from "flatpickr/dist/l10n/fr"
import './ls-sidebar/ls-sidebar'


$(document).ready(function () {
    bsCustomFileInput.init()
    let config = {
        enableTime: true,
        altInput: true,
        altFormat: "d/m/y : H:i",
        "locale": French
    };
     flatpickr($('input[type=datetime-local]'), config);


    $("input.custom-file-upload").change(function () {
        let $input = $(this);
        $input.parent().attr('hidden', true);
        let $detailDiv = $(this).parent().next();
        let removeBtn = `<button  type="button" class="btn btn-outline-danger borderless"><span class="fas fa-trash-alt"> </span></button>`
        $detailDiv.html(removeBtn + $(this)[0].files[0].name);
        $detailDiv.attr('hidden', false);

        $detailDiv.children().first().click(function() {
            $detailDiv.attr('hidden', true);
            $input.val('');
            $input.parent().attr('hidden', false);
        });

    })

})

//const getMessage = require('./getMessage');
//import getMessage from './getMessage'
//console.log(getMessage(5));
//global.$ = $;
