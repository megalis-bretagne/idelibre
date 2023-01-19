import $ from "jquery";

$('document').ready(() => {

    $('#deputy').addClass('d-none')
    $('#presentiel').addClass('d-none')

    $('#confirmPresence').change(() => {
        if (!($('#deputy').hasClass('d-none'))) {
            $('#deputy').addClass('d-none')
        }

        if ($('#presentiel').hasClass('d-none')) {
            $('#presentiel').removeClass('d-none')
        }
    })

    $('#confirmAbsence').change(() => {
        $('#deputy').removeClass('d-none')
        $('#presentiel').addClass('d-none')

    })

})