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

$(document).ready(function () {
    let $labelActors = $('#type_associatedActorsLabel');
    let $labelEmployees = $('#type_associatedEmployeesLabel');
    let $labelGuests = $('#type_associatedGuestsLabel');
    let nbSelectedActors = $('#type_associatedActors option:selected').length;
    let nbSelectedEmployees = $('#type_associatedEmployees option:selected').length;
    let nbSelectedGuests = $('#type_associatedGuests option:selected').length;

    $labelActors.html(nbSelectedActors);
    $labelEmployees.html(nbSelectedEmployees);
    $labelGuests.html(nbSelectedGuests);
    $('#type_associatedActors').change(function (){
        nbSelectedActors = $('#type_associatedActors option:selected').length;
        $labelActors.html(nbSelectedActors);
    });
    $('#type_associatedEmployees').change(function (){
        nbSelectedEmployees = $('#type_associatedEmployees option:selected').length;
        $labelEmployees.html(nbSelectedEmployees);
    });
    $('#type_associatedGuests').change(function (){
        nbSelectedGuests = $('#type_associatedGuests option:selected').length;
        $labelGuests.html(nbSelectedGuests);
    });
});