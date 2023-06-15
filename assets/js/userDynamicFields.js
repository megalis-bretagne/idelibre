window.onload = () => {

    const isDeputyGroup = document.querySelector("#isDeputyGroup")
    const isDeputyInput = document.querySelector("#user_isDeputy");
    const mandatorTypeGroup = document.querySelector("#mandatorTypeGroup")
    const mandatorTypeInput = document.querySelector("#user_mandatorType")
    const mandatorNameGroup = document.querySelector("#mandatorGroup")
    const mandatorNameInput = document.querySelector("#user_mandator")

    isDeputyInput.onchange = () => {
        const isDeputyValue = isDeputyInput.value;

        if('0' === isDeputyValue) {
            console.log("N'est pas suppleant")
            show(mandatorTypeGroup)
            show(mandatorNameGroup)
            return 0;
        }
            console.log('Est suppleant')
            hide(mandatorTypeGroup)
            hide(mandatorNameGroup)
    }


    function hide(value) {
        value.classList.add('d-none')
        value.children[1].setAttribute('disabled', 'disabled')
        value.children[1].required = false
    }

    function show(value) {
        value.classList.remove('d-none')
        value.children[1].removeAttribute('disabled')
        value.children[1].required = true
    }





}