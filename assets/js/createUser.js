import $ from 'jquery';

$('#user_role').change(function (e) {
    $('label[for=user_initPassword], #user_initPassword').show();
    $('#actor-info').hide();
    $('#user_party').val('');
    $('#user_title').val('');
    $('#user_initPassword').val('0');
    $('#user_plainPassword_first').parent().parent().hide();
    $('#user_plainPassword_second').parent().parent().hide();

    if ($(this).find(":selected").text() === 'Administrateur') {
        $('label[for=user_initPassword], #user_initPassword').hide();
        $('#user_plainPassword_first').parent().parent().hide();
        $('#user_plainPassword_second').parent().parent().hide();
        return;
    }
    else if ($(this).find(":selected").text() === 'Elu') {
        $('#actor-info').show();
        return;
    }
});
