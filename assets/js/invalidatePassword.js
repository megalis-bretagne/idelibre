import $ from 'jquery';

const invalidatePassword =$('#invalidatePassword');
const invalidateSinglePassword = $('#invalidateSinglePassword');
const dataInvalidate = invalidatePassword.data('invalidate');
const dataInvalidateSingle = invalidateSinglePassword.data('invalidate');
const invalidateConfirmBtn = $('#invalidateConfirmBtn');
const invalidateSingleConfirmBtn = $('#invalidateSingleConfirmBtn');
const cancelBtn = $('#invalidateCancelBtn');
const cancelSingleBtn = $('#invalidateSingleCancelBtn');


confirmInvalidation(invalidatePassword, invalidateConfirmBtn, dataInvalidate);
confirmInvalidation(invalidateSinglePassword, invalidateSingleConfirmBtn, dataInvalidateSingle);
clearInput(invalidateConfirmBtn, invalidatePassword);
clearInput(cancelBtn, invalidatePassword);
clearInput(invalidateSingleConfirmBtn, invalidateSinglePassword);
clearInput(cancelSingleBtn, invalidateSinglePassword);

function confirmInvalidation(target, confirmBtn, data) {
    target.bind("input changes", function() {
        const value =  $(this).val();

        if(value === data) {
            confirmBtn.removeAttr('disabled');
            return;
        }
        confirmBtn.attr('disabled', true);
    });
}

function clearInput(btn, target ) {
    btn.on('click', function (e) {
        target.val("");
    });
}



