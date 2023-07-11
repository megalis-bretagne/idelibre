window.onload = () => {

    const isDeputyGroup = document.querySelector("#isDeputyGroup")
    const isDeputyInput = document.querySelector("#user_isDeputy");
    const mandatorTypeGroup = document.querySelector("#mandatorTypeGroup")
    const mandatorTypeInput = document.querySelector("#user_mandatorType")
    const mandatorNameGroup = document.querySelector("#mandatorGroup")
    const mandatorNameInput = document.querySelector("#user_mandator")

    listCleaner(mandatorNameInput);

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

    mandatorTypeInput.onchange = () => {
        const mandatorTypeValue = mandatorTypeInput.value;
        console.log(mandatorTypeValue)


    }

    function getList(value) {
        ajaxListGeneration(value)
    }

    function ajaxListGeneration(value) {
        let httpRequest = new XMLHttpRequest();
        httpRequest.onreadystatechange = alterContents;
        httpRequest.open('GET', `/user/${getUserId()}/list/${value}`);
        httpRequest.send();

        function alterContents() {
            if (httpRequest.readyState === XMLHttpRequest.DONE && httpRequest.status === 200) {
                if(httpRequest.responseText.trim() === "") {
                    console.log("aucun élu disponible")
                    // showErrorMessage();
                }
                mandatorNameInput.innerHTML += httpRequest.responseText
            } else {
                console.log('Il y a eu un problème avec la requête.');
            }
        }
    }

    function getUserId(){
        return "a9c4307a-bf77-4846-8888-ea5c8f2ccd17"
    }

    function listCleaner(value) {
        let options = value.getElementsByTagName('option');
        for (let i = options.length; i--;) {
            if (i !== 0){
                value.removeChild(options[i]);
            }
        }
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