(function () {

    angular.module('idelibreApp').factory('socketioSrv', function (accountSrv, $rootScope, localDbSrv, sslCheckerSrv, $log, arSrv, annotationSrv, $http) {

        var socketio = {};

        socketio.reloadConvocationAndCheckProjet = false;
        socketio.status = false;

        var accountIdSocketMap = {};

        var addUserList = function (socket, token, seanceId) {
            socket.emit("getUserList", {
                token: token,
                seanceId: seanceId
            });
        }

        /**
         * Close every sockets
         * @returns {void}
         */
        socketio.killSockets = function () {
            for (var key in accountIdSocketMap) {
                accountIdSocketMap[key].disconnect();
            }
            accountIdSocketMap = {}
        }

        /**
         * Init sockets for every accounts
         * @returns {undefined}
         */
        socketio.initSockets = function () {
            for (var i = 0, ln = accountSrv.getList().length; i < ln; i++) {
                console.log(config.API_LEVEL);
                var socket = io.connect(accountSrv.getList()[i].url + '/' + config.API_LEVEL, {
                    forceNew: true,
                    path: '/socket.io'
                });
                initListener(socket, accountSrv.getList()[i]);
            }
        };

        /**
         * Set socket listeners
         * @param {Socket} socket
         * @param {Account} account
         * @returns {void}
         */
        var initListener = function (socket, account) {
            //check if server is reachable
            $http.get(account.url + '/nodejs/checkConnection')

                .success(function (data, status, headers, config) {
                    //data = server version
                })
                .error(function (data, status, headers, config) {
                    //close this websocket
                    $rootScope.$broadcast('notify', {
                        class: 'danger',
                        content: "<b>" + account.name + "</b>" + Messages.CANT_REACH_SERVER
                    });
                    //  socket.disconnect();
                    account.status = OFFLINE;
                    //  return;
                });


            socket.on('hello', function () {
                accountIdSocketMap[account.id] = socket;
                console.log(account.token);
                socket.emit('authenticate',
                    {
                        username: account.username,
                        password: account.password,
                        suffix: account.suffix,
                        token: account.token
                    });
            });

            /**
             * authentification feedBack
             */
            socket.on('authFeedback', function (data) {

                if (!data.success) {
                    $rootScope.$broadcast('notify', {
                        class: 'danger',
                        content: "<b>" + account.name + "</b> " + Messages.AUTH_ERROR
                    });
                    socket.disconnect();
                    account.status = OFFLINE;

                    accountSrv.removePassword();
                    accountSrv.removeToken();
                    accountSrv.save();
                    return;
                }


                $rootScope.$broadcast('notify', {
                    class: 'success',
                    content: "<b>" + account.name + "</b>" + Messages.AUTH_SUCCESS
                });
                account.token = data.token;
                account.userId = data.userId;
                //set account status online en broadcast it


                account.isSharedAnnotation = data.configuration.isSharedAnnotation;
                account.status = ONLINE;
                $rootScope.$broadcast('connectionStatus', {state: true});

                var decoded = jwt_decode(data.token);

                account.type = decoded.group_id;

                // List already sync seances with rev number ({seanceId: seanceId, seanceRev: seanceRevision})
                var syncSeanceList = _.map(account.seances, function (seance) {
                    return {seanceId: seance.id, seanceRev: seance.rev};
                });

                //save locally
                accountSrv.removePassword();
                accountSrv.save();


                socketio.sendARByAccount(account);
                socketio.sendAnnotationRead(account.id);
                socketio.sendPendingAnnotation(account.id);
                socketio.sendDeleteAnnotation(account.id);

                //check all convocations !


                if (account.type == ACTEURS) {
                    localDbSrv.getAllConvocationByAccount(account);
                } else {
                    localDbSrv.getAllInvitationsByAccount(account);
                }

                //ask for seances to server
                socket.emit("updateSeances", {
                    token: account.token,
                    seancesList: syncSeanceList
                });
            });

            /**
             * get the new, deleten modified seances from server
             */
            socket.on('updateSeancesFeedback', function (data) {
                account.type = ACTEURS;
                var json = JSON.parse(data); //todo try catch
                console.log(json);

                if (!_.isEmpty(json.toAdd)) {
                    account.addSeances(json.toAdd);
                    $rootScope.$broadcast('notify', {class: 'info', content: Messages.SEANCE_ADD});
                    // Ask for user list
                    json.toAdd.forEach(function (seance) {
                        addUserList(socket, account.token, seance.seance_id);
                    });
                }

                if (!_.isEmpty(json.toModify)) {
                    $rootScope.$broadcast('notify', {class: 'info', content: Messages.SEANCE_MODIFIED});
                    json.toModify.forEach(function (seance) {
                        //update modified seance
                        account.replaceSeance(seance);
                        //ask for user list
                        addUserList(socket, account.token, seance.seance_id);
                        $rootScope.$broadcast('refresh_modify', {seanceId: seance.seance_id});
                    });


                }

                if (!_.isEmpty(json.toRemove)) {
                    $rootScope.$broadcast('notify', {class: 'warning', content: Messages.SEANCE_DELETED});
                    account.removeSeances(json.toRemove);
                    _.each(json.toRemove, function (seanceToRemove) {
                        localDbSrv.removeSeanceLocalDb(seanceToRemove.seanceId, account);
                    });
                }

                if (!_.isEmpty(json.toModify) || !_.isEmpty(json.toAdd)) {
                    localDbSrv.getAllConvocationByAccount(account);
                    //localDbSrv.checkAllProjetsDocument();
                    localDbSrv.checkAllProjetsDocumentByAccount(account);
                    localDbSrv.checkAllOtherdocsDocumentByAccount(account);
                }

                // Save locally
                accountSrv.save();

                //ask for annotations
                socket.emit("getAnnotations", {token: account.token});
            });


            socket.on('updateSeancesInvitesFeedback', function (data) {
                account.type = INVITES;
                var json = JSON.parse(data);
                console.log('updateSeancesInvitesFeedback');
                console.log(json);
                if (!_.isEmpty(json.toAdd)) {
                    account.addSeances(json.toAdd);
                    $rootScope.$broadcast('notify', {class: 'info', content: Messages.SEANCE_ADD});
                }

                if (!_.isEmpty(json.toRemove)) {
                    $rootScope.$broadcast('notify', {class: 'warning', content: Messages.SEANCE_DELETED});
                    account.removeSeances(json.toRemove);
                }


                if (!_.isEmpty(json.toModify)) {
                    $rootScope.$broadcast('notify', {class: 'info', content: Messages.SEANCE_MODIFIED});
                    json.toModify.forEach(function (seance) {
                        //update modified seance
                        account.replaceSeance(seance);
                        $rootScope.$broadcast('refresh_modify', {seanceId: seance.seance_id});
                    });

                }

                if (!_.isEmpty(json.toModify) || !_.isEmpty(json.toAdd)) {
                    localDbSrv.getAllInvitationsByAccount(account);
                    localDbSrv.checkAllProjetsDocumentByAccount(account);
                    localDbSrv.checkAllOtherdocsDocumentByAccount(account);
                }

                // Save locally
                accountSrv.save();

            });


            socket.on('updateSeancesAdministratifsFeedback', function (data) {

                account.type = ADMINISTRATIFS;
                var json = JSON.parse(data);
                console.log("updateSeancesAdministratifsFeedback");
                console.log(json);

                if (!_.isEmpty(json.toAdd)) {
                    account.addSeances(json.toAdd);
                    $rootScope.$broadcast('notify', {class: 'info', content: Messages.SEANCE_ADD});


                }

                if (!_.isEmpty(json.toRemove)) {
                    $rootScope.$broadcast('notify', {class: 'warning', content: Messages.SEANCE_DELETED});
                    account.removeSeances(json.toRemove);
                }


                if (!_.isEmpty(json.toModify)) {
                    $rootScope.$broadcast('notify', {class: 'info', content: Messages.SEANCE_MODIFIED});
                    json.toModify.forEach(function (seance) {
                        //update modified seance
                        account.replaceSeance(seance);
                        $rootScope.$broadcast('refresh_modify', {seanceId: seance.seance_id});
                    });

                }

                if (!_.isEmpty(json.toModify) || !_.isEmpty(json.toAdd)) {
                    localDbSrv.getAllInvitationsByAccount(account);
                    localDbSrv.checkAllProjetsDocumentByAccount(account);
                    localDbSrv.checkAllOtherdocsDocumentByAccount(account);
                }

                localDbSrv.getAllInvitationsByAccount(account);
                accountSrv.save();


            });


            socket.on('userList', function (data) {
                var users;
                try {
                    users = JSON.parse(data);
                    console.log(users);
                } catch (e) {
                    console.log(e);
                    return;
                }
                //get the seance
                if (users.length === 0)
                    return;
                var seanceId = users[0].seance_id;
                var seance = account.findSeance(seanceId);
                if (!seance)
                    return;
                seance.users = users;
                accountSrv.save();

            });


            socket.on("getAnnotationsFeedback", function (data) {
                var jsonAnnots;
                try {
                    jsonAnnots = JSON.parse(data);
                } catch (e) {
                    console.log(e);
                    return;
                }

                account.deleteAllAnnotations();

                for (var i = 0, ln = jsonAnnots.length; i < ln; i++) {
                    var annot = jsonAnnots[i];
                    var annotation = new Annotation(annot.annotation_id, annot.annotation_author_id, annot.annotation_author_name,
                        annot.annotation_text, annot.annotation_rect, annot.annotation_page + 1, annot.annotation_shareduseridlist, annot.annotation_date);
                    if (annot.isread) {
                        annotation.isRead = true;
                    }
                    if (annot.annotation_projet_id) {
                        addAnnotationToProjet(annotation, account, annot.annotation_projet_id);
                    }
                    if (annot.annotation_seance_id) {
                        addAnnotationToConvocation(annotation, account, annot.annotation_seance_id);
                    }

                    if (annot.annotation_annexe_id) {
                        addAnnotationToAnnexe(annotation, account, annot.annotation_annexe_id);
                    }

                }
                $rootScope.$broadcast('refreshAnnotations', {});
            });


            socket.on("newAnnotation", function (data) {
                console.log(data);
                var parse;
                try {
                    parse = JSON.parse(data);
                } catch (e) {
                    console.log(e);
                    return;
                }


                var annot;
                try {
                    annot = parse.annotation;
                } catch (e) {
                    console.log(e);
                    return;
                }


                var annotation = new Annotation(annot.annotation_id, annot.annotation_author_id, annot.annotation_author_name,
                    annot.annotation_text, annot.annotation_rect, annot.annotation_page + 1, annot.annotation_shareduseridlist, annot.annotation_date);


                if (annot.originType == DocType.PROJET) {
                    addAnnotationToProjet(annotation, account, annot.originId)
                }

                if (annot.originType == DocType.CONVOCATION) {
                    addAnnotationToConvocation(annotation, account, annot.originId)
                }


                if (annot.originType == DocType.ANNEXE) {
                    addAnnotationToAnnexe(annotation, account, annot.originId)
                }

                //TODO CHECK CONVOCATION AND ANNEXE (ANNEXE NO RELOAD IMAGE)
                accountSrv.save();

                $rootScope.$broadcast('notify', {
                    class: 'info',
                    content: '<b>Annotation : </b>' + annotation.authorName + Messages.ANNOTATION_SHARED
                });
                $rootScope.$broadcast('refreshAnnotationItems', {id: annot.originId});
            });


            socket.on('sharedAnnotationDeleted', function (data) {
                //find the document and the annotaion position
                var toDelete = account.findAnnotationIndex(data.annotation_Id);
                //remove it
                toDelete.doc.getAnnotations().splice(toDelete.pos, 1);

                $rootScope.$broadcast("refreshAnnotations", {});

                accountSrv.save();

                //todo refresh annotaions icones !


            });


            socket.on('disconnect', function () {
                $log.debug('disconnect');
                socketio.status = false;
                account.status = OFFLINE;
                delete accountIdSocketMap[account.id];
                $rootScope.$broadcast('connectionStatus', {state: false});
            });


            socket.on('reconnect', function (data) {
            });

            socket.on('connect', function (data) {
            })


            socket.on('convocationReadFeedBack', function () {
                //clear the ars 
                arSrv.getList()[account.id] = [];
                arSrv.save();
            });

            socket.on('newSeance', function (data) {
                //FIX : same with updateSeances
                var syncSeanceList = _.map(account.seances, function (seance) {
                    return {seanceId: seance.id, seanceRev: seance.rev};
                });
                socket.emit("updateSeances", {
                    token: account.token,
                    seancesList: syncSeanceList
                });
            });


            socket.on('modifiedSeance', function (data) {
                //FIX: same function again 
                var syncSeanceList = _.map(account.seances, function (seance) {
                    return {seanceId: seance.id, seanceRev: seance.rev};
                });
                socket.emit("updateSeances", {
                    token: account.token,
                    seancesList: syncSeanceList
                });
            });


            socket.on('removeSeance', function (data) {
                //FIX: same function again
                var syncSeanceList = _.map(account.seances, function (seance) {
                    return {seanceId: seance.id, seanceRev: seance.rev};
                });
                socket.emit("updateSeances", {
                    token: account.token,
                    seancesList: syncSeanceList
                });
            });

            socket.on('addNewAnnotationFeedback', function (data) {
                annotationFeedback(account);
            });

            socket.on('updateAnnotationFeedback', function (data) {
                annotationFeedback(account);

            });

            socket.on("deleteAnnotationFeedback", function (data) {
                annotationSrv.clearDeleteListByAccountId(account.id);
                annotationSrv.save();
            });


            socket.on("sendAnnotationReadFeedback", function (data) {
                annotationSrv.clearReadListByAccountId(account.id);
                annotationSrv.save();
            });


            socket.on("archivedSeancesListFeedBack", function (data) {
                var jsonSeances;
                try {
                    jsonSeances = JSON.parse(data);
                    console.log(jsonSeances);
                } catch (e) {
                    console.log(e);
                    return;
                }
                var seanceDao = new SeanceDAO();
                var archivedSeances = [];
                for (var i = 0, ln = jsonSeances.length; i < ln; i++) {
                    archivedSeances.push(seanceDao.unserialize(jsonSeances[i]));
                }
                $rootScope.$broadcast('archivedSeancesList', {archivedSeances: archivedSeances});
            });


            socket.on("archivedProjetsListFeedBack", function (data) {
                var jsonProjets;

                try {
                    jsonProjets = JSON.parse(data);
                    console.log(jsonProjets);
                } catch (e) {
                    console.log(e);
                    return;
                }
                var projetDao = new ProjetDAO();
                var archivedProjets = [];
                for (var i = 0, ln = jsonProjets.length; i < ln; i++) {
                    archivedProjets.push(projetDao.unserialize(jsonProjets[i]));
                }
                $rootScope.$broadcast('archivedProjetsList', {archivedProjets: archivedProjets});
            });


            socket.on("archivedOtherdocsListFeedBack", function (data) {
                var jsonOtherdocs;

                try {
                    jsonOtherdocs = JSON.parse(data);
                    console.log(jsonOtherdocs);
                } catch (e) {
                    console.log(e);
                    return;
                }
                var otherdocDao = new OtherdocDAO();
                var archivedOtherdocs = [];
                for (var i = 0, ln = jsonOtherdocs.length; i < ln; i++) {
                    archivedOtherdocs.push(otherdocDao.unserialize(jsonOtherdocs[i]));
                }
                $rootScope.$broadcast('archivedOtherdocsList', {archivedOtherdocs: archivedOtherdocs});
            });
        };


        function annotationFeedback(account) {


            annotationSrv.clearPendingListByAccountId(account.id);
            annotationSrv.save();
        }


        function addAnnotationToProjet(annotation, account, projetId) {
            for (var i = 0, ln = account.seances.length; i < ln; i++) {
                var projet = account.seances[i].findProjet(projetId)
                if (projet) {
                    projet.addAnnotation(annotation);
                    return;
                }
            }
        }


        function addAnnotationToConvocation(annotation, account, seanceId) {

            for (var i = 0, ln = account.seances.length; i < ln; i++) {

                if (account.seances[i].id == seanceId) {
                    account.seances[i].convocation.addAnnotation(annotation);
                    return;
                }
            }
        }


        function addAnnotationToAnnexe(annotation, account, annexeId) {
            for (var i = 0, ln = account.seances.length; i < ln; i++) {
                for (var j = 0, lj = account.seances[i].getProjets().length; j < lj; j++) {
                    var projet = account.seances[i].getProjets()[j];
                    var annexe = projet.findAnnexe(annexeId);
                    if (annexe) {
                        annexe.addAnnotation(annotation)
                    }

                }
            }
        }


        socketio.sendARByAccount = function (account) {
            var ars = arSrv.getList()
            if (account.status == ONLINE) {
                var socket = accountIdSocketMap[account.id];
                if (socket) {
                    socket.emit('ARs', {token: account.token, seancesId: ars[account.id]});
                }
            }
        };


        // /**
        //  *
        //  * @param {Json} data  seanceId, accountId
        //  * @returns {undefined}
        //  */
        // socketio.sendAR = function () {
        //     var ars = arSrv.getList()
        //     for (var accountId in ars) {
        //         var account = accountSrv.findAccountById(accountId);
        //         if(!account){
        //             return;
        //         }
        //         if (account.status == ONLINE) {
        //             var socket = accountIdSocketMap[accountId];
        //             if (socket) {
        //                 socket.emit('ARs', {token: account.token, seancesId: ars[accountId]});
        //             }
        //         }
        //     }
        // };
        //


        socketio.sendAR = function () {
            var ars = arSrv.getList()
            for (var accountId in ars) {
                var account = accountSrv.findAccountById(accountId);

                if (account && account.status == ONLINE) {
                    var socket = accountIdSocketMap[accountId];
                    if (socket) {
                        socket.emit('ARs', {token: account.token, seancesId: ars[accountId]});
                    }
                }
            }
        };


        socketio.sendAnnotationRead = function (accountId) {
            var account = accountSrv.findAccountById(accountId);

            if (account.status != ONLINE)
                return;

            var annotationIds = annotationSrv.getReadListByAccountId(accountId);
            var socket = accountIdSocketMap[accountId];
            if (socket) {
                socket.emit("sendAnnotationRead", {token: account.token, annotationIds: annotationIds})
            }
        };


        socketio.sendPendingAnnotation = function (accountId) {

            var account = accountSrv.findAccountById(accountId);
            if (account.status != ONLINE)
                return;

            var annotations = annotationSrv.getServerFormatedPendingList(accountId);
            var socket = accountIdSocketMap[accountId];
            if (socket) {
                socket.emit("annotation", {token: account.token, annotation: annotations})
            }
        }


        socketio.sendDeleteAnnotation = function (accountId) {
            var account = accountSrv.findAccountById(accountId);
            if (account.status != ONLINE)
                return;

            var socket = accountIdSocketMap[accountId];
            var ids = annotationSrv.getDeleteListByAccountId(accountId);
            if (socket) {
                socket.emit("deleteAnnotations", {token: account.token, ids: ids})
            }
        }


        socketio.archivedSeancesList = function (account) {
            var socket = accountIdSocketMap[account.id];
            if (socket) {
                socket.emit("archivedSeancesList", {token: account.token});
            }
        }

        socketio.archivedProjets = function (account, seanceId) {
            var socket = accountIdSocketMap[account.id];
            if (socket) {
                socket.emit("archivedProjetsList", {token: account.token, seanceId: seanceId});
            }
        }

        socketio.sendConfirmPresence = function (account, seanceId, status, mandataire) {
            var socket = accountIdSocketMap[account.id];
            if (socket) {
                socket.emit("sendPresence", {
                    token: account.token,
                    seanceId: seanceId,
                    presentStatus: status,
                    procuration_name: mandataire
                });
            }
        }

        socketio.archivedOtherdocs = function (account, seanceId) {
            var socket = accountIdSocketMap[account.id];
            if (socket) {
                socket.emit("archivedOtherdocsList", {token: account.token, seanceId: seanceId});
            }
        }

        return socketio;
    });
})();
