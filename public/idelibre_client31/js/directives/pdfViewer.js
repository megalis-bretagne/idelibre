/**
 * 
 * data est un account
 * @returns {undefined}
 */
(function () {
    'use strict';

    angular.module('idelibreApp').directive('pdfViewer', function ($rootScope, $http) {


        /**
         * @namespace accountBtn
         */
        return {
            templateUrl: 'js/directives/pdfViewer.html',
            restrict: 'E',
            replace: false,
            scope: {
                seanceid: '=',
                accountid: '=',
                document: '=', //projet, annexe, convocation
                documentid: '=',
                typeSender: '=',
                senderid: "=",
                typedocument: "=",
                action: '&'
            },
            /**
             * 
             * @param {type} $scope
             * @function accountBtn.controller
             */
            controller: function ($scope, $window, fakeUrlSrv, accountSrv, localDbSrv, $log, $anchorScroll, $location) {






                $scope.$on('toggleRightDrawer', function (e, data) {
                    $scope.isDrawer = !$scope.isDrawer;

                });


                $scope.$on('toggleLeftDrawer', function (e, data) {
                    $scope.isLeftDrawer = !$scope.isLeftDrawer;
                });


                $scope.isDrawer = false;
                $scope.isLeftDrawer = false;
                fakeUrlSrv.removeUrls();
                $scope.pageNum = 0;
                $scope.pageTotal = 0;

                var seanceId = $scope.seanceid;
                var accountId = $scope.accountid;

                var account = accountSrv.findAccountById(accountId);


                var seance = account.findSeance(seanceId);
                $scope.seance = seance;
                $scope.account = account;

                $scope.enableAnnotations = account.type == ACTEURS

                if($scope.enableAnnotations) {
                    if ($scope.typedocument != "archivedProjet" && $scope.typedocument != "archivedAnnexe") {
                        $scope.users = seance.getSharedUsers(account.userId);
                    }
                }


////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//Gestion du pdf


                var getPdf = function () {
                    var documentId = $scope.documentid;

                    //closure pour l'appel
                    //(function (pdfdata, length) {
                    localDbSrv.getData(documentId).then(function (result) {
                        var url = URL.createObjectURL(result);
                        fakeUrlSrv.addUrl(url);
                        printPdf(url);
                    }).catch(function (err) {
                        $log.error(err);
                    });
                };


                var getPdfFromServer = function () {
                    var documentId = $scope.documentid;
                    var url;
                    if ($scope.typedocument == "archivedProjet") {
                        url = account.url + '/nodejs/' + config.API_LEVEL + '/projets/dlPdf/' + documentId;
                    }
                    if ($scope.typedocument == "archivedAnnexe") {
                        url = account.url + "/nodejs/" + config.API_LEVEL + "/annexes/dlAnnexe/" + documentId;
                    }

                    $http({method: 'GET', url: url, responseType: "blob"
                        , headers: {
                            'token': account.token}
                    })
                            .success(function (data, status, headers, config) {
                                //creation de la fake url qui pointe su lr blob
                                var url = URL.createObjectURL(data);
                                //demande de rendu du pdf
                                printPdf(url);
                            })
                            .error(function (data, status, headers, config) {
                                $log.error('error');
                            });
                };

                if ($scope.typedocument == "archivedProjet" || $scope.typedocument == "archivedAnnexe") {
                    getPdfFromServer();
                } else {
                    getPdf();
                }


                $scope.showDrawers = function () {
                    return !($scope.typedocument == "archivedProjet" || $scope.typedocument == "archivedAnnexe")
                }




                function printPdf(url) {
                    var pageRendering = false;
                    var pageNumPending = null;

                    var myDocument = PDFJS.getDocument(url).then(function (pdf) {



                        var maxPage = pdf.pdfInfo.numPages;
                        $scope.pageTotal = maxPage;
                        var currentPage = 1;
                        var canvas = document.getElementById('the-canvas');
                        var renderPage = function (page) {
                            if (page > maxPage || page < 1)
                                return;

                            pageRendering = true;

                            currentPage = page;
                            $scope.pageNum = page;
                            if (!$rootScope.$$phase) {
                                $scope.$apply();
                            }

                            pdf.getPage(page).then(function (page) {
                                var scale = window.innerWidth / page.pageInfo.view[2];   //pdf width
                                //var scale = 1;
                                var viewport = page.getViewport(scale); //(size(page.pageInfo.view[2] width  [3]height

                                console.log("page");
                                console.log(page);

                                // Prepare canvas using PDF page dimensions.
                                //   var canvas = document.getElementById('the-canvas');
                                var context = canvas.getContext('2d');
                                canvas.height = viewport.height;
                                canvas.width = viewport.width;
                                // Render PDF page into canvas context.
                                var renderContext = {
                                    canvasContext: context,
                                    viewport: viewport
                                };

                                page.render(renderContext).then(function () {
                                    pageRendering = false;
                                    if (pageNumPending !== null) {
                                        // New page rendering is pending
                                        renderPage(pageNumPending);
                                        pageNumPending = null;
                                    }
                                    $rootScope.$broadcast("pageRendered", {height: canvas.height, width: canvas.width, page: currentPage, scale: scale});
                                    $window.scrollTo(0, 0);
                                });
                            });
                        }

                        renderPage(1);
                        $scope.gotoNextPage = function () {
                            console.log("okOKOKOKOKOKOKOKOKOKOK");
                            queueRenderPage(currentPage + 1);
                        }
                        
                        
                        $scope.gotoPreviousPage = function () {
                            console.log("okOKOKOKOKOKOKOKOKOKOK");
                            queueRenderPage(currentPage - 1);
                        }

                        $scope.gotoPage = function () {
                            queueRenderPage($scope.pageNum);
                        }


                        function queueRenderPage(num) {
                            if (pageRendering) {
                                pageNumPending = num;
                            } else {
                                renderPage(num);
                            }
                        }


                        $scope.$on('goToPage', function (e, data) {
                            queueRenderPage(data.page);
                        });
                        $scope.$on('swipePage', function (e, data) {
                            queueRenderPage(currentPage + data);
                        });
                    });
                }

                $('ul.dropdown-menu').on('click', function (event) {
                    event.stopPropagation();
                });

            }

        };

    });

})();