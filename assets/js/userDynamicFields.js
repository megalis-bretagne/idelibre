const hasRoleInput = document.querySelector("#user_role")
const hasRoleValue = hasRoleInput.value
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

    if(value === roleActorId) {
        mandatorNameLabel.innerHTML = "Désigner un suppléant "
        getList("deputies")
        show(mandatorNameGroup)
        return;
    }

    if(value === roleDeputyId) {
        mandatorNameLabel.innerHTML = "<b>Associer un élu titulaire <span class='text-danger'>*</span></b>"
        getList("actors")
        show(mandatorNameGroup)
        return;
    }

    hide(mandatorNameGroup);

}




function getList(value) {
    listCleaner(mandatorNameInput)
    ajaxListGeneration(value)
}

function ajaxListGeneration(value) {
    let httpRequest = new XMLHttpRequest();
    httpRequest.onreadystatechange = alterContents;
    httpRequest.open('GET', `/user/${getUserId()}list/${value}`);
    httpRequest.send();
    console.log("pas dans la requete ")

    function alterContents() {
        if (httpRequest.readyState === XMLHttpRequest.DONE && httpRequest.status === 200) {
            console.log("dans la requete")
            mandatorNameInput.innerHTML += httpRequest.responseText
            return false;
        } else {
            console.log('Il y a eu un problème avec la requête.');
        }
    }
}

function getUserId(){
    const voterId = window.location.pathname.split('/')[3];

    return voterId ? voterId + '/' : '';
}

function listCleaner(value) {
    let options = value.getElementsByTagName('option');
    for (let i = options.length; i--;) {
        if (i !== 0){
            value.removeChild(options[i]);
        }
    }
}

function hide(value) {
    value.classList.add('d-none')
    value.children[1].setAttribute('disabled', 'disabled')
    value.children[1].required = false
}

function show(value) {
    value.classList.remove('d-none')
    value.children[1].removeAttribute('disabled')
}


