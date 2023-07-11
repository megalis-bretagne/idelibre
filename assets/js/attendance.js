const attendanceInput = document.querySelector('#attendance_attendance');
const replacementTypeGroup = document.querySelector("#attendanceStatusGroup");
const replacementTypeInput = document.querySelector("#attendance_status");
const deputyGroup = document.querySelector('#deputyGroup');
const deputyInput = document.querySelector('#attendance_deputy')
const deputy = document.querySelector("h1").dataset.deputy


window.onload = () => {
    const value = attendanceInput.value;

    if(value === "suppleant"){
        deputyGroup.classList.remove('d-none');
        deputyInput.setAttribute('disabled', 'disabled')
        deputyInput.innerHTML += `<option value="${deputy}">${deputy}</option>`
        deputy ? deputyInput.value = deputy : getList('deputy', deputyInput);
        return;
    }

    if(value === "procuration") {
        deputyGroup.classList.remove('d-none');
        deputyInput.removeAttribute("disabled");
        return;
    }
    deputyInput.required = false
    deputyGroup.classList.add('d-none');
    deputyGroup.setAttribute('disabled', 'disabled');
}

attendanceInput.onchange = () => {
    const value = attendanceInput.value;
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
        deputyInput.innerHTML += `<option value="${deputy}">${deputy}</option>`
        deputy ? deputyInput.value = deputy : getList(url ,'deputies', deputyInput);
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





