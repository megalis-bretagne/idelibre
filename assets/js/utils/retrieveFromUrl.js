window.getUserId = () => {
    const voterId = window.location.pathname.split('/')[3];

    return voterId ? voterId + '/' : '';
}

window.getToken = () => {
    const token = window.location.pathname.split('/')[3]
    return token ? token + '/' : '';
}
