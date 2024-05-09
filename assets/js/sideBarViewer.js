document.addEventListener('DOMContentLoaded', function () {

    const pdfContainer = document.getElementById('pdf-container');
    const selectedDocumentId = pdfContainer.getAttribute('data-selected-document-id');


    let elementDiv = document.querySelectorAll('.element-sidebar')

    setSelectedClass("document_" + selectedDocumentId);


    elementDiv.forEach(element => {
        element.addEventListener('click', function() {
         setSelectedClass(this.id);
        });
    });


    function setSelectedClass(toSelectElementId) {
        elementDiv.forEach(element => {

            if(element.id === toSelectElementId){
                element.classList.add('document-selected');

                return;
            }

            element.classList.remove('document-selected')
        });
    }


});