import $ from 'jquery';

// Invalidation des tous les utilisateurs
const invalidatePassword =$('#invalidatePassword');
const dataInvalidate = invalidatePassword.data('invalidate');
const invalidateConfirmBtn = $('#invalidateConfirmBtn');
const cancelBtn = $('#invalidateCancelBtn');
const closeBtn = $('#close-invalidate')

confirmInvalidation(invalidatePassword, invalidateConfirmBtn, dataInvalidate);
clearInput(invalidateConfirmBtn, invalidatePassword, invalidateConfirmBtn);
clearInput(cancelBtn, invalidatePassword, invalidateConfirmBtn);
clearInput(closeBtn, invalidatePassword, invalidateConfirmBtn);

// Invalidate single user password
const invalidateInputs = document.querySelectorAll('.invalidateInput');
for (let i = 0; i < invalidateInputs.length; i++) {

    let invalidateInput = $(invalidateInputs[i]);
    let dataInvalidateSingle = $(invalidateInputs[i]).data('invalidate');
    let invalidateSingleConfirmBtn = invalidateInput.parents('.modal-content').find('.invalidateSingleConfirmBtn');
    let cancelSingleBtn = invalidateInput.parents('.modal-content').find('.invalidateSingleCancelBtn');
    let closeSingleBtn = invalidateInput.parents('.modal-content').find('.close-invalidate-single');

    confirmInvalidation(invalidateInput, invalidateSingleConfirmBtn, dataInvalidateSingle);
    clearInput(invalidateSingleConfirmBtn, invalidateInput, invalidateSingleConfirmBtn);
    clearInput(cancelSingleBtn, invalidateInput, invalidateSingleConfirmBtn);
    clearInput(closeSingleBtn, invalidateInput, invalidateSingleConfirmBtn);
}


function confirmInvalidation(target, confirmBtn, data) {
    $(target).bind("input changes", function() {
        const value =  $(this).val();

        if(value === data) {
            confirmBtn.removeAttr('disabled');
            return;
        }
        confirmBtn.attr('disabled', true);
    });
}

function clearInput(btn, target, toDisable ) {
    btn.on('click', function (e) {
        target.val("");
        toDisable.attr('disabled', true);
    });
}



