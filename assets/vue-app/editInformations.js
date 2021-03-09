import './vue-app.css';
import $ from 'jquery'
import 'bootstrap'


let isDirty = false;
$('input, select').click(function () {
    isDirty = true;
});


$('.change-tab').click(function (event) {

    if ($(this).hasClass('active')) {
        event.preventDefault();
        return false;
    }

    if (isDirty) {
        event.preventDefault();
        $('#confirm-btn').attr('href', $(this).attr('href'));
        $('#confirm-not-save').modal();

        return false;
    }
    return true;
});
