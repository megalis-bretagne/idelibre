import $ from 'jquery';

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
