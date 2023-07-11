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

        if('0' === mandatorTypeValue) {
            mandatorNameGroup.classList.add('d-none')
        }

        show(mandatorNameGroup)

        if('1' === mandatorTypeValue) {
            console.log("Designe un suppléant : " + mandatorTypeValue)
            getList('deputies')
        }

        if('2' === mandatorTypeValue) {
            console.log("Donne procuration : " + mandatorTypeValue)
            getList('actors')
        }


    }

    function getList(value) {
        listCleaner(mandatorNameInput)
        ajaxListGeneration(value)
    }



    function ajaxListGeneration(value) {
        let httpRequest = new XMLHttpRequest();
        httpRequest.onreadystatechange = alterContents;
        httpRequest.open('GET', `/user/${getUserId()}list/${value}`);
        httpRequest.send();
        console.log("here")

        function alterContents() {
            if (httpRequest.readyState === XMLHttpRequest.DONE && httpRequest.status === 200) {
                console.log("here2")
                mandatorNameInput.innerHTML += httpRequest.responseText
                return false;
            } else {
                console.log('Il y a eu un problème avec la requête.');
            }
        }
    }

    function getUserId(){
        const voterId = window.location.pathname.split('/')[3];

        return voterId ? voterId + '/' : '';
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