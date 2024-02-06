import $ from 'jquery';

// secure invalidate multiple users password
const invalidatePassword =$('#invalidate-password');
const dataInvalidate = invalidatePassword.data('invalidate');
const invalidateConfirmBtn = $('#invalidate-confirm-btn');
const cancelBtn = $('#invalidate-cancel-btn');
const closeBtn = $('#close-invalidate')

secureConfirmation(invalidatePassword, invalidateConfirmBtn, dataInvalidate);
clearInput(invalidateConfirmBtn, invalidatePassword, invalidateConfirmBtn);
clearInput(cancelBtn, invalidatePassword, invalidateConfirmBtn);
clearInput(closeBtn, invalidatePassword, invalidateConfirmBtn);

// secure invalidation user password
const invalidateInputs = document.querySelectorAll('.invalidate-input');
for (let i = 0; i < invalidateInputs.length; i++) {

    let invalidateInput = $(invalidateInputs[i]);
    let dataInvalidateSingle = $(invalidateInputs[i]).data('invalidate');
    let invalidateSingleConfirmBtn = invalidateInput.parents('.modal-content').find('.invalidate-single-confirm-btn');
    let cancelSingleBtn = invalidateInput.parents('.modal-content').find('.invalidate-single-cancel-btn');
    let closeSingleBtn = invalidateInput.parents('.modal-content').find('.close-invalidate-single');

    secureConfirmation(invalidateInput, invalidateSingleConfirmBtn, dataInvalidateSingle);
    clearInput(invalidateSingleConfirmBtn, invalidateInput, invalidateSingleConfirmBtn);
    clearInput(cancelSingleBtn, invalidateInput, invalidateSingleConfirmBtn);
    clearInput(closeSingleBtn, invalidateInput, invalidateSingleConfirmBtn);
}


// secure single user/structure deletion
const deleteInputs = document.querySelectorAll('.delete-input');
for (let j = 0; j < deleteInputs.length; j++) {

        let deleteInput = $(deleteInputs[j]);
        let dataDelete = $(deleteInputs[j]).data('delete');
        let deleteConfirmBtn = deleteInput.parents('.modal-content').find('.delete-btn');
        let cancelDeleteBtn = deleteInput.parents('.modal-content').find('.cancel-delete-btn');
        let closeDeleteBtn = deleteInput.parents('.modal-content').find('.delete-close-btn');

        secureConfirmation(deleteInput, deleteConfirmBtn, dataDelete);
        clearInput(deleteConfirmBtn, deleteInput, deleteConfirmBtn);
        clearInput(cancelDeleteBtn, deleteInput, deleteConfirmBtn);
        clearInput(closeDeleteBtn, deleteInput, deleteConfirmBtn);
}


// secure multiple user deletion
let deleteBatchInput =  $('#delete-batch-input')
let dataDelete = deleteBatchInput.data('delete');
let deleteBatchConfirmBtn = deleteBatchInput.parents('.modal-content').find('#delete-batch-btn');
let cancelDeleteBtn = deleteBatchInput.parents('.modal-content').find('#cancel-delete-batch-btn');
let closeDeleteBatchBtn = deleteBatchInput.parents('.modal-content').find('#delete-batch-close-btn');

secureConfirmation(deleteBatchInput, deleteBatchConfirmBtn, dataDelete);
clearInput(deleteBatchConfirmBtn, deleteBatchInput, deleteBatchConfirmBtn);
clearInput(cancelDeleteBtn, deleteBatchInput, deleteBatchConfirmBtn);
clearInput(closeDeleteBatchBtn, deleteBatchInput, deleteBatchConfirmBtn);

function secureConfirmation(target, confirmBtn, data) {
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
        setTimeout(function() {
            toDisable.attr('disabled', true);
        }, 3000);
    });
}



