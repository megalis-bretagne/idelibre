const attendanceInput = document.querySelector('#attendance_attendance');
const replacementTypeGroup = document.querySelector("#attendanceStatusGroup");
const replacementTypeInput = document.querySelector("#attendance_status");
const deputyGroup = document.querySelector('#deputyGroup');
const deputyInput = document.querySelector('#attendance_deputy')
const deputy = document.querySelector("h1").dataset.deputy
const deputyId = document.querySelector("h1").dataset.deputyId

window.onload = () => {
    const value = attendanceInput.value ? attendanceInput.value : null;
    listCleaner(deputyInput)
}

attendanceInput.onchange = () => {
    const value = attendanceInput.value ? attendanceInput.value : null;
    console.log(value)

    if(value === "absent"){
        show(replacementTypeGroup)
        return;
    }

    hide(deputyGroup)
}

replacementTypeInput.onchange = () => {
    const value = replacementTypeInput.value;
    let url = `/attendance/${getToken()}list`

    if("deputy" === value ) {
        listCleaner(deputyInput)
        show(deputyGroup)
        deputyInput.innerHTML += `<option value="${deputyId}">${deputy}</option>`
        if(!deputy) {
            deputyInput.innerHTML += getList(url, 'deputies', deputyInput)
            deputyInput.removeAttribute('disabled')
            return;
        }
        deputyInput.value = deputy
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





