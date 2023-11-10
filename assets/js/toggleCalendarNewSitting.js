import $ from 'jquery';


const reminderIsActiveTrue = document.querySelector('#sitting_reminder_isActive_0');
const reminderIsActiveFalse = document.querySelector('#sitting_reminder_isActive_1');
const reminderDuration = document.querySelector('#sitting_reminder_duration');
const sittingReminder = document.querySelector('#sitting_reminder');

window.addEventListener('load', function (event) {
    if (reminderIsActiveFalse.checked) {
        reminderDuration.setAttribute('disabled', 'disabled');
    }

    if (sittingReminder !== null && sittingReminder.parentNode.contains(document.querySelector('.isDisabled'))) {
        if (!reminderIsActiveTrue.checked) {
            reminderIsActiveTrue.setAttribute('disabled', 'disabled');
        }
    }
});

reminderIsActiveTrue.addEventListener('change', function (event) {
    if (reminderIsActiveTrue.checked) {
        reminderDuration.removeAttribute('disabled');
    }
});

reminderIsActiveFalse.addEventListener('change', function (event) {
    if (reminderIsActiveFalse.checked) {
        reminderDuration.setAttribute('disabled', 'disabled');
    }
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
