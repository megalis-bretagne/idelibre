const attendanceInput = document.querySelector('#attendance_attendance');
const replacementTypeGroup = document.querySelector("#attendanceStatusGroup");
const replacementTypeInput = document.querySelector("#attendance_status");
const deputyGroup = document.querySelector('#deputyGroup');
const deputyInput = document.querySelector('#attendance_deputy')
const deputy = document.querySelector("h1").dataset.deputy
const deputyId = document.querySelector("h1").dataset.deputy_id
let url = `/attendance/${getToken()}list`

window.onload = () => {
    let attendanceValue = attendanceInput.value ? attendanceInput.value : null;

    listCleaner(deputyInput)

    if( "absent" === attendanceValue){
        show(replacementTypeGroup)
        return;
    }
    const value = replacementTypeInput.value;

    if("deputy" === value ) {
        listCleaner(deputyInput)
        show(deputyGroup)

        if(!deputy) {
            deputyInput.innerHTML += getList(url, 'deputies', deputyInput)
            deputyInput.removeAttribute('disabled')
            return;
        }

        deputyInput.innerHTML += `<option value="${deputyId}">${deputy}</option>`
        // deputyInput.value = deputy
        deputyInput.setAttribute('disabled', 'disabled')
        return;
    }

    if("poa" === value) {
        listCleaner(deputyInput)
        show(deputyGroup)
        deputyInput.innerHTML += getList(url, "actors", deputyInput)
        return;
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
        listCleaner(deputyInput)
        show(deputyGroup)

        if(!deputy) {
            deputyInput.innerHTML += getList(url, 'deputies', deputyInput)
            deputyInput.removeAttribute('disabled')
            return;
        }

        deputyInput.innerHTML += `<option value="${deputyId}">${deputy}</option>`
        // deputyInput.value = deputy
        deputyInput.setAttribute('disabled', 'disabled')
        return;
    }

    if("poa" === value) {
        listCleaner(deputyInput)
        show(deputyGroup)
        deputyInput.innerHTML += getList(url, "actors", deputyInput)
        return;
    }

    hide(deputyGroup)
}





