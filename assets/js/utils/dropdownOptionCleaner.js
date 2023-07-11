window.listCleaner = (value) => {
    let options = value.getElementsByTagName('option');
    for (let i = options.length; i--;) {
        if (i !== 0){
            value.removeChild(options[i]);
        }
    }
}
