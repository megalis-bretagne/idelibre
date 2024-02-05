import $ from 'jquery';

// Invalidation des tous les utilisateurs
const invalidatePassword =$('#invalidatePassword');
const dataInvalidate = invalidatePassword.data('invalidate');
const invalidateConfirmBtn = $('#invalidateConfirmBtn');
const cancelBtn = $('#invalidateCancelBtn');
const closeBtn = $('#close-invalidate')

secureConfirmation(invalidatePassword, invalidateConfirmBtn, dataInvalidate);
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

    secureConfirmation(invalidateInput, invalidateSingleConfirmBtn, dataInvalidateSingle);
    clearInput(invalidateSingleConfirmBtn, invalidateInput, invalidateSingleConfirmBtn);
    clearInput(cancelSingleBtn, invalidateInput, invalidateSingleConfirmBtn);
    clearInput(closeSingleBtn, invalidateInput, invalidateSingleConfirmBtn);
}


// secure user deletion
const deleteInputs = document.querySelectorAll('.delete-input');
for (let i = 0; i < deleteInputs.length; i++) {

        let deleteInput = $(deleteInputs[i]);
        let dataDelete = $(deleteInputs[i]).data('delete');
        let deleteConfirmBtn = deleteInput.parents('.modal-content').find('.delete-user-btn');
        let cancelDeleteBtn = deleteInput.parents('.modal-content').find('.cancel-delete-user-btn');
        let closeDeleteBtn = deleteInput.parents('.modal-content').find('.close-delete-user');

        secureConfirmation(deleteInput, deleteConfirmBtn, dataDelete);
        clearInput(deleteConfirmBtn, deleteInput, deleteConfirmBtn);
        clearInput(cancelDeleteBtn, deleteInput, deleteConfirmBtn);
        clearInput(closeDeleteBtn, deleteInput, deleteConfirmBtn);
}



function secureConfirmation(target, confirmBtn, data) {
    $(target).bind("input changes", function() {
        const value =  $(this).val();
        console.log(data + " " + value)


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



