window.getList = (url, value, input) => {
    listCleaner(input)
    ajaxListGeneration(url, value, input)
}

function ajaxListGeneration(url, value, input) {
    let httpRequest = new XMLHttpRequest();
    httpRequest.onreadystatechange = alterContents;
    // httpRequest.open('GET', `/user/${getUserId()}list/${value}`);
    httpRequest.open('GET', `${url}/${value}`);
    httpRequest.send();
    console.log("pas dans la requete ")

    function alterContents() {
        if (httpRequest.readyState === XMLHttpRequest.DONE && httpRequest.status === 200) {
            console.log("hi")
            input.innerHTML += httpRequest.responseText
            return false;
        } else {
            console.log('Il y a eu un problème avec la requête.');
        }
    }
}

