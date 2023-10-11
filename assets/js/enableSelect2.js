import $ from 'jquery'
import 'select2'


$(document).ready(function () {

    let condition = true;
    if( $('select').attr('multiple') === 'multiple' ) {
        condition = false;
    }

    $('select').select2({
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

