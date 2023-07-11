const hasRoleInput = document.querySelector("#user_role")
const hasRoleValue = hasRoleInput.value
const roleActorId = "230a1c1d-eaec-4fb7-9ba7-d7ac47dc97bb";

const isDeputyGroup = document.querySelector("#isDeputyGroup")
const isDeputyInput = document.querySelector("#user_isDeputy");
let isDeputyValue = isDeputyInput.value

const mandatorTypeGroup = document.querySelector("#mandatorTypeGroup")
const mandatorTypeInput = document.querySelector("#user_mandatorType")
const mandatorTypeValue = mandatorTypeInput.value

const mandatorNameGroup = document.querySelector("#mandatorGroup")
const mandatorNameInput = document.querySelector("#user_mandator")
const mandatorNameValue = mandatorNameInput.value



window.onload = () => {

    listCleaner(mandatorNameInput);

    if (roleActorId === hasRoleValue) {
        show(isDeputyGroup)
        show(mandatorTypeGroup)
        hide(mandatorNameGroup)
        return false;
    }

    if (roleActorId !== hasRoleValue) {
        hide(isDeputyGroup)
        isDeputyInput.removeAttribute("disabled")
        hide(mandatorNameGroup)
        hide(mandatorTypeGroup)
        return false;
    }

    if (roleActorId === hasRoleValue && '0' === isDeputyValue) {
        show(mandatorTypeGroup)
        hide(mandatorNameGroup)
        return 0;
    }
    if(roleActorId === hasRoleValue && '1' === isDeputyValue) {
        hide(mandatorTypeGroup)
        show(mandatorNameGroup)
        return 0;
    }

    if(roleActorId === hasRoleValue && '0' === isDeputyValue && "null" !== mandatorTypeValue ) {
        show(mandatorNameGroup)
        return 0;
    }

}
hasRoleInput.onchange = () => {
    let value = hasRoleInput.value
    if(value === roleActorId) {
        console.log('role actor => show isDeputy and show mandatorType')
        show(isDeputyGroup)
        show(mandatorTypeGroup)
        show(mandatorNameGroup)
        return 0;
    }
    console.log('role pas actor => hide isDeputy and hide mandatorType')
    hide(isDeputyGroup)
    hide(mandatorTypeGroup)
    isDeputyInput.value = false;
    return 0;
}

isDeputyInput.onchange = () => {
    let value = isDeputyInput.value;

    if ("0" === value) {
        console.log("pas suppleant => show mandatorType")
        show(mandatorTypeGroup)
        show(mandatorNameGroup)
    }

    if("1" === value) {
        console.log('est suppleant => hide mandatorType  show mandatorName')
        hide(mandatorTypeGroup)
        show(mandatorNameGroup)
        getList('actors')
    }

}

mandatorTypeInput.onchange = () => {
    let value = mandatorTypeInput.value;
    console.log(typeof (value))
    isDeputyInput.value === "0" ? isDeputyInput.value = false: console.log("false");

    if(null === value){
        hide(mandatorNameGroup)
        listCleaner(mandatorNameInput)
        return 0;
    }

    if("1" === value) {
        console.log("list des suppleants")
        getList("deputies")
        return 0;
    }

    if("2" === value) {
        console.log("list des mandator")
        getList("actors")
        return 0;
    }
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
