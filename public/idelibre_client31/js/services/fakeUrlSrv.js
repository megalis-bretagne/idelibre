(function () {
    'use strict';

    /**
     * Les fakeurls sont des url créée à partir des blob qu'il faut supprimer pour ne pas garder inutillement les blob en memoire
     */
    angular.module('idelibreApp').factory('fakeUrlSrv', function () {
        
        var fakeUrl = {};
        var urlList =[];
        
        fakeUrl.addUrl = function(url){
            urlList.push(url);
        };
        
        fakeUrl.removeUrls = function(){
            _.each(urlList, function(url){
               var res = window.URL.revokeObjectURL(url); 
            });
            urlList =[];

        }
       
        
        return fakeUrl;

    });
})();