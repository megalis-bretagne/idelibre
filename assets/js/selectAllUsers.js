import $ from 'jquery'

$('#selectAll').click(function () {
    let $options = $('#type_associatedActors option')
    for (let i = 0; i < $options.length; i++) {
        $options[i].selected = true;
    }
    $options.parent().trigger("change");
});

$('#selectAllEmployees').click(function () {
    let $options = $('#type_associatedEmployees option')
    for (let i = 0; i < $options.length; i++) {
        $options[i].selected = true;
    }
    $options.parent().trigger("change");
});

$('#selectAllGuests').click(function () {
    let $options = $('#type_associatedGuests option')
    for (let i = 0; i < $options.length; i++) {
        $options[i].selected = true;
    }
    $options.parent().trigger("change");
});