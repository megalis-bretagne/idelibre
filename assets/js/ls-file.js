import $ from "jquery";

$(document).ready(function () {
    $("input.ls-file").each(function () {
        let $input = $(this);
        let $detailDiv = $(this).parent().next();
        $(this).change(function() {
            $input.parent().attr('hidden', true);
            let $fileNameSpan = $(this).parent().next().children().eq(1);
            $fileNameSpan.html($(this)[0].files[0].name);
            $detailDiv.attr('hidden', false);
        })

        let $deleteBtn = $(this).parent().next().children().first();

        $deleteBtn.click(function () {
            if( !$deleteBtn.hasClass("delete-file")) {
                return;
            }
            $detailDiv.attr('hidden', true);
            $input.val('');
            $input.parent().attr('hidden', false);
        });

    });

    const invitationGroup = $("label[for='sitting_invitationFile']").parent();
    const convocationInput = $('#sitting_convocationFile')
    const trash = $(invitationGroup).children().eq(3).children().eq(0);
    let sittingId = window.location.pathname.split('/')[3]
    async function removeInvitationFile() {
        await fetch(`/sitting/${sittingId}/information/removeInvitation`);
    }
    $(trash).click(function () {
        removeInvitationFile();
    })

    if ($(convocationInput).attr('disabled') === 'disabled') {
        $(trash).remove()
    }

})
