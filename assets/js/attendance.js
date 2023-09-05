const attendanceInput = document.querySelector('#attendance_attendance');
const mandataireGroup = document.querySelector('#attendance_mandataire_group');
const deputyGroup = document.querySelector('#attendance_deputy_group')
const deputy = document.querySelector("h1").dataset.deputy

getToken = () => {
    const token = window.location.pathname.split("/")[3]
    return token + ''

}
let url = `/attendance/${getToken()}`

attendanceInput.onchange = () => {

    let attendanceValue = attendanceInput.value;

    if(attendanceValue === "poa"){
        show(mandataireGroup)
        deputy ? hide(deputyGroup) : console.log('')
        return;
    }

    if( deputy && attendanceValue === "deputy"){
        show(deputyGroup)
        hide(mandataireGroup)
        return;
    }
    hide(mandataireGroup)
    hide(deputyGroup)
}








