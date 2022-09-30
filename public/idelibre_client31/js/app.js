(function () {

    'use strict';
    angular.module('idelibreApp', ['ngResource', 'ngRoute', 'angular-gestures', 'ui.bootstrap', 'ngToast', 'angularSpinner'/*, 'toaster', 'ngAnimate'*/]);


    angular.module('idelibreApp').config(function (usSpinnerConfigProvider) {
        usSpinnerConfigProvider.setDefaults({color: 'grey', radius: 30, width: 8, length: 16});
    });


    angular.module('idelibreApp').config(['$provide', function ($provide) {
        $provide.decorator('$log', ['$delegate', function ($delegate) {
            // Keep track of the original debug method, we'll need it later.
            var origDebug = $delegate.debug;
            /*
             * Intercept the call to $log.debug() so we can add on
             * our enhancement. We're going to add on a date and
             * time stamp to the message that will be logged.
             */
            $delegate.debug = function () {
                var args = [].slice.call(arguments);
                args[0] = [new Date().toString(), ': ', args[0]].join('');

                // Send on our enhanced message to the original debug method.
                origDebug.apply(null, args)
            };

            return $delegate;
        }]);
    }]);


    angular.module('idelibreApp').config(function ($routeProvider) {
        $routeProvider.when(
            '/accueil', {
                templateUrl: 'js/templates/accueil/accueil.html',
                controller: 'AccueilCtrl'
            }).when(
            '/seance/:accountId', {
                templateUrl: 'js/templates/seance/seance.html',
                controller: 'SeanceCtrl'
            }).when(
            '/odj/:seanceId/:accountId', {
                templateUrl: 'js/templates/odj/odj.html',
                controller: 'OdjCtrl'
            }).when(
            '/identification/', {
                templateUrl: 'js/templates/identification/identification.html',
                controller: 'IdentificationCtrl'
            }).when(
            '/forgetPassword/', {
                templateUrl: 'js/templates/forgetPassword/forgetPassword.html',
                controller: 'ForgetPasswordCtrl'
            }).when(
            '/convocation/:convocationDocumentId/:seanceId/:accountId', {
                templateUrl: 'js/templates/convocation/convocation.html',
                controller: 'ConvocationCtrl'
            }).when(
            '/projet/:documentId/:projetId/:seanceId/:accountId', {
                templateUrl: 'js/templates/projet/projet.html',
                controller: 'ProjetCtrl'
            }).when(
            '/otherdoc/:documentId/:otherdocId/:seanceId/:accountId', {
                templateUrl: 'js/templates/otherdoc/otherdoc.html',
                controller: 'OtherdocCtrl'
            }).when(
            '/navbar/', {
                templateUrl: 'js/templates/navbartest/navbartest.html',
                controller: 'NavbartestCtrl'
            }).when(
            '/archive/:accountId', {
                templateUrl: 'js/templates/archive/archive.html',
                controller: 'ArchiveCtrl'
            }).when(
            '/archiveSeance/:accountId/:seanceId', {
                templateUrl: 'js/templates/archiveSeance/archiveSeance.html',
                controller: 'ArchiveSeanceCtrl'
            }).when(
            '/archiveprojet/:projetId/:documentId/:accountId/:seanceId', {
                templateUrl: 'js/templates/archiveProjet/archiveProjet.html',
                controller: 'ArchiveProjetCtrl'
            }).when(
            '/annexe/:annexeId/:projetId/:seanceId/:accountId/', {
                templateUrl: 'js/templates/annexe/annexe.html',
                controller: 'AnnexeCtrl'
            }).when(
            '/archiveAnnexe/:annexeId/:projetId/:seanceId/:accountId/', {
                templateUrl: 'js/templates/archiveAnnexe/archiveAnnexe.html',
                controller: 'ArchiveAnnexeCtrl'
            }).otherwise({
            redirectTo: '/accueil'
        });

    });


})();



