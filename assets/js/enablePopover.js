import $ from 'jquery'
import 'bootstrap'

$(function () {
    $('[data-bs-toggle="popover"]').popover({
        html: true,
    })
});
