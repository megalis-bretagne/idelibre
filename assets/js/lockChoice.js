window.addEventListener('load', function () {
    const isDisabled = document.getElementsByClassName('isDisabled');
    for (let i = 0; i < isDisabled.length; i++) {
        if (!isDisabled[i].checked) {
            isDisabled[i].setAttribute('disabled', 'disabled');
        }
    }

    const dataInfo = document.querySelector()
});
