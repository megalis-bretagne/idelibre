const hasRoleInput = document.querySelector("#user_role")
const hasRoleValue =  hasRoleInput.value
const roleActorId = "230a1c1d-eaec-4fb7-9ba7-d7ac47dc97bb";

const deputyNameGroup = document.querySelector("#deputyGroup")



window.onload = () => {
       if (roleActorId === hasRoleValue) {
        show(deputyNameGroup)
        return ;
    }
    hide(deputyNameGroup)
}

hasRoleInput.onchange = () => {
    let value = hasRoleInput.value

    if(value === roleActorId) {
        show(deputyNameGroup)
        return;
    }

    hide(deputyNameGroup);
}

