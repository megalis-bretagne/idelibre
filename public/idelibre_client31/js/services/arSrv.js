(function () {
    'use strict';
    /**
     * singleton de Login
     */
    angular.module('idelibreApp').factory('arSrv', function () {
        var ars = {};

        var arList = {};



        /**
         * 
         * @param {type} ar : {accountId, seanceId}
         */
        ars.add = function (ar) {
            if (!arList[ar.accountId]) {
                arList[ar.accountId] = []
            }
            arList[ar.accountId].push(ar.seanceId);
        }


        ars.getList = function () {
            return arList;
        }



        var serialize = function (obj) {
            return(JSON.stringify(obj));
        };

        ars.save = function () {
            localStorage.setItem('pendingARsStorage', serialize(arList));
        };

        ars.load = function () {
            var jsonArs = JSON.parse(localStorage.getItem('pendingARsStorage'));
            if (jsonArs)
                arList = jsonArs;
        };



        return ars;
    });


})();
