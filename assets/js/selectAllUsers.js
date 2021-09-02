import $ from 'jquery'

$('#selectAll').click(function () {
    let $options = $('#type_associatedActors option')
    for (let i = 0; i < $options.length; i++) {
        $options[i].selected = true;
    }
    $options.parent().trigger("change");
});
