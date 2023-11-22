import $ from 'jquery';

const invalidatePassword = document.querySelector('#invalidatePassword');
const dataInvalidate = invalidatePassword.dataset.invalidate;
const cancelBtn = $('#invalidateCancelBtn').children('button.btn-link');
console.log(cancelBtn);

$("#invalidatePassword").bind("input changes", function() {
    const value =  $(this).val();
    console.log(value , dataInvalidate);

    if(value === dataInvalidate) {
        console.log('ok')
        $('#invalidateConfirmBtn').removeAttr('disabled');
        return;
    }
    $('#invalidateConfirmBtn').attr('disabled', true);
});

$('#invalidateConfirmBtn').on('click', function () {
    invalidatePassword.value ="";
});

$("#invalidataCancelBtn").on('click', function () {
    invalidatePassword.value ="";
});


