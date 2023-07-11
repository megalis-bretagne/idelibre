import $ from 'jquery';

$(document).ready(function () {
    let $url = $('#lsvote_connector_url');
    let $apiKey = $('#lsvote_connector_apiKey');
    let $status = $('#statusLsvote');

    $url.on('focusout', check);
    $apiKey.on('focusout', check);

    function check() {
        if (!($url.val() && $apiKey.val())) {
            $status.html("Le connecteur n'est pas correctement configuré")
                .removeClass('alert-info')
                .removeClass('alert-success')
                .addClass('alert-danger');
            return;
        }
        $url.val($url.val().replace(/\/+$/, ""));

        $.ajax({
            url: $status.data('url-check'),
            data: {
                url: $url.val(),
                apiKey: $apiKey.val()
            },
            success: function (data) {
                $status.html(`Le connecteur est correctement configuré`)
                    .removeClass('alert-danger')
                    .removeClass('alert-info')
                    .addClass('alert-success');
            },
            error: function () {
                $status.html("Le connecteur n'est pas correctement configuré")
                    .removeClass('alert-success')
                    .removeClass('alert-info')
                    .addClass('alert-danger');
            }
        })
    }

    check();
});
