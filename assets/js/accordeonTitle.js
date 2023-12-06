const accordeon = document.querySelector('th.expandable');
accordeon.setAttribute('title', 'Déplier');

accordeon.onclick = function () {
    if (accordeon.classList.contains('expand-icon')) {
        this.setAttribute('title', 'Déplier')
    }
    else {
        this.setAttribute('title', 'Replier')
    }
}


