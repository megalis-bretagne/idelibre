const myDropdown = $('#navDropdown');
const link =$('#navbarDropdown');
$(myDropdown).on('show.bs.dropdown', function () {
    link.css('color', 'var(--bs-ls-info-400)')
})
$(myDropdown).on('hide.bs.dropdown', function () {
    link.css('color', 'white')

})
