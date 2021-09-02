import $ from 'jquery'


$('document').ready(function () {
    $(".select-batch").each(function () {
        this.checked = false;
    });


    $(".select-batch").click(function () {
        if (this.checked == true) {
            $(this).parent().parent().addClass('alert-warning font-weight-bolder');
        } else {
            $(this).parent().parent().removeClass('alert-warning font-weight-bolder');
        }
    });
});


let deleteList = [];

window.deleteAll = function() {
    let toDelete = []
    $(".select-batch").each(function () {
        if (this.checked) {
            toDelete.push({
                id: this.value,
                username: $(this).data('username'),
                fullName: $(this).data('firstname') + " " + $(this).data('lastname')
            });
        }
    });
    deleteList = toDelete;

    console.log(toDelete);

    if (!deleteList.length) {
        return;
    }
    generateUserList();
    generateInputList();

    $("#modalDelete").modal('show');
}


function generateUserList() {
    let html = '<table class="table table-striped">'
    html += '<tbody>'
    for (let i = 0; i < deleteList.length; i++) {
        html += '<tr class="d-flex">'

        html += '<td class="col-7">' +  deleteList[i].fullName + '</td>' ;
        html += '<td class="col-5">' + deleteList[i].username + '</td>';
        html += '</tr>';
    }
    html += '</tbody> </table>';
    $("#addListUser").html(html);
}

function generateInputList() {
    let html = "";
    for (let i = 0; i < deleteList.length; i++) {
        html += '<input type="hidden" name="users[]" value="' + deleteList[i].id + '">';
        html += "<br>";
    }
    html += "<br>";
    $("#user-list").html(html);
}
