import $ from 'jquery';


$("#sitting_calendar_isActive").change(function (event) {
    let $calendarDuration = $('#sitting_calendar_duration');
    if (event.currentTarget.checked) {
        $calendarDuration.attr('disabled', false);

        return;
    }
    $calendarDuration.attr('disabled', true);
});


$("#type_calendar_isActive").change(function (event) {
    let $calendarDuration = $('#type_calendar_duration');
    if (event.currentTarget.checked) {
        $calendarDuration.attr('disabled', false);

        return;
    }
    $calendarDuration.attr('disabled', true);
});
