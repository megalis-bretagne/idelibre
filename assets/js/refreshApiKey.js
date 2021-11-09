import $ from 'jquery';

window.refreshKey = function () {
    $.get('/apikey/refresh', function (res) {
        $('input[name="api_user[token]"]').first().val(res.apiKey);
    });
}
