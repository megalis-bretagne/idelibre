import $ from 'jquery'
import 'select2'


$('document').ready(function () {
    $('select').select2({
        'closeOnSelect': true,
        'allowClear': true,
         width: '100%',
        placeholder :'Selectionner ...',
        "language": {
            "noResults": function () {
                return "Pas de résultats trouvés";
            }
        }

    });


});

