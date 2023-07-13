console.log("List cleaner")

window.listCleaner = (value) => {
    let options = value.getElementsByTagName('option');
    for (let i = options.length; i;) {
        if (options[i]) {
            value.removeChild(options[i])
        }
    }
}


