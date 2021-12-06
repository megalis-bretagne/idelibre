(function () {
    'use strict';
    angular.module('idelibreApp').directive('annotationjs', function ($location, $rootScope, $modal, annotationSrv, socketioSrv, accountSrv) {


        /**
         * @namespace accountBtn
         */
        return {
            templateUrl: 'js/directives/annotationjs.html',
            restrict: 'E',
            replace: false,
            scope: {
                account: '=',
                document: '=',
                users: '=',
                action: '&'
            },
            /**
             * 
             * @param {type} $scope
             * @function accountBtn.controller
             */
            controller: function ($scope, $element) {




                var elemClicked;
                var paper;
                var stickynotesList = {};
                var activeStickynote = null; //selected stickynote
                var isModeResize = false;
                var isCreateMode = false;
                var isMoveMode = false;
                var info = null;
                var currentPage = 0
                var currentScale = 1;
                var threeDots;
                var sparseStickynotes = {};
                var mouseDownX = 0;
                var mouseDownY = 0;
                var longClickTimer;
                var swipeTimer;
                var lastTouchX = 0;
                var lastTouchY = 0;
                
                
                $scope.isCreateMode = isCreateMode;


                var addStickyToSpaseArray = function (stickynote) {
                    if (!sparseStickynotes[currentPage]) {
                        sparseStickynotes[currentPage] = [];
                    }
                    sparseStickynotes[currentPage].push(stickynote);
                }


                var removeFromStickySpaseArray = function (id, page) {
                    if (!sparseStickynotes[page]) {
                        return;
                    }
                    var index = _.findIndex(sparseStickynotes[page], function (stikynote) {
                        return stikynote.id === id;
                    });
                    if (index === -1) {
                        return;
                    }
                    sparseStickynotes[page].splice(index, 1);
                }


//init stickysparsearray with previoouslySavedAnnotations
                var initSparseStickynotes = function () {
                    for (var i = 0, ln = $scope.document.getAnnotations().length; i < ln; i++) {
                        var annot = $scope.document.getAnnotations()[i];
                        if (!sparseStickynotes[annot.page]) {
                            sparseStickynotes[annot.page] = [];
                        }

                        sparseStickynotes[annot.page].push(AnnotationUtils.annotationToStickyNote(annot, $scope.account.userId));
                    }


                }


                initSparseStickynotes();
                var popupSticky = function (stickynote) {
                    $modal.open({
                        templateUrl: 'js/templates/modalInfo/modalSitckynote.html',
                        controller: 'ModalStickyNote',
                        size: 'lg',
                        resolve: {
                            stickynote: function () {
                                return stickynote;
                            },
                            users: function () {
                                return $scope.users;
                            },
                            isSharedAnnotation: function() {
                                return $scope.account.isSharedAnnotation;
                            }
                        }
                    });
                };
                var initInfo = function () {
                    var roundInfo = paper.circle(10, 10, 10);
                    roundInfo.attr({
                        fill: "blue",
                        "fill-opacity": 1,
                        stroke: "white",
                        "stroke-width": 0,
                        "stroke-opacity": 1

                    });
                    var iText = paper.text(10, 10, "i");
                    iText.attr({
                        fill: "white",
                        "font-size": 16,
                        "font-weight": "bold",
                    });
                    info = paper.set().push(roundInfo, iText);
                    info.hide();
                }

                var initThreedots = function () {



                    var attributes = {fill: "blue",
                        "fill-opacity": 1,
                        stroke: "blue",
                        "stroke-width": 0,
                        "stroke-opacity": 1};


                    var dot31 = paper.circle(20, 20, 3).attr(attributes);
                    var dot32 = paper.circle(15, 25, 3).attr(attributes);
                    var dot33 = paper.circle(10, 30, 3).attr(attributes);
                    threeDots = paper.set().push(dot31, dot32, dot33);
                    threeDots.hide();
                }




                var setColor = function (stickynote) {
                    var attributes = {};
                    if (stickynote.sharedUserIdList.length > 0) {
                        attributes = {fill: "green",
                            "fill-opacity": 1,
                            stroke: "green",
                            "stroke-width": 0,
                            "stroke-opacity": 1};
                    } else {
                        attributes = {fill: "blue",
                            "fill-opacity": 1,
                            stroke: "blue",
                            "stroke-width": 0,
                            "stroke-opacity": 1};
                    }

                    if (stickynote.isLocked) {
                        attributes = {fill: "red",
                            "fill-opacity": 1,
                            stroke: "red",
                            "stroke-width": 0,
                            "stroke-opacity": 1};
                    }


                    return attributes;
                };


                var setThreeDotsPosition = function (x, y, width, height) {

                    var attributes = setColor(activeStickynote);

                    var eX = x + width;
                    var eY = y + height;
                    var items = threeDots.items;
                    for (var i = 0, ln = items.length; i < ln; i++) {
                        items[i].attr({
                            cx: (eX - 8) - (5 * i),
                            cy: (eY - 18) + (5 * i)
                        });
                        items[i].attr(attributes);
                    }
                    threeDots.show();
                }

                var setInfoPostion = function (x, y, width, height) {
                    var attributes = setColor(activeStickynote);

                    info.items[0].attr({//roundInfo
                        cx: x + width / 2,
                        cy: y + height / 2
                    });
                    info.items[0].attr(attributes);

                    info.items[1].attr({//iText
                        x: x + width / 2,
                        y: y + height / 2
                    });
                    info.show();
                }



                var setInfoVisible = function (x, y, width, height) {
                    setThreeDotsPosition(x, y, width, height);
                    setInfoPostion(x, y, width, height);
                }


                var setInfoVisibleColor = function (stickyNote) {
                    var attributes = setColor(stickyNote);
                    info.items[0].attr(attributes); //circle around i
                    //three dots  
                    var dots = threeDots.items;
                    for (var i = 0, ln = dots.length; i < ln; i++) {
                        dots[i].attr(attributes);
                    }
                }

                var setInfoInvisible = function () {
                    threeDots.hide();
                    info.hide();
                }


                var setOtherInactive = function (id) {
                    for (var index in stickynotesList) {
                        if (index != id) {
                            stickynotesList[index].active = false;
                        }
                    }
                }

                var getActiveStickynote = function () {
                    for (var index in stickynotesList) {
                        if (stickynotesList[index].active === true) {
                            return stickynotesList[index];
                        }
                    }
                }



                var removeFromStickynoteList = function (id) {
                    if (stickynotesList.hasOwnProperty(id)) {
                        delete stickynotesList[id];
                    }
                }


// get stickynotes in the touch zone
                var getCandidateStickynotes = function (xTouch, yTouch) {
                    var stickynoteCandidates = [];
                    for (var index in stickynotesList) {
                        var attr = stickynotesList[index].raphael.attrs;
                        if (isInRect(xTouch, yTouch, attr.x, attr.y, attr.width, attr.height)) {
                            stickynoteCandidates.push(stickynotesList[index]);
                        }
                    }
                    return stickynoteCandidates;
                }

// return true if the stickynote contain the click zone
                var isInRect = function (xTouch, yTouch, x, y, width, height) {
                    if ((xTouch >= x) && (xTouch <= x + width) &&
                            (yTouch >= y) && (yTouch <= y + height)) {
                        return true;
                    }
                    return false;
                }



//return the smallest stickynote of an stickynote array
                var getSmallestStickynote = function (stickynotes) {
                    var smallestSticky = stickynotes[0];
                    smallestSticky.aera = smallestSticky.raphael.attrs.width * smallestSticky.raphael.attrs.height;
                    for (var i = 0, ln = stickynotes.length; i < ln; i++) {
                        stickynotes[i].aera = stickynotes[i].raphael.attrs.width * stickynotes[i].raphael.attrs.height;
                        if (stickynotes[i].aera < smallestSticky.aera) {
                            smallestSticky = stickynotes[i]
                        }
                    }
                    return smallestSticky;
                }



//  initThreedots();


                var isInresizeZone = function (event, stickynote) {
                    var xTouch = event.pageX;
                    var yTouch = event.pageY;


                    if (event.originalEvent.touches) {
                        xTouch = event.originalEvent.touches[0].pageX;
                        yTouch = event.originalEvent.touches[0].pageY;
                    }


                    var stickynoteRight = stickynote.raphael.attrs.x + stickynote.raphael.attrs.width;
                    var stickynoteBottom = stickynote.raphael.attrs.y + stickynote.raphael.attrs.height;
                    if ((xTouch > stickynoteRight - 20 && xTouch < stickynoteRight + 20) && (yTouch > stickynoteBottom - 15 && yTouch < stickynoteBottom + 20)) {
                        return true;
                    }
                    return false;
                }


                var isInInfoZone = function (event, stickynote) {
                    var xTouch = event.pageX;
                    var yTouch = event.pageY;

                    if (event.originalEvent.touches) {
                        xTouch = event.originalEvent.touches[0].pageX;
                        yTouch = event.originalEvent.touches[0].pageY;
                    }




                    var stickynoteCenterX = stickynote.raphael.attrs.x + stickynote.raphael.attrs.width / 2;
                    var stickynoteCenterY = stickynote.raphael.attrs.y + stickynote.raphael.attrs.height / 2;
                    if ((xTouch > stickynoteCenterX - 20 && xTouch < stickynoteCenterX + 20) && (yTouch > stickynoteCenterY - 15 && yTouch < stickynoteCenterY + 20)) {
                        return true;
                    }
                    return false;
                }

                function DrawRectangle(x, y, w, h) {

                    var stickynote = new SitckyNote();
                    var id = guid();
                    stickynote.id = id;
                    stickynote.authorId = $scope.account.userId;
                    stickynote.authorName = $scope.account.username;
                    stickynote.text = "";
                    stickynote.page = currentPage;
                    stickynote.timestamp = new Date().getTime();
                    stickynotesList[id] = stickynote;
                    addStickyToSpaseArray(stickynote);
                    //    $scope.document.annotations.push(stickynote); TODO push annotation not stickynote !!!!

                    stickynote.raphael = paper.rect(x, y, w, h);
                    $(stickynote.raphael.node).attr('id', id);
                    stickynote.raphael.attr({
                        fill: "#9FA8DA",
                        "fill-opacity": .4,
                        stroke: "#9FA8DA",
                        "stroke-width": 2,
                        "stroke-opacity": 1

                    });

                    stickynote.rect = {x: paper.rect.x / currentScale, y: paper.rect.y / currentScale, width: paper.rect.width / currentScale, height: paper.rect.height / currentScale};


                    return stickynote;
                }


                var handleUp = function (e) {
                    $('#d1').unbind('mousemove');
                    $('#d1').unbind('touchmove');
                    
                    var distX = lastTouchX - mouseDownX;
                    var distY = lastTouchY - mouseDownY;

                    var elapsedTime = new Date().getTime() - swipeTimer;

                    if (elapsedTime <= 200 && Math.abs(distX) >= 150 && Math.abs(distY) < 80) {
                        //   e.originalEvent.preventDefault();
                        if (distX < 0) {
                            $rootScope.$broadcast("swipePage", 1);
                        } else {
                            $rootScope.$broadcast("swipePage", -1);
                        }
                        //swipe direction is define by the sign
                    }

                    clearTimeout(longClickTimer);


                    if (activeStickynote) {
                        activeStickynote.raphael.attr({
                            "fill-opacity": 0.4
                        });
                        var rect = activeStickynote.raphael.attrs;
                        activeStickynote.rect = {x: rect.x / currentScale, y: rect.y / currentScale, width: rect.width / currentScale, height: rect.height / currentScale};
                        setInfoVisible(activeStickynote.rect.x * currentScale, activeStickynote.rect.y * currentScale, activeStickynote.rect.width * currentScale, activeStickynote.rect.height * currentScale);
                    }

                    if (activeStickynote && activeStickynote.isLocked) {
                        isModeResize = false;
                        isMoveMode = false;
                        isCreateMode = false;
                        $scope.isCreateMode = false;
                        return;
                    }




                    if (isCreateMode) {
                        var BBox = activeStickynote.raphael.getBBox();
                        //if stickynote is to small
                        if (BBox.width === 0 && BBox.height === 0) {
                            removeFromStickynoteList(activeStickynote.raphael.node.id);
                            activeStickynote.raphael.remove();
                        } else {
                            popupSticky(activeStickynote);
                            var annotation = AnnotationUtils.stickyNoteToAnnotation(activeStickynote, $scope.account.userId);
                            annotation.setOrigin($scope.document, $scope.account);
                            $scope.document.addAnnotation(annotation);
                            accountSrv.save();
                            annotationSrv.addToPendingList($scope.account.id, annotation);
                            annotationSrv.save();
                            socketioSrv.sendPendingAnnotation($scope.account.id);
                        }
                    }


                    if (isModeResize || isMoveMode) {
                        var annotation = AnnotationUtils.stickyNoteToAnnotation(activeStickynote, $scope.account.userId);
                        annotation.setOrigin($scope.document, $scope.account);
                        $scope.document.addAnnotation(annotation);
                        accountSrv.save();
                        annotationSrv.addToPendingList($scope.account.id, annotation);
                        annotationSrv.save();
                        socketioSrv.sendPendingAnnotation($scope.account.id);

                    }
                    isModeResize = false;
                    isMoveMode = false;
                    isCreateMode = false;
                    $scope.isCreateMode=false;
                }

                var handleMove = function (e) {
                    // e.originalEvent.preventDefault();
                    if (activeStickynote && activeStickynote.isLocked)
                        return;

                    // Prevent text edit cursor while dragging in webkit browsers
                    if (isCreateMode || isModeResize || isMoveMode)
                        e.originalEvent.preventDefault();

                    //Touch position
                    var upX = e.pageX; //- offset.left;
                    var upY = e.pageY; //- offset.top;
                    //touch osition for mobile
                    if (e.originalEvent.touches) {
                        upX = e.originalEvent.touches[0].pageX;
                        upY = e.originalEvent.touches[0].pageY;
                    }

                    lastTouchX = upX;
                    lastTouchY = upY;

                    if (isMoveMode) {
                        var dx = (upX - activeStickynote.ox);
                        var dy = (upY - activeStickynote.oy);
                        activeStickynote.raphael.attr({
                            x: activeStickynote.x + dx,
                            y: activeStickynote.y + dy
                        });
                    }

                    if (isModeResize) {
                        setInfoInvisible();
                        activeStickynote.raphael.attr({
                            width: upX - activeStickynote.dx,
                            height: upY - activeStickynote.dy
                        });
                    } else {
                        if (isCreateMode) {
                            //  var offset = $("#d1").offset();
                            var width = upX - mouseDownX;
                            var height = upY - mouseDownY;

                            activeStickynote.raphael.attr({"width": width > 0 ? width : 0,
                                "height": height > 0 ? height : 0});
                        }
                    }
                }

                var handleDown = function (e) {
                    swipeTimer = new Date().getTime();
                    //   e.originalEvent.preventDefault();
                    var offset = $("#d1").offset();

                    mouseDownX = e.pageX; // offset.left;
                    mouseDownY = e.pageY; // - offset.top;


                    if (e.originalEvent.touches) {
                        mouseDownX = e.originalEvent.touches[0].pageX;
                        mouseDownY = e.originalEvent.touches[0].pageY;
                    }


                    if (isCreateMode) {
                        setInfoInvisible();
                        setOtherInactive(null);
                        activeStickynote = DrawRectangle(mouseDownX, mouseDownY, 0, 0);
                    } else {
                        if (activeStickynote) {
                            activeStickynote.x = activeStickynote.raphael.attr("x"); // current position of the stickynote
                            activeStickynote.y = activeStickynote.raphael.attr("y"); // current position of the stickynote
                            activeStickynote.ox = mouseDownX - offset.left; //  position cursor click
                            activeStickynote.oy = mouseDownY - offset.top;
                            activeStickynote.width = activeStickynote.raphael.attr("width");
                            activeStickynote.height = activeStickynote.raphael.attr("height");
                            activeStickynote.dx = mouseDownX - activeStickynote.width; //décalage par rapport au bord
                            activeStickynote.dy = mouseDownY - activeStickynote.height;
                        }

                        if (activeStickynote && isInresizeZone(e, activeStickynote)) {
                            isModeResize = true;
                        } else if (activeStickynote && isInInfoZone(e, activeStickynote)) {
                            isModeResize = false;
                            popupSticky(activeStickynote);
                        } else {
                            isModeResize = false;
                            longClickTimer = window.setTimeout(function () {
                                //activeStickynote.isMoveable = true;
                                activeStickynote.raphael.attr({
                                    "fill-opacity": 0.6
                                });
                                setInfoInvisible();
                                activeStickynote.x = activeStickynote.raphael.attr("x"); // current position of the stickynote
                                activeStickynote.y = activeStickynote.raphael.attr("y"); // current position of the stickynote
                                activeStickynote.ox = mouseDownX; //  position cursor click
                                activeStickynote.oy = mouseDownY;
                                activeStickynote.width = activeStickynote.raphael.attr("width");
                                activeStickynote.height = activeStickynote.raphael.attr("height");
                                isMoveMode = true;
                            }, 1200);

                            var stickynoteCandidates = getCandidateStickynotes(mouseDownX, mouseDownY);
                            if (stickynoteCandidates.length === 0) {
                                setInfoInvisible();
                                activeStickynote = null;
                            }

                            if (stickynoteCandidates.length > 0) {
                                var selectedStickynote = getSmallestStickynote(stickynoteCandidates);
                                var x = selectedStickynote.raphael.attrs.x;
                                var y = selectedStickynote.raphael.attrs.y;
                                var width = selectedStickynote.raphael.attrs.width;
                                var height = selectedStickynote.raphael.attrs.height;
                                activeStickynote = selectedStickynote;
                                setInfoVisible(x, y, width, height);
                                //if (!selectedStickynote.active) {
                                selectedStickynote.active = true;
                                setOtherInactive(selectedStickynote.raphael.node.id);
                                //  return false;
                                //}
                            }
                        }
                    }


                    $("#d1").mousemove(handleMove);
                    $("#d1").bind("touchmove", function (e) {
                        handleMove(e)
                    });
                    return false;

                }


                $('#d1').unbind('mousedown');
                $('#d1').unbind('mousemove');
                $('#d1').unbind('mouseup');
                $('#d1').unbind('touchstart');
                $('#d1').unbind('touchmove');
                $("#d1").mousedown(handleDown);
                $("#d1").bind("touchstart", function (e) {
                    handleDown(e);
                });

                $("#d1").mouseup(handleUp);
                $("#d1").bind("touchend", function (e) {
                    handleUp(e);
                });



                $scope.toggleAnnotationMode = function () {
                    isCreateMode = !isCreateMode;
                    $scope.isCreateMode = isCreateMode;
                    setInfoInvisible();
                    setOtherInactive(null);
                }

                var drawStickies = function () {
                    for (var index in stickynotesList) {
                        drawSticky(stickynotesList[index]);
                    }
                }

                function drawSticky(stickynote) {
                    stickynote.raphael = paper.rect(stickynote.rect.x * currentScale, stickynote.rect.y * currentScale, stickynote.rect.width * currentScale, stickynote.rect.height * currentScale);
                    stickynote.raphael.attr({
                        fill: "#9FA8DA",
                        "fill-opacity": .4,
                        stroke: "#9FA8DA",
                        "stroke-width": 2,
                        "stroke-opacity": 1
                    });
                    $(stickynote.raphael.node).attr('id', stickynote.id);

                    if (stickynote.sharedUserIdList.length > 0) {

                        if (stickynote.isLocked) {
                            stickynote.raphael.attr({
                                fill: "#EF9A9A",
                                "fill-opacity": .4,
                                stroke: "#EF9A9A",
                                "stroke-width": 2,
                                "stroke-opacity": 1
                            });
                        } else {
                            //if($scope.account.user_id == stickynote.authorId)
                            stickynote.raphael.attr({
                                fill: "#A5D6A7",
                                "fill-opacity": .4,
                                stroke: "#A5D6A7",
                                "stroke-width": 2,
                                "stroke-opacity": 1
                            });
                        }
                    }
                }


                var setStickynoteList = function (stickiesArray) {
                    stickynotesList = {};
                    for (var i = 0, ln = stickiesArray.length; i < ln; i++) {
                        stickynotesList[stickiesArray[i].id] = stickiesArray[i]
                    }

                }

                function chooseStickynoteColor(stickyNote) {
                    if (stickyNote.sharedUserIdList.length > 0) {
                        return {
                            fill: "#A5D6A7",
                            "fill-opacity": .4,
                            stroke: "#A5D6A7",
                            "stroke-width": 2,
                            "stroke-opacity": 1
                        };
                    } else {
                        return {
                            fill: "#9FA8DA",
                            "fill-opacity": .4,
                            stroke: "#9FA8DA",
                            "stroke-width": 2,
                            "stroke-opacity": 1
                        };
                    }
                }





                $scope.$on("refreshSticky", function (e, data) {
                    data.stickynote.raphael.attr(chooseStickynoteColor(data.stickynote));
                    setInfoVisibleColor(data.stickynote);
                    var annotation = AnnotationUtils.stickyNoteToAnnotation(activeStickynote, $scope.account.userId);
                    annotation.setOrigin($scope.document, $scope.account);
                    $scope.document.addAnnotation(annotation);
                    accountSrv.save();
                    annotationSrv.addToPendingList($scope.account.id, annotation);
                    annotationSrv.save();
                    socketioSrv.sendPendingAnnotation($scope.account.id);
                });


                $scope.$on("pageRendered", function (e, data) {
                    if (paper) {
                        paper.remove();
                    }

                    paper = Raphael("d1", data.width, data.height);
                    currentPage = data.page;
                    currentScale = data.scale;
                    //  stickynotesList = sparseStickynotes[currentPage] || [];
                    setStickynoteList(sparseStickynotes[currentPage] || []);
                    drawStickies();
                    initInfo();
                    initThreedots();
                });






                $scope.$on("deleteSticky", function (e, data) {
                    setInfoInvisible();
                    //remove from screen
                    $('#' + data.stickynote.id).remove();
                    //remove from stickynotesList
                    delete stickynotesList[data.stickynote.id];
                    removeFromStickySpaseArray(data.stickynote.id, data.stickynote.page);
                    annotationSrv.addToDeleteList($scope.account.id, data.stickynote.id);
                    annotationSrv.save();
                    socketioSrv.sendDeleteAnnotation($scope.account.id);
                    $scope.document.deleteAnnotation(data.stickynote.id);
                    accountSrv.save();
                });







                //UUID GENERATOR
                function guid() {
                    function s4() {
                        return Math.floor((1 + Math.random()) * 0x10000)
                                .toString(16)
                                .substring(1);
                    }
                    return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
                            s4() + '-' + s4() + s4() + s4();
                }










            }

        };
    });
})();
