window.hide = (value) => {
    value.classList.add('d-none')
    value.children[1].setAttribute('disabled', 'disabled')
    value.children[1].required = false
}

window.show = (value) => {
    value.classList.remove('d-none')
    value.children[1].removeAttribute('disabled');
}
