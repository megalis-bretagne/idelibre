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
            $detailDiv.attr('hidden', true);
            $input.val('');
            $input.parent().attr('hidden', false);
        });

    });

})
