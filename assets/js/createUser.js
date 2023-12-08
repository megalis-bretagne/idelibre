const adminRoleId = document.querySelector('#user_role').getAttribute('data-roleAdmin');
const actorRoleId = document.querySelector('#user_role').getAttribute('data-roleActor');
const roleInput = document.querySelector('#user_role');
const actorInfo = document.querySelector('#actor-info');
const passwordInitGroup = document.querySelector('#passwordInitGroup');
const passwordGroup = document.querySelector('#passwordGroup');
const initPasswordTrue = document.querySelector('#user_initPassword_0');
const initPasswordFalse = document.querySelector('#user_initPassword_1');


window.addEventListener('load', function () {

    if (roleInput.value === adminRoleId) {
        hidePasswordForAdmin()
        return;
    }

    if (roleInput.value === actorRoleId) {
        showDataForActor()
        return;
    }

    if (!initPasswordFalse.checked && !initPasswordTrue.checked) {
        initPasswordFalse.checked = true;
        return;
    }

    passwordInitGroup.classList.remove('d-none');
    actorInfo.classList.add('d-none');
});


roleInput.addEventListener('change', function () {

    if (roleInput.value === adminRoleId) {
        hidePasswordForAdmin()
        return;
    }

    if (roleInput.value === actorRoleId) {
        showDataForActor()
        return;
    }
    passwordInitGroup.classList.remove('d-none');
    actorInfo.classList.add('d-none');

});

function hidePasswordForAdmin() {
    passwordInitGroup.classList.add('d-none');
    passwordGroup.classList.add('d-none');
    actorInfo.classList.add('d-none');
    initPasswordFalse.checked = true;

}

function showDataForActor() {
    actorInfo.classList.remove('d-none');
    passwordInitGroup.classList.remove('d-none');
    initPasswordTrue.checked = true;
    passwordGroup.classList.remove('d-none');
}

initPasswordTrue.addEventListener('change', function () {
    passwordGroup.classList.remove('d-none');
});
initPasswordFalse.addEventListener('change', function () {
    passwordGroup.classList.add('d-none');
} );




