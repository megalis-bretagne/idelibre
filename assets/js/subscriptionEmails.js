let subscriptionEmails = document.querySelector('#subscriptionEmails').data('subscription');
const checkboxYes = document.querySelector('#user_preference_subscription_acceptMailRecap_0');
const checkboxNo = document.querySelector('#user_preference_subscription_acceptMailRecap_1');

document.addEventListener('load', function () {
   checkYes();
    checkNo();
});

document.addEventListener('change', function () {
    checkYes();
    checkNo();
});


function checkYes() {
    if (subscriptionEmails === 'true') {
        checkboxYes.checked = true;
    }
}

function checkNo() {
    if (subscriptionEmails === '') {
        checkboxNo.checked = true;
    }
}
