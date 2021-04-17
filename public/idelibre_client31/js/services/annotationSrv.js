(function () {
    'use strict';
    /**
     * singleton de Login
     */
    angular.module('idelibreApp').factory('annotationSrv', function () {
        var annotation = {};

        var readList = {};

        var pendingList = {};

        var deleteList = {};


        annotation.addToReadList = function (accountId, annotationId) {
            if (!readList[accountId])
                readList[accountId] = [];
            readList[accountId].push(annotationId);
        };


        annotation.getReadList = function () {
            return readList;
        };


        annotation.getReadListByAccountId = function (accountId) {
            return readList[accountId] || [];
        };


        annotation.getPendingListByAccountId = function (accountId) {
            return pendingList[accountId] || [];
        };

        annotation.getPendingList = function () {
            return pendingList;
        };


        annotation.clearPendingListByAccountId = function (accountId) {
            pendingList[accountId] = [];
        };


        annotation.clearReadListByAccountId = function (accountId) {
            readList[accountId] = [];
        }


        annotation.getDeleteList = function () {
            return deleteList || [];
        }


        annotation.getDeleteListByAccountId = function (accountId) {
            return deleteList[accountId] || [];
        };


        annotation.clearDeleteListByAccountId = function (accountId) {
            deleteList[accountId] = [];
        }


        annotation.addToDeleteList = function (accountId, annotationId) {
            if (!deleteList[accountId])
                deleteList[accountId] = [];

            //check if is present into pendingList
            var index = _.findIndex(pendingList[accountId], function (annot) {
                return annot.id === annotationId;
            });
            if (index == -1) {
                deleteList[accountId].push(annotationId);
            } else {

                //if present remove it from prendingList
                pendingList[accountId].splice(index, 1);
            }
        };


        annotation.getServerFormatedPendingList = function (accountId) {
            var list = pendingList[accountId] || [];
            var formatedList = [];
            for (var i = 0, ln = list.length; i < ln; i++) {
                formatedList.push(AnnotationUtils.getServerFormated(list[i]));
            }
            return formatedList;
        }


        annotation.addToPendingList = function (accountId, annotation) {
            if (!pendingList[accountId])
                pendingList[accountId] = [];
            var index = _.findIndex(pendingList[accountId], function (annot) {
                return annot.id == annotation.id;
            });
            if (index != -1) {
                //annotation already exist replace it
                pendingList[accountId][index] = annotation;
            } else {
                pendingList[accountId].push(annotation);
            }
        }



        var serialize = function (obj) {
            return(JSON.stringify(obj));
        };

        annotation.save = function () {
            localStorage.setItem('pendingAnnotationStorage', serialize({
                readList: readList,
                pendingList: pendingList,
                deleteList: deleteList
            }));
        };

        annotation.load = function () {
            var jsonPendingAnnotations = JSON.parse(localStorage.getItem('pendingAnnotationStorage'));
            if (jsonPendingAnnotations) {
                readList = jsonPendingAnnotations.readList;
                pendingList = jsonPendingAnnotations.pendingList;
                deleteList = jsonPendingAnnotations.deleteList;
            }
        };

        return annotation;


    });


})();