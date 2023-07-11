const hasRoleInput = document.querySelector("#user_role")
const hasRoleValue =  hasRoleInput.value
const roleActorId = "230a1c1d-eaec-4fb7-9ba7-d7ac47dc97bb";
const roleDeputyId = "eb74e130-b142-476c-9439-f06b58472a17";

const mandatorNameGroup = document.querySelector("#mandatorGroup")
const mandatorNameLabel = document.querySelector("#mandatorNameLabel")
const mandatorNameInput = document.querySelector("#user_associatedWith")
const mandatorNameValue = mandatorNameInput.value



window.onload = () => {

    listCleaner(mandatorNameInput);

    if (roleActorId === hasRoleValue) {
        show(mandatorNameGroup)
        getList("deputies")
        return ;
    }

    if(roleDeputyId === hasRoleValue) {
        show(mandatorNameGroup)
        getList("actors")
        return;
    }

    hide(mandatorNameGroup)
}
hasRoleInput.onchange = () => {
    let value = hasRoleInput.value
    let url = `/user/${getUserId()}list`

    if(value === roleActorId) {
        mandatorNameLabel.innerHTML = "Désigner un suppléant "
        getList(url, "deputies", mandatorNameInput)
        show(mandatorNameGroup)
        return;
    }

    if(value === roleDeputyId) {
        mandatorNameLabel.innerHTML = "<b>Associer un élu titulaire <span class='text-danger'>*</span></b>"
        getList(url, "actors", mandatorNameInput)
        show(mandatorNameGroup)
        return;
    }

    hide(mandatorNameGroup);
}

