const attendanceInput = document.querySelector('#attendance_attendance');
const deputyGroup = document.querySelector('#deputyGroup');
const deputyInput = document.querySelector('#attendance_deputy')
const deputy = document.querySelector("h1").dataset.deputy


window.onload = () => {
    const value = attendanceInput.value;

    if(value === "suppleant"){
        deputyGroup.classList.remove('d-none');
        deputyInput.setAttribute('disabled', 'disabled')
        deputyInput.innerHTML += `<option value="${deputy}">${deputy}</option>`
        deputy ? deputyInput.value = deputy : '';
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

    if(value === "suppleant"){
        deputyGroup.classList.remove('d-none');
        deputyInput.setAttribute('disabled', 'disabled')
        deputyInput.innerHTML += `<option value="${deputy}">${deputy}</option>`
        deputy ? deputyInput.value = deputy : '';
        return;
    }

    if(value === "procuration") {
        deputyGroup.classList.remove('d-none');
        deputyInput.removeAttribute("disabled");
        getList("actors", deputyInput)
        return;
    }
    deputyInput.required = false
    deputyGroup.classList.add('d-none');
    deputyGroup.setAttribute('disabled', 'disabled');
}




