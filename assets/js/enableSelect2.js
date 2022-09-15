import $ from 'jquery'
import 'select2'


$('document').ready(function () {
    $('select').select2({
        'closeOnSelect': false,
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

