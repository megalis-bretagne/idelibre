const attendanceInput = document.querySelector('#attendance_attendance');
const replacementTypeGroup = document.querySelector("#attendanceStatusGroup");
const replacementTypeInput = document.querySelector("#attendance_status");
const mandataireGroup = document.querySelector('#mandataireGroup');
const mandataireInput = document.querySelector('#attendance_mandataire')
const deputy = document.querySelector("h1").dataset.deputy
console.log(deputy)
const deputyId = document.querySelector("h1").dataset.deputy_id
const flashNoDeputy ='<div class="alert alert-danger" role="alert">Aucun suppléant ne vous est assigné </div>'

getToken = () => {
    const token = window.location.pathname.split("/")[3]
    return token + ''

}
let url = `/attendance/${getToken()}`



window.onload = () => {
    let attendanceValue = attendanceInput.value ? attendanceInput.value : null;

    listCleaner(mandataireInput)

    if( "absent" === attendanceValue){
        show(replacementTypeGroup)
        return;
    }
    const value = replacementTypeInput.value;

    if("deputy" === value ) {
        listCleaner(mandataireInput)
        show(mandataireGroup)

        if(!deputy) {
            mandataireInput.innerHTML += getList(url, 'deputies', mandataireInput)
            mandataireInput.removeAttribute('disabled')
            return;
        }

        mandataireInput.innerHTML += `<option value="${deputyId}">${deputy}</option>`
        // mandataireInput.value = deputy
        mandataireInput.setAttribute('readonly', 'readonly')
        return;
    }

    if("poa" === value) {
        listCleaner(mandataireInput)
        show(mandataireGroup)
        mandataireInput.innerHTML += getList(url, "actors", mandataireInput)
        return;
    }

    if ("present" === value) {
        hide(replacementTypeGroup)
        hide(mandataireGroup)
    }

    hide(replacementTypeGroup)
}

attendanceInput.onchange = () => {
    let value = attendanceInput.value ? attendanceInput.value : null;
    console.log(value)

    if(value === "absent"){
        show(replacementTypeGroup)
        return;
    }
    hide(replacementTypeGroup)
}

replacementTypeInput.onchange = () => {
    const value = replacementTypeInput.value;

    if("deputy" === value ) {
        listCleaner(mandataireInput)
        show(mandataireGroup)

        if(!deputy) {
            mandataireInput.innerHTML += flashNoDeputy
            return;
        }
        mandataireInput.innerHTML += `<option value="${deputy}">${deputy}</option>`
        mandataireInput.setAttribute('readonly', 'readonly')
        return;
    }

    if("poa" === value) {
        listCleaner(mandataireInput)
        show(mandataireGroup)
        mandataireInput.innerHTML += getList(url, "actors", mandataireInput)
        return;
    }

    hide(mandataireGroup)
}






