import './pdf.worker.mjs';
import * as pdfjsLib from "pdfjs-dist";
import * as pdfjsViewer from "pdfjs-dist/legacy/web/pdf_viewer.mjs";


pdfjsLib.GlobalWorkerOptions.workerSrc = 'pdfjs-dist/pdf.worker.min.mjs';

document.addEventListener('DOMContentLoaded', function() {
    const pdfContainer = document.getElementById('pdf-container');
    const url = pdfContainer.getAttribute('data-url');


    const eventBus = new pdfjsViewer.EventBus();
    const pdfViewer = new pdfjsViewer.PDFViewer({container: pdfContainer, eventBus});

    console.log(url);

    const loadingTask = pdfjsLib.getDocument({
        // url: "https://s29.q4cdn.com/175625835/files/doc_downloads/test.pdf",
        url: url,
    });

    loadingTask.promise.then(function(pdfDocument) {
        pdfViewer.setDocument(pdfDocument);
    }).catch(function(reason) {
        console.error(`Error: ` + reason);
    });


    eventBus.on("pagesinit", function () {
        pdfViewer.currentScaleValue = "page-width";
    })

    window.addEventListener('resize', () => {
        console.log(pdfViewer);
        pdfViewer.currentScaleValue = "page-width";
    });

    window.changePdf = url => {
        console.log(url);

        const loadingTask = pdfjsLib.getDocument({
            url: url,
        });

        loadingTask.promise.then(function(pdfDocument) {
            pdfViewer.setDocument(pdfDocument);
        }).catch(function(reason) {
            console.error(`Error: ` + reason);
        });
    }

});