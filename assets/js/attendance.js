import $ from "jquery";

$('document').ready(() => {


    let $selectAttendance= $("#attendance_attendance");
    let $deputyDiv = $('#deputy-div');
    let $deputyInput= $('#attendance_deputy');

    $selectAttendance.change(() => {
        const status = $selectAttendance.val();
        if(status === 'absent') {
            $deputyDiv.removeClass('d-none');
            $deputyInput.attr('disabled', false)
            return;
        }

        $deputyDiv.addClass('d-none');
        $deputyInput.attr('disabled', true)
    })

})