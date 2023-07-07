window.getList = (value, input) => {
    // listCleaner(input)
    ajaxListGeneration(value, input)
}

function ajaxListGeneration(value, input) {
    let httpRequest = new XMLHttpRequest();
    httpRequest.onreadystatechange = alterContents;
    httpRequest.open('GET', `/user/${getUserId()}list/${value}`);
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


function getUserId() {
    const voterId = window.location.pathname.split('/')[3];

    return voterId ? voterId + '/' : '';
}
