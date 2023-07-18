import $ from 'jquery';

$('#user_role').change(function (e) {
    $('label[for=user_initPassword], #user_initPassword').show();
    if ($(this).find(":selected").text() === 'Administrateur') {
        $('label[for=user_initPassword], #user_initPassword').hide();
        return;
    }
    if ($(this).find(":selected").text() === 'Elu') {
        $('#actor-info').show();
        return;
    }
    $('#actor-info').hide();
    $('#user_party').val('');
    $('#user_title').val('');
});
