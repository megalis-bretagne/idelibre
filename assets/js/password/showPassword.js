import $ from 'jquery'

$(document).ready(function () {
    $("#showPassword").on('click', function (event) {
        event.preventDefault();
        let $passwordInput = $('#show_hide_password input');
        let $passwordSpan = $('#showPassword');

        if ($passwordInput.attr("type") === "text") {
            $('#show_hide_password input').attr('type', 'password');
            $passwordSpan.addClass("fa-eye-slash");
            $passwordSpan.removeClass("fa-eye");
            return;
        }

        $passwordInput.attr('type', 'text');
        $passwordSpan.removeClass("fa-eye-slash");
        $passwordSpan.addClass("fa-eye");
    });
});
