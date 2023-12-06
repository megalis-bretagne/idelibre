import $ from 'jquery';

const initPasswordTrue = document.querySelector('#user_initPassword_1');
const initPasswordFalse = document.querySelector('#user_initPassword_0');
const passwordGroup = document.querySelector('.password-group');


document.onload = () => {
    if (!(initPasswordTrue.checked === true) && !(initPasswordFalse.checked === true)) {
        initPasswordFalse.checked
    }

    if (initPasswordTrue.checked) {
        initPasswordTrue.check
        return;
    }
    initPasswordFalse.check
};

initPasswordTrue.addEventListener('change', function () {
    passwordGroup.classList.remove('d-none');
});
initPasswordFalse.addEventListener('change', function () {
    passwordGroup.classList.add('d-none');
} );





// const roleInput = document.querySelector('#user_role');
//
//
// /* Si le role admin est selectionné on cache la possibilite de créer le mot de passe */
// roleInput.addEventListener('change', function () {
//     if (roleInput.value === '230a1c1d-eaec-4fb7-9ba7-d7ac47dc97bb') {
//         initPasswordGroup.classList.add('d-none');
//         return;
//     }
//     initPasswordGroup.classList.remove('d-none');
// });
//
//
//
//
//
// initPasswordTrue.addEventListener('change', function () {
//     if (initPasswordTrue.checked) {
//         passwordGroup.classList.remove('d-none');
//     }
// });
//
// initPasswordFalse.addEventListener('change', function () {
//     if (initPasswordFalse.checked) {
//         passwordGroup.classList.add('d-none');
//     }
// });
//
// document.addEventListener('load', function () {
//     if (initPasswordTrue.checked) {
//         passwordGroup.classList.remove('d-none');
//     }
//     passwordGroup.classList.add('d-none');
// });
//
//
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
