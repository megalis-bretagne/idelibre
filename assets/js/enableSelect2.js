import $ from 'jquery'
import 'select2'


$(document).ready(function () {

    let condition = true;
    if( $('.form-select').attr('multiple') === 'multiple' ) {
        condition = false;
    }

    $('.form-select').select2({
        theme: "bootstrap-5",
        'closeOnSelect': condition,
        'allowClear': true,
         width: '100%',
        placeholder :'Sélectionner ...',
        "language": {
            "noResults": function () {
                return "Pas de résultats trouvés";
            }
        }
    });

    $('#type_reminder_duration').select2({
        'closeOnSelect': true,
        'allowClear': true,
        width: '100%',
        placeholder :'Sélectionner ...',
        "language": {
            "noResults": function () {
                return "Pas de résultats trouvés";
            }
        }
    });

});

