const attendanceInput = document.querySelector('#attendance_attendance');
const mandataireGroup = document.querySelector('#attendance_mandataire_group');
const mandataireInput = document.querySelector('#attendance_mandataire');
const deputyGroup = document.querySelector('#attendance_deputy_group')
const deputy = document.querySelector("h1").dataset.deputy

getToken = () => {
    const token = window.location.pathname.split("/")[3]
    return token + ''

}
let url = `/attendance/${getToken()}`

window.onload = () => {
    let attendanceValue = attendanceInput?.value;

    if(attendanceValue === "poa"){
        show(mandataireGroup)
        mandataireInput.setAttribute("required", "required")
        hide(deputyGroup)
        return;
    }

    if( attendanceValue === "deputy" && deputy !== null){
        show(deputyGroup)
        hide(mandataireGroup)
        return;
    }
    hide(mandataireGroup)
    hide(deputyGroup)
}

attendanceInput.onchange = () => {
    let attendanceValue = attendanceInput?.value;


    if(attendanceValue === "poa"){
        show(mandataireGroup)
        mandataireInput.setAttribute("required", "required")
        hide(deputyGroup)
        return;
    }

    if( attendanceValue === "deputy" && deputy !== null){
        show(deputyGroup)
        hide(mandataireGroup)
        return;
    }
    hide(mandataireGroup)
    hide(deputyGroup)
}








