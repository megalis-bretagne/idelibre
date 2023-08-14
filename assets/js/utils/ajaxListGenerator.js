window.getList = (url, value, input) => {
    listCleaner(input)
    ajaxListGeneration(url, value, input)
}

function ajaxListGeneration(url, value, input) {
    let httpRequest = new XMLHttpRequest();
    httpRequest.onreadystatechange = alterContents;
    httpRequest.open('GET', `${url}/${value}`);
    httpRequest.send();

    function alterContents() {
        console.log(httpRequest.readyState)
        if (httpRequest.readyState === XMLHttpRequest.DONE && httpRequest.status === 200) {
            input.innerHTML += httpRequest.responseText.trim()
            return;
        }
        console.log('Impossible de charger la liste.');
    }
}

