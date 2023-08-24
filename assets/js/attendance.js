const attendanceInput = document.querySelector('#attendance_attendance');
const replacementTypeGroup = document.querySelector("#attendanceStatusGroup");
const replacementTypeInput = document.querySelector("#attendance_status");
const mandataireGroup = document.querySelector('#attendance_mandataire_group');
const deputyGroup = document.querySelector('#attendance_deputy_group')
const deputy = document.querySelector("h1").dataset.deputy

getToken = () => {
    const token = window.location.pathname.split("/")[3]
    return token + ''

}
let url = `/attendance/${getToken()}`



window.onload = () => {
    let attendanceValue = attendanceInput.value ? attendanceInput.value : null;

    if( "absent" === attendanceValue){
        show(replacementTypeGroup)
        return;
    }
    const value = replacementTypeInput.value;

    if("deputy" === value ) {
        show(deputyGroup)
        hide(mandataireGroup)
        return;
    }

    if("poa" === value) {
        show(mandataireGroup)
        hide(deputyGroup)
        return;
    }

    if ("present" === value) {
        hide(replacementTypeGroup)
        hide(mandataireGroup)
        hide(deputyGroup)
    }

    hide(replacementTypeGroup)
}

attendanceInput.onchange = () => {
    let value = attendanceInput.value ? attendanceInput.value : null;

    if(value === "absent"){
        show(replacementTypeGroup)
        return;
    }
    hide(replacementTypeGroup)
}

replacementTypeInput.onchange = () => {
    const value = replacementTypeInput.value;

    if("deputy" === value ) {
        show(deputyGroup)
        hide(mandataireGroup)
        return;
    }

    if("poa" === value) {
        show(mandataireGroup)
        hide(deputyGroup)
        return;
    }

    hide(mandataireGroup)
    hide(deputyGroup)
}






