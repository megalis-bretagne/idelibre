import $ from 'jquery';

$(document).ready(function () {
    let $url = $('#comelus_connector_url');
    let $apiKey = $('#comelus_connector_apiKey');
    let $status = $('#status');

    $url.on('focusout', check);
    $apiKey.on('focusout', check)



    function check(isOnlyCheck) {
        if (!($url.val() && $apiKey.val())) {
            return;
        }
        $.ajax({
            url: $status.data('url-check'),
            data: {
                url: $url.val(),
                apiKey: $apiKey.val()
            },
            success: function () {
                $status.html("Le connecteur est correctement configuré")
                    .removeClass('alert-danger')
                    .addClass('alert-success');

                if(isOnlyCheck !== true) {
                    getMailingLists();
                }
            },
            error: function () {
                $status.html("Le connecteur n'est pas correctement configuré")
                    .removeClass('alert-success')
                    .addClass('alert-danger');
                $('#comelus_connector_mailingListId').empty();
                $('#comelus_connector_mailingListId').append('<option value="">Connecteur non correctement configuré</option>');
            }

        })


    }


    function getMailingLists() {
        $.ajax({
            url: $('#comelus_connector_mailingListId').data('url-list'),
            data: {
                url: $url.val(),
                apiKey: $apiKey.val()
            },
            success: function (data) {
                let $selectMailingList = $('#comelus_connector_mailingListId');
                $selectMailingList.empty();
                $selectMailingList.append('<option value="">choississez une liste</option>');
                for(let i =0 ; i< data.length ; i++) {
                    $selectMailingList.append($("<option></option>")
                        .attr("value", data[i].id).text(data[i].name));
                }
            },
            error: function (err) {
                $selectMailingList.empty();
                $selectMailingList.append('<option value="">Connecteur non correctement configuré</option>');
            }
        })
    }
    check(true);
});
