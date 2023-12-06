const accordeons = document.querySelectorAll('th.expandable');
accordeons.forEach((a, index) => {
    a.onclick = () => {
       if (a.classList.contains('collapse-icon')){
           a.setAttribute('title', 'Replier')
           return;
       }
       a.setAttribute('title', 'Deplier')
    }
})






