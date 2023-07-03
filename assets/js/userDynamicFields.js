const hasRoleInput = document.querySelector("#user_role")
const hasRoleValue = hasRoleInput.value
const roleActorId = "230a1c1d-eaec-4fb7-9ba7-d7ac47dc97bb";

const isDeputyGroup = document.querySelector("#isDeputyGroup")
const isDeputyInput = document.querySelector("#user_isDeputy");
let isDeputyValue = isDeputyInput.value

const mandatorNameGroup = document.querySelector("#mandatorGroup")
const mandatorNameLabel = document.querySelector("#mandatorNameLabel")
const mandatorNameInput = document.querySelector("#user_associatedWith")
const mandatorNameValue = mandatorNameInput.value



window.onload = () => {

    listCleaner(mandatorNameInput);

    if (roleActorId === hasRoleValue) {
        show(isDeputyGroup)
        show(mandatorNameGroup)
        if(isDeputyValue === "1") {
            getList("actors")
            return ;
        }
        getList("deputies")
        return ;
    }

    if (roleActorId !== hasRoleValue) {
        hide(isDeputyGroup)
        isDeputyInput.removeAttribute("disabled")
        hide(mandatorNameGroup)
        return false;
    }



}
hasRoleInput.onchange = () => {
    let value = hasRoleInput.value
    if(value === roleActorId) {
        console.log('role actor => show isDeputy')
        show(isDeputyGroup)
        show(mandatorNameGroup)
        getList("deputies")
        return 0;
    }
    console.log('role pas actor => hide isDeputy')
    hide(isDeputyGroup)
    isDeputyInput.value = false;
    return 0;
}

isDeputyInput.onchange = () => {
    let value = isDeputyInput.value;

    if ("1" === value) {
        // console.log('est suppleant => hide mandatorType  show mandatorName')
        console.log(mandatorNameLabel.innerHTML)
        mandatorNameLabel.innerHTML = "<b>Associer un élu <span class='text-danger'>*</span></b>";
        getList('actors')
        show(mandatorNameGroup)
        mandatorNameInput.setAttribute("required", "required")
        return false;
    }

    console.log("pas suppleant => show mandatorType")
    mandatorNameLabel.textContent = "Associer un suppléant";
    getList("deputies")
    show(mandatorNameGroup)
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

function replace(input, value, string){
    if(value === input) {
        let group = input+'Group';
        console.log(group)
        group.child("label").innerHTML = " 000 Associé un " + string
    }
}
