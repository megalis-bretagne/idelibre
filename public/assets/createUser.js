$('#user_role').change(function (e) {
    if ($(this).find(":selected").text() === 'Elu') {
        $('#actor-info').show();
        return;
    }
    $('#actor-info').hide();
    $('#user_party').val('');
    $('#user_title').val('');
});
