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

    if (window.location.href.split("/").pop() === 'add') {
        updateReminder();
    }


});

reminderIsActiveTrue.addEventListener('change', function (event) {
    console.log('ok');
    if (reminderIsActiveTrue.checked) {
        reminderDuration.removeAttribute('disabled');
    }
});

reminderIsActiveFalse.addEventListener('change', function (event) {
    if (reminderIsActiveFalse.checked) {
        reminderDuration.setAttribute('disabled', 'disabled');
    }
});


$('#sitting_type').change(function (event) {
    updateReminder(event.target.value)
})

function updateReminder() {
    const typeId = $('#sitting_type').val();
    $.get('/type/reminder/' + typeId, function (res) {
        if (res.isActive) {
            reminderIsActiveTrue.checked = true;
            reminderDuration.removeAttribute('disabled');
        } else {
            reminderIsActiveFalse.checked = true;
            reminderDuration.setAttribute('disabled', 'disabled');
        }

        reminderDuration.value = res.duration ?? 120;
    });
}
