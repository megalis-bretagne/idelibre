import $ from 'jquery';


$("#sitting_reminder_isActive").change(function (event) {
    let $reminderDuration = $('#sitting_reminder_duration');
    if (event.currentTarget.checked) {
        $reminderDuration.attr('disabled', false);

        return;
    }
    $reminderDuration.attr('disabled', true);
});


$("document").ready(function () {
    let typeId = $('#sitting_type').val();
    if (!typeId) return;
    updateReminder(typeId);
})


$('#sitting_type').change(function(event) {
    updateReminder(event.target.value)
})

function updateReminder(typeId) {
    $.get('/type/reminder/' + typeId, function (res) {
        console.log(res);
        let $reminderDuration = $('#sitting_reminder_duration');
        $('#sitting_reminder_isActive').attr('checked', res.isActive);
        $reminderDuration.attr('disabled', !res.isActive)
        $reminderDuration.val(res.duration ?? 120);
    });
}
