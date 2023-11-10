import $ from 'jquery';

const initPasswordTrue = document.querySelector('#user_initPassword_1');
const initPasswordFalse = document.querySelector('#user_initPassword_0');
const passwordGroup = document.querySelector('.password-group');


initPasswordTrue.addEventListener('change', function () {
    if (initPasswordTrue.checked) {
        passwordGroup.classList.remove('d-none');
    }
});

initPasswordFalse.addEventListener('change', function () {
    if (initPasswordFalse.checked) {
        passwordGroup.classList.add('d-none');
    }
});

document.addEventListener('load', function () {
    if (initPasswordTrue.checked) {
        passwordGroup.classList.remove('d-none');
    }
    passwordGroup.classList.add('d-none');
});


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
