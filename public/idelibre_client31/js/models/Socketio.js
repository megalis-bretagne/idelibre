//plus utilisé
//
////
//
//var Socketio = function () {
//};
//
//
//
//
///**
// * demande de synchronisation des seances
// * @param {Socket} socket
// * @param {Account} account
// * @returns {}
// */
//Socketio.prototype.syncSeances = function (socket, account) {
//    socket.emit('sync seances', {
//        username: credentials.username,
//        password: credentials.password,
//        seances: existingSeances
//    });
//};
//
//
///**
// * récupere les accounts correspondant au login
// * @param {Socket} socket
// * @param {Login} login
// * @returns {}
// */
//Socketio.prototype.syncAccounts = function (socket, login) {
//    socket.emit('sync accounts', {
//        username: login.username,
//        password: login.password,
//        server_url: login.server_url
//    });
//};
//
//
///**
// * 
// * creation des listenners et actions associés du socket
// * 
// * @param {Socket} socket
// * @param {number} i  position de l'account
// * @param {$scope} scope $scope du controller
// * @returns {}
// */
//Socketio.prototype.createListener = function (socket, i, scope) {
//
//    console.log(scope);
//    socket.on('hello', function (data) {
//        console.log('connect action listener');
//        scope.state = 'conn listener socket.io';
//        scope.$apply();
//        
//
//    });
//};