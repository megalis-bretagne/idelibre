const adminRoleId = '17f4b8ba-7a34-4463-9901-88b619a64be3';
const actorRoleId = '230a1c1d-eaec-4fb7-9ba7-d7ac47dc97bb';
const roleInput = document.querySelector('#user_role');
const passwordInitGroup = document.querySelector('#passwordInitGroup');
const actorInfo = document.querySelector('#actor-info');

window.onload = () => {
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
}

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
    actorInfo.classList.add('d-none');
}

function showDataForActor() {
    actorInfo.classList.remove('d-none');
    passwordInitGroup.classList.remove('d-none');
}




