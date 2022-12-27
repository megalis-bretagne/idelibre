import $ from 'jquery';

$(document).ready(function () {

    if (0 == $('#user_initPassword').val()) {
        hidePlainPassword();
    }

    $('#user_initPassword').change(function() {
        if (1 == $(this).val()) {
            resetPlainPassword();

            $('#user_plainPassword_first').parent().parent().show();
            $('#user_plainPassword_second').parent().parent().show();
        } else {
            hidePlainPassword();
        }
    });
});

function hidePlainPassword()
{
    resetPlainPassword();

    $('#user_plainPassword_first').parent().parent().hide();
    $('#user_plainPassword_second').parent().parent().hide();
}

function resetPlainPassword()
{
    let firstPlainPassword = $('#user_plainPassword_first');
    let secondPlainPassword = $('#user_plainPassword_second');

    $(firstPlainPassword).val('');
    $(secondPlainPassword).val('');

    let firstPlainPasswordProgesseBar = $(firstPlainPassword).parent().parent().find('.progress-bar');
    $(firstPlainPasswordProgesseBar).attr('style', 'width:0%;');
    $(firstPlainPasswordProgesseBar).attr('aria-valuenow', '0');

    let secondPlainPasswordProgesseBar = $(secondPlainPassword).parent().parent().find('.progress-bar');
    $(secondPlainPasswordProgesseBar).attr('style', 'width:0%;');
    $(secondPlainPasswordProgesseBar).attr('aria-valuenow', '0');
}