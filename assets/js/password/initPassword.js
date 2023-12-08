const initPasswordTrue = document.querySelector('#user_initPassword_0');
const initPasswordFalse = document.querySelector('#user_initPassword_1');
const passwordGroup = document.querySelector('#passwordGroup');


window.addEventListener('load', function () {

    if (!initPasswordFalse.checked && !initPasswordTrue.checked) {
        initPasswordFalse.checked = true;
    }

    if (initPasswordFalse.checked) {
        // console.log('passwordInitFalse')
        passwordGroup.classList.add('d-none');
    }

});

initPasswordTrue.addEventListener('change', function () {
    passwordGroup.classList.remove('d-none');
});
initPasswordFalse.addEventListener('change', function () {
    passwordGroup.classList.add('d-none');
} );
