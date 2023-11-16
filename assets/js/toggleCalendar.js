const reminderIsActiveTrue = document.querySelector('#type_reminder_isActive_0');
const reminderIsActiveFalse = document.querySelector('#type_reminder_isActive_1');
const reminderDuration = document.querySelector('#type_reminder_duration');
const typeReminder = document.querySelector('#type_reminder');

window.addEventListener('load', function () {

    if (!reminderIsActiveTrue.checked && !reminderIsActiveFalse.checked) {
        reminderIsActiveFalse.checked = true;
    }

    if (reminderIsActiveFalse.checked) {
        reminderDuration.setAttribute('disabled', 'disabled');
    }

    if (typeReminder !== null && typeReminder.parentNode.contains(document.querySelector('.isDisabled'))) {
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


