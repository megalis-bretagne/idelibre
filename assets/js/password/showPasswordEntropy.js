import $ from "jquery";
import '@libriciel/ls-jquery-password/dist/js/ls-jquery-password.min';

$(document).ready(function () {
    let fieldPassword = $('.showValidationPasswordEntropy')[0],
        minEntropy = $(fieldPassword).attr('data-minimum-entropy');

    $(fieldPassword)
        .lsPasswordStrengthMeter( {
            "className": "ls-password-strength-meter",
            "inputGroupClass": "input-group",
            "inputGroupTag": "div",
            "thresholds": [
                { "value": 0, "className": "bg-danger" },
                { "value": minEntropy/2, "className": "bg-warning" },
                { "value": minEntropy, "className": "bg-success" }
            ]
        })
        .lsPasswordToggler($.fn.lsPasswordToggler.configure('5.0'));
});