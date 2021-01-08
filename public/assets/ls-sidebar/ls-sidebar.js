$('.ls-sidebar li').click(function () {
    $(this).addClass('active');
    $(this).siblings().attr('class', "");

    removeSideSubMenu($(this).find('ul'))
})

$('.ls-sidebar .main-menu > li').hover(
    function () {
        $(this).addClass('over-menu');

        if ($(this).hasClass('active')) {
            return;
        }
        let ul = $(this).find('ul');
        ul.wrapInner('<div class="pop"></div>');
        ul.addClass('pop');

    },
    function () {
        $(this).removeClass('over-menu');
        if ($(this).hasClass('active')) {
            return;
        }
        removeSideSubMenu($(this).find('ul'))
    }
)

$('.ls-sidebar .sub-menu > li').click(function () {
    removeSideSubMenu($(this).closest('ul'));
});

function removeSideSubMenu(ulSubMenu) {
    ulSubMenu.removeClass('pop');
    ulSubMenu.find('div').contents().unwrap();
}
