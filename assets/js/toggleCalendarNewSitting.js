import $ from 'jquery';


$("#sitting_calendar_isActive").change(function (event) {
    let $calendarDuration = $('#sitting_calendar_duration');
    if (event.currentTarget.checked) {
        $calendarDuration.attr('disabled', false);

        return;
    }
    $calendarDuration.attr('disabled', true);
});


$("document").ready(function () {
    let typeId = $('#sitting_type').val();
    if (!typeId) return;
    updateCalendar(typeId);
})


$('#sitting_type').change(function(event) {
    updateCalendar(event.target.value)
})

function updateCalendar(typeId) {
    $.get('/type/calendar/' + typeId, function (res) {
        console.log(res);
        let $calendarDuration = $('#sitting_calendar_duration');
        $('#sitting_calendar_isActive').attr('checked', res.isActive);
        $calendarDuration.attr('disabled', !res.isActive)
        $calendarDuration.val(res.duration ?? 120);
    });
}
