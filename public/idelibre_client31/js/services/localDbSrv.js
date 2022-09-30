(function () {
    'use strict';

    angular.module('idelibreApp').factory('localDbSrv', function (loginSrv, $rootScope, $http, $log, accountSrv) {

        //TODO metttre dans la conf en global
        var CONVOCATION = 1;
        var PROJET = 2;
        var ANNEXE = 3;
        var INVITATION = 4;
        var OTHERDOC = 5;

        var pouch = {};
        var pouchdb;


        function init() {
            console.log('ini');
            if (/*config.cordova*/ false) {
                pouchdb = new PouchDB('idelibreDB'/*, {adapter: 'websql'}*/);
            } else {

                var ua = navigator.userAgent.toLowerCase();

                if (ua.indexOf('safari') != -1) {
                    if (ua.indexOf('chrome') > -1) {
                        //alert("chrome") // Chrome
                        pouchdb = new PouchDB('idelibreDB');
                    } else {
                        // Safari (mobile et desktop);
                        if (ua.indexOf('mobile') > -1) {
                            //alert('safari mobile');
                            //safari mobile (safari mobile max 50MO !);
                            pouchdb = new PouchDB('idelibreDB', {adapter: 'websql', size: 50});
                        } else {
                            //alert('safari');
                            //safari
                            pouchdb = new PouchDB('idelibreDB', {/*adapter: 'websql',*/ size: 500});
                        }
                    }
                } else {
                    //ni safari ni chrome
                    //    alert('autre');
                    pouchdb = new PouchDB('idelibreDB');
                }
            }
        }

        init();



        pouch.purge = function () {
            pouchdb.destroy().then(function () {
                init();
                pouch.getAllConvocation();
                pouch.checkAllProjetsDocument();
                pouch.checkAllOtherdocsDocument();
            }).catch(function (err) {
                console.log(err);
            })
        };


        /**
         * initialisation de pouchdb pour ios (entre une quelconque premiere donnée car ne gere pas le 0)
         * @returns {}
         */
        pouch.init = function () {
            $log.info('pouch.init');
            pouchdb.put({
                _id: '00',
                title: '1'
            }).then(function (response) {
                //    alert('response');
            }).catch(function (err) {
                console.log(err);
                if(err.status === 500) {
                    alert("Votre navigateur n'accepte pas qu'idelibre télécharge les documents dans sa mémoire. " +
                        "Peut-être êtes-vous en mode de navigation privée ?");
                }
            });
        };



        // enregistre une donnée 
        pouch.save = function (id, data, callback) {
            $log.debug('begin save');
            pouchdb.put({
                _id: id,
                data: data
            }, function (err, response) {
                callback(err, response);
            });
        };

        /**
         *
         * @param {string} id
         * @param {function(doc)} callback
         * @returns {undefined}
         */
        pouch.find = function (id, callback) {
            pouchdb.get(id, function (err, doc) {
                callback(doc);
            });
        };


        /**
         * Supprime un élement dans la base de donnée locale en fonction de son id
         * @param {String} docId
         * @returns {}
         */
        var removeById = function (docId) {
            pouchdb.get(docId, function (err, doc) {
                if (err) {
                    $log.error(' image inexistante ' + err);
                } else {
                    pouchdb.remove(doc, function (err, response) {
                        if (err) {
                            $log.error("Erreur lors de la suppression de l'image");
                        } else {
                            $log.debug("Image supprimé");
                        }
                    });
                }
            });
        };


        /**
         * supression des données de la seance dans la bdd locale
         * @param {Seance} seance
         * @returns {undefined}
         */
        pouch.removeSeanceLocalDb = function (seanceId, account) {
            var seance = account.findSeance(seanceId);
            if(!seance){
                return;
            }

            //suppresion de la convocation
            removeById(seance.getConvocationDocumentId());

            //supression des documents des projet TODO: faire avec un db.bulkDocs pour plus de performence (un seul acces db)
            _.each(seance.getProjetDocumentsId(), function (documentId) {
                removeById(documentId);
            });

            //supression des documents des autres documents TODO: faire avec un db.bulkDocs pour plus de performence (un seul acces db)
            _.each(seance.getOtherdocDocumentsId(), function (documentId) {
                removeById(documentId);
            });
        };


        var projetQueue = [];
        var isRunningProjetQueue = false;
        var otherdocQueue = [];
        var isRunningOtherdocQueue = false;


        pouch.getProjet = function (document, collectivite, seance, accountId) {
            //seulement si ce morceau n'a pas encore été chargé
            if (!(document.isLoaded == LOADED)) {
                addQueue(collectivite, seance, accountId, document, PROJET);
            }
        }


        pouch.getOtherdoc = function (document, collectivite, seance, accountId) {
            //seulement si ce morceau n'a pas encore été chargé
            if (!(document.isLoaded == LOADED)) {
                addQueue(collectivite, seance, accountId, document, OTHERDOC);
            }
        }


        pouch.addAnnexeToDownload = function (collectivite, seance, accountId, annexe) {
            addQueue(collectivite, seance, accountId, annexe, ANNEXE);
        }


        var addQueue = function (collectivite, seance, accountId, document, type) {
            var alreadyInQueue;

            if (type != ANNEXE) {
                alreadyInQueue = _.find(projetQueue, function (el) {
                    return el.document.id == document.id;
                });
            } else if (type == OTHERDOC) {
                alreadyInQueue = _.find(otherdocQueue, function (el) {
                    return el.document.id == document.id;
                });
            } else {
                alreadyInQueue = _.find(projetQueue, function (el) {
                    return el.document.annexe_id == document.annexe_id;
                });
            }


            if (!alreadyInQueue) {
                projetQueue.push({collectivite: collectivite, seance: seance, accountId: accountId, document: document, type: type});
                execProjetQueue();

                otherdocQueue.push({collectivite: collectivite, seance: seance, accountId: accountId, document: document, type: type});
                execOtherdocQueue();
            } else {
                execProjetQueue();
                //alert ('already in ouh');
                execOtherdocQueue();
            }
        };



        var execProjetQueue = function () {
            //si la queue n'est pas vide et pas deja en train d'etre dépilée
            if (!_.isEmpty(projetQueue) && !isRunningProjetQueue) {
                isRunningProjetQueue = true;
                //recupération du premier el de la queue
                var part = projetQueue.splice(0, 1)[0];

                if (!(part.document.isLoaded == LOADED)) {
                    getAndSaveDoc(part.collectivite, part.seance, part.accountId, part.document, part.type, execProjetQueue);
                } else {
                    isRunningProjetQueue = false;
                    execProjetQueue();
                }
            }
        }



        var execOtherdocQueue = function () {
            //si la queue n'est pas vide et pas deja en train d'etre dépilée
            if (!_.isEmpty(otherdocQueue) && !isRunningOtherdocQueue) {
                isRunningOtherdocQueue = true;
                //recupération du premier el de la queue
                var part = otherdocQueue.splice(0, 1)[0];

                if (!(part.document.isLoaded == LOADED)) {
                    getAndSaveDoc(part.collectivite, part.seance, part.accountId, part.document, part.type, execOtherdocQueue());
                } else {
                    isRunningOtherdocQueue = false;
                    execOtherdocQueue();
                }
            }
        }

// USELESS ??
  /*      var execQueue = function () {
            //si la queue n'est pas vide et pas deja en train d'etre dépilée
            if (!_.isEmpty(partQueue) && !isRunningQueue) {
                isRunningQueue = true;
                //recupération du premier el de la queue
                var part = partQueue.splice(0, 1)[0];


                if (!part.pdfPart.isLoaded) {
                    getAndSaveParts(part.pdfPart, part.collectivite, part.seance, part.accountId, part.document, part.type, execQueue);
                } else {
                    isRunningQueue = false;
                    execQueue();
                }
            }
        }
*/

        var saveInDataBase = function (data, typeDocument, pdfdata, accountId, seance, document, callback) {
            $rootScope.$broadcast('download');
            var documentId
            //create an interface instead
            if (typeDocument == ANNEXE) {
                documentId = document.annexe_id;
            } else {
                documentId = document.id;
            }


            pouchdb.put({
                _id: documentId,
                documentId: documentId,
                part: 1,
                _attachments: {
                    'mypdf': {
                        type: 'application/pdf',
                        data: data
                    }
                }
            }, {conflicts: false}).then(function (response) {
                $rootScope.$broadcast('download');
                // emission des loaded
                if (typeDocument === CONVOCATION) {
                    // blob = null;
                    document.isLoaded = 2; //LOADED
                    $rootScope.$broadcast('update loaded convocation', {
                        documentId: pdfdata.document_id,
                        accountId: accountId,
                        seanceId: seance.id
                    });
                    isRunningQueueConvocation = false;
                    callback();

                }

                if (typeDocument === PROJET) {
                    // blob = null;
                    document.isLoaded = 2; //LOADED
                    $rootScope.$broadcast('update loaded projet', {
                        documentId: document.id,
                        accountId: accountId,
                        seanceId: seance.id
                    });
                    isRunningProjetQueue = false;
                    callback();
                }

                if (typeDocument === ANNEXE) {
                    $rootScope.$broadcast('update loaded annexe', {
                        documentId: document.annexe_id,
                        accountId: accountId,
                        seanceId: seance.id
                    });
                    isRunningProjetQueue = false;
                    callback();
                }

                if (typeDocument === OTHERDOC) {
                    // blob = null;
                    document.isLoaded = 2; //LOADED
                    $rootScope.$broadcast('update loaded other document', {
                        documentId: document.id,
                        accountId: accountId,
                        seanceId: seance.id
                    });
                    isRunningOtherdocQueue = false;
                    callback();
                }


            }).catch(function (err) {

                if (typeDocument == PROJET) {
                    errorSaveProjet(err, document, accountId, seance);
                }

                if (typeDocument == ANNEXE) {
                    errorSaveAnnexe(err, document, accountId, seance);
                }

                if (typeDocument == OTHERDOC) {
                    errorSaveAnnexe(err, document, accountId, seance);
                }


                isRunningQueueConvocation = false;
                isRunningProjetQueue = false;
                isRunningOtherdocQueue = false;
                callback();
            });
        };


        var errorSaveAnnexe = function (err, document, accountId, seance) {
            if (err.status === 409) {
                document.loaded = LOADED; //LOADED
                $rootScope.$broadcast('update loaded annexe', {
                    documentId: document.annexe_id,
                    accountId: accountId,
                    seanceId: seance.id
                });
            } else {
                $log.error(err);
                $rootScope.$broadcast('error loaded annexe', {
                    documentId: document.annexe_id,
                    accountId: accountId,
                    seanceId: seance.id
                });
            }
        }



        var errorSaveProjet = function (err, document, accountId, seance) {

            if (err.status === 409) {
                document.isLoaded = LOADED; //LOADED
                $rootScope.$broadcast('update loaded projet', {
                    documentId: document.id,
                    accountId: accountId,
                    seanceId: seance.id
                });
            } else {
                $log.error(err);
                $rootScope.$broadcast('error loaded projet', {
                    documentId: document.id,
                    accountId: accountId,
                    seanceId: seance.id
                });
            }
        }



        var errorSaveOtherdoc = function (err, document, accountId, seance) {

            if (err.status === 409) {
                document.isLoaded = LOADED; //LOADED
                $rootScope.$broadcast('update loaded other document', {
                    documentId: document.id,
                    accountId: accountId,
                    seanceId: seance.id
                });
            } else {
                $log.error(err);
                $rootScope.$broadcast('error loaded other document', {
                    documentId: document.id,
                    accountId: accountId,
                    seanceId: seance.id
                });
            }
        }

        /** X
         * Télécharge et sauvegarde une annexe en pdf
         * @param {uuid} annexeId
         * @param {uuid} collectiviteId
         * @param {function} callbackSucess
         * @param {function} callbackError
         * @returns {}
         */
        pouch.getAnnexePdf = function (account, annexeId, collectiviteId, callbackSucess, callbackError) {
            var url = account.url + "/nodejs/" + config.API_LEVEL + "/annexes/dlAnnexe/" + annexeId;
            //$http.get(url, {timeout: TIMEOUT}).
            $http({method: 'GET', url: url, responseType: "blob"
                , headers: {
                    'token': account.token}
            }).
            success(function (data, status, headers, config) {
                var blob = data;
                pouchdb.put({
                    _id: annexeId,
                    _attachments: {
                        'mypdf': {
                            content_type: 'application/pdf',
                            data: blob
                        }
                    }

                }, {conflicts: false}).then(function (response) {
                    callbackSucess();
                }).catch(function (err) {
                    callbackSucess();
                });
            }).
            error(function (data, status, headers, config) {
                $log.error('error');
                callbackError();
            });
        };


        /** X
         * parcourt toute les seances de tous les accounts pour ene xtraire les id des documents des convoc et seance/ account associés
         * @returns {undefined}
         */
        pouch.getAllConvocation = function () {
            //récuperation des ids des documents, des accounts, des seances
            var accounts = accountSrv.getList();
            for (var iA = 0, lnA = accounts.length; iA < lnA; iA++) {
                if (accounts[iA].seances && accounts[iA].type == ACTEURS) {
                    for (var iS = 0, lnS = accounts[iA].seances.length; iS < lnS; iS++) {
                        pouch.lookForConvocationDocument(accounts[iA].seances[iS].convocation.document_text, accounts[iA], accounts[iA].seances[iS]);
                    }
                }else if(accounts[iA].seances && accounts[iA].type != ACTEURS ){

                    for (var iS = 0, lnS = accounts[iA].seances.length; iS < lnS; iS++) {
                        if(accounts[iA].seances[iS].invitation)
                            pouch.lookForConvocationDocument(accounts[iA].seances[iS].invitation.document_text, accounts[iA], accounts[iA].seances[iS]);
                    }
                }
            }
        };


        pouch.getAllConvocationByAccount = function (account) {
            //récuperation des ids des documents, des accounts, des seances
            if (account.seances) {
                for (var iS = 0, lnS = account.seances.length; iS < lnS; iS++) {
                    pouch.lookForConvocationDocument(account.seances[iS].convocation.document_text, account, account.seances[iS]);
                }
            }
        };




        pouch.getAllInvitationsByAccount = function (account) {
            //récuperation des ids des documents, des accounts, des seances
            if (account.seances) {
                for (var iS = 0, lnS = account.seances.length; iS < lnS; iS++) {
                    pouch.lookForConvocationDocument(account.seances[iS].invitation.document_text, account, account.seances[iS]);
                }
            }
        };








        /**X
         * on boucle sut tous les pdf datas de la convocation
         */
        pouch.lookForConvocationDocument = function (document, account, seance) {
            lookForConvocationPdf(document, account, seance);
        }


        var lookForConvocationPdf = function (document, account, seance) {
            pouch.find(document.id, function (doc) {
                if (doc) {
                    document.isLoaded = 2; //LOADED
                    $rootScope.$broadcast('update loaded convocation', {
                        documentId: document.id,
                        accountId: account.id,
                        seanceId: seance.id
                    });

                } else {
                    document.isLoaded = NOTLOADED; //NOTLOADED
                    $rootScope.$broadcast('update loaded convocation', {
                        documentId: document.id,
                        accountId: account.id,
                        seanceId: seance.id
                    });
                    //getConvocationPart(pdfdatas, document, account.name, seance, account.id);
                    addQueueConvocation(document, account.name, seance, account.id);

                }
            });

        };




        var queueConvocation = [];
        var isRunningQueueConvocation = false;




        var addQueueConvocation = function (document, accountName, seance, accountId) {
            queueConvocation.push({accoutName: accountName, seance: seance, accountId: accountId, document: document});
            execQueueConvocation();

        };


        var execQueueConvocation = function () {

            if (!_.isEmpty(queueConvocation) && !isRunningQueueConvocation) {
                isRunningQueueConvocation = true;
                //recupération du premier el de la queue
                var part = queueConvocation.splice(0, 1)[0];

                // if(part.documnent.type == ADMINISTRATIFS){
                //     getAndSaveDoc(part.accoutName, part.seance, part.accountId, part.document, INVITATION, execQueueConvocation);
                // }else{
                getAndSaveDoc(part.accoutName, part.seance, part.accountId, part.document, CONVOCATION, execQueueConvocation);
                // }

            }
        };


        var getAndSaveDoc = function (collectivite, seance, accountId, document, typeDocument, callback, documentsToDl) {
            $rootScope.$broadcast('upload');
            var account = accountSrv.findAccountById(accountId);
            if (!account) {
                return;
            }


            if (typeDocument != ANNEXE && typeDocument != OTHERDOC) {
                var url = account.url + '/nodejs/' + config.API_LEVEL + '/projets/dlPdf/' + document.id;
            } else if (typeDocument == OTHERDOC) {
                var url = account.url + '/nodejs/' + config.API_LEVEL + '/otherdocs/dlPdf/' + document.id;
            } else {
                var url = account.url + "/nodejs/" + config.API_LEVEL + "/annexes/dlAnnexe/" + document.annexe_id;
            }


            if(typeDocument == CONVOCATION && account.type != ACTEURS){
                var url = account.url + "/nodejs/" + config.API_LEVEL + "/projets/dlPdf/" + document.id;
            }


            var timeout;
            //si le type de document est une convocation alors le timeout est doublé
            if (typeDocument == CONVOCATION) {
                timeout = 2 * TIMEOUT;
            } else {
                timeout = TIMEOUT;
                $rootScope.$broadcast('make spin icon', {documentId: document.id});
            }

            $http({method: 'GET', url: url, responseType: "blob"
                , headers: {
                    'token': account.token}
            }).
            // $http.get(url, {timeout: timeout}).
            success(function (data, status, headers, config) {
                saveInDataBase(data, typeDocument, null, accountId, seance, document, callback);
            }).
            error(function (data, status, headers, config) {
                $log.error('error');
                if (typeDocument != ANNEXE && typeDocument != OTHERDOC) {
                    $rootScope.$broadcast('error loaded projet', {
                        documentId: document.id,
                        accountId: accountId,
                        seanceId: seance.id
                    });
                } else if (typeDocument != ANNEXE && typeDocument == OTHERDOC) {
                    $rootScope.$broadcast('error loaded other document', {
                        documentId: document.id,
                        accountId: accountId,
                        seanceId: seance.id
                    });
                }
                else {
                    $rootScope.$broadcast('error loaded annexe', {
                        documentId: document.annexe_id,
                        accountId: accountId,
                        seanceId: seance.id
                    });
                }

                console.log("error");
                isRunningProjetQueue = false;
                isRunningQueueConvocation = false;
                isRunningOtherdocQueue = false;
                callback();
            });
        };


        /** X
         * test si un projet est déja chargé
         * @param {type} documentId
         * @param {type} projet
         * @param {type} seanceId
         * @param {type} accountId
         * @returns {undefined}
         */
        pouch.checkProjetDocument = function (document, projet, seance, account) {
            lookForProjetPdf(document, projet, account, seance);
            if(projet && projet.annexes) {
                for (var iP = 0, lnP = projet.annexes.length; iP < lnP; iP++) {
                    lookForAnnexePdf(projet.annexes[iP], projet, seance, account);
                }
            }
        };


        /** X
         * test si un autre document est déja chargé
         * @param {type} documentId
         * @param {type} otherdoc
         * @param {type} seanceId
         * @param {type} accountId
         * @returns {undefined}
         */
        pouch.checkOtherdocDocument = function (document, otherdoc, seance, account) {
            lookForOtherdocPdf(document, otherdoc, account, seance);
        };


        var lookForAnnexePdf = function (annexe, projet, account, seance) {

            pouch.find(annexe.annexe_id, function (doc) {
                if (doc) {

                    annexe.loaded = LOADED; //LOADED
                    $rootScope.$broadcast('update loaded annexe', {
                        annexetId: annexe.annexe_id,
                        accountId: account.id,
                        seanceId: seance.id,
                        projetId: projet.id
                    });
                }else{
                    annexe.loaded = NOTLOADED; //LOADED
                    $rootScope.$broadcast('update loaded annexe', {
                        annexetId: annexe.annexe_id,
                        accountId: account.id,
                        seanceId: seance.id,
                        projetId: projet.id
                    });
                }

            });
        };



        /**
         *  recherche des morceau de prdf de projet
         * @param {type} pdfdatas
         * @param {type} document
         * @param {type} projet
         * @param {type} account
         * @param {type} seance
         * @returns {undefined}
         */
        var lookForProjetPdf = function (document, projet, account, seance) {

            pouch.find(document.id, function (doc) {
                if (doc) {
                    document.isLoaded = LOADED; //LOADED
                    $rootScope.$broadcast('update loaded projet', {
                        documentId: document.id,
                        accountId: account.id,
                        seanceId: seance.id,
                        projetId: projet.id
                    });

                }else{
                    document.isLoaded = NOTLOADED; //LOADED
                    $rootScope.$broadcast('update loaded projet', {
                        documentId: document.id,
                        accountId: account.id,
                        seanceId: seance.id,
                        projetId: projet.id
                    });
                }
            });
        };


        /**
         *  recherche des morceau de pdf de autre document
         * @param {type} pdfdatas
         * @param {type} document
         * @param {type} otherdoc
         * @param {type} account
         * @param {type} seance
         * @returns {undefined}
         */
        var lookForOtherdocPdf = function (document, otherdoc, account, seance) {

            pouch.find(document.id, function (doc) {
                if (doc) {
                    document.isLoaded = LOADED; //LOADED
                    $rootScope.$broadcast('update loaded other document', {
                        documentId: document.id,
                        accountId: account.id,
                        seanceId: seance.id,
                        otherdocId: otherdoc.id
                    });

                }else{
                    document.isLoaded = NOTLOADED; //LOADED
                    $rootScope.$broadcast('update loaded other document', {
                        documentId: document.id,
                        accountId: account.id,
                        seanceId: seance.id,
                        otherdocId: otherdoc.id
                    });
                }
            });
        };


        /**
         * verification si l'annexe est deja chargée
         * @param {type} annexeId
         * @param {type} annexe
         * @returns {undefined}
         */
        pouch.checkAnnexePdf = function (annexeId, callback) {
            pouch.find(annexeId, function (doc) {
                if (doc) {
                    callback();
                }
            });
        };



        /** X
         *  //TODO checkAllProjetsDocumentByAccount
         *
         * parcourt toute les seances de tous les accounts pour en extraire les id des documents des projets et seance/ account associés
         * @returns {undefined}
         */
        pouch.checkAllProjetsDocument = function () {
            //récuperation des ids des documents, des accounts, des seances
            //pour chaque account
            var accounts = accountSrv.getList();
            for (var iA = 0, lnA = accounts.length; iA < lnA; iA++) {
                if (accounts[iA].seances) {
                    //pour chaque seance
                    for (var iS = 0, lnS = accounts[iA].seances.length; iS < lnS; iS++) {
                        //pour chaque projet
                        for (var iP = 0, lnP = accounts[iA].seances[iS].projets.length; iP < lnP; iP++) {
                            pouch.checkProjetDocument(accounts[iA].seances[iS].projets[iP].document_text, accounts[iA].seances[iS].projets[iP], accounts[iA].seances[iS], accounts[iA]);
                        }
                    }
                }
            }
        };


        pouch.checkAllProjetsDocumentByAccount = function (account) {
            if (account.seances) {
                //pour chaque seance
                for (var iS = 0, lnS = account.seances.length; iS < lnS; iS++) {
                    if(account.seances[iS].projets) {
                        //pour chaque projet
                        for (var iP = 0, lnP = account.seances[iS].projets.length; iP < lnP; iP++) {
                            pouch.checkProjetDocument(account.seances[iS].projets[iP].document_text, account.seances[iS].projets[iP], account.seances[iS], account);
                        }
                    }
                }
            }
        };



        /** X
         *  //TODO checkAllOtherdocsDocumentByAccount
         *
         * parcourt toute les seances de tous les accounts pour en extraire les id des documents des autres documents et seance/ account associés
         * @returns {undefined}
         */
        pouch.checkAllOtherdocsDocument = function () {
            //récuperation des ids des documents, des accounts, des seances
            //pour chaque account
            var accounts = accountSrv.getList();
            for (var iA = 0, lnA = accounts.length; iA < lnA; iA++) {
                if (accounts[iA].seances) {
                    //pour chaque seance
                    for (var iS = 0, lnS = accounts[iA].seances.length; iS < lnS; iS++) {
                        //pour chaque autre document
                        for (var iP = 0, lnP = accounts[iA].seances[iS].otherdocs.length; iP < lnP; iP++) {
                            pouch.checkOtherdocDocument(accounts[iA].seances[iS].otherdocs[iP].document_text, accounts[iA].seances[iS].otherdocs[iP], accounts[iA].seances[iS], accounts[iA]);
                        }
                    }
                }
            }
        };


        pouch.checkAllOtherdocsDocumentByAccount = function (account) {
            if (account.seances) {
                //pour chaque seance
                for (var iS = 0, lnS = account.seances.length; iS < lnS; iS++) {
                    if(account.seances[iS].otherdocs) {
                        //pour chaque autre document
                        for (var iP = 0, lnP = account.seances[iS].otherdocs.length; iP < lnP; iP++) {
                            pouch.checkOtherdocDocument(account.seances[iS].otherdocs[iP].document_text, account.seances[iS].otherdocs[iP], account.seances[iS], account);
                        }
                    }
                }
            }
        };


        pouch.getData = function (docId) {
            return pouchdb.getAttachment(docId, 'mypdf');
        };


        return pouch;

    });


})();
