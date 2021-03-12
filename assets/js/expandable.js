import $ from 'jquery'

$('.expandable').click(function () {
    if ($(this).hasClass('expand-icon')) {
        $(this).parent().siblings().first().removeClass('hidden');
        $(this).closest('table').children().eq(1).removeClass('hidden');
        $(this).removeClass('expand-icon').addClass('collapse-icon')
        return;
    }
    $(this).parent().siblings().first().addClass('hidden');
    $(this).closest('table').children().eq(1).addClass('hidden');
    $(this).removeClass('collapse-icon').addClass('expand-icon')

});
