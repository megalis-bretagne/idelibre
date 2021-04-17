(function () {


    angular.module('idelibreApp').factory('workerSrv', function () {


        var worker={}; 

        var currentDocument;
        
        
        worker.setDocument = function(newDocument){
            currentDocument = newDocument;
        };
        
        worker.clearDocument = function(){
            if (currentDocument){
                currentDocument.cleanup();
                currentDocument.destroy();
                currentDocument = null;
                                                  
            }else{
                console.log('empty document');
            }
        };


        return worker;
    });


})();