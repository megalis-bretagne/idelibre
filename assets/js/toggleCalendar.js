import $ from 'jquery';


$("#sitting_reminder_isActive").change(function (event) {
    let $reminderDuration = $('#sitting_reminder_duration');
    if (event.currentTarget.checked) {
        $reminderDuration.attr('disabled', false);

        return;
    }
    $reminderDuration.attr('disabled', true);
});


$("#type_reminder_isActive").change(function (event) {
    let $reminderDuration = $('#type_reminder_duration');
    if (event.currentTarget.checked) {
        $reminderDuration.attr('disabled', false);

        return;
    }
    $reminderDuration.attr('disabled', true);
});
