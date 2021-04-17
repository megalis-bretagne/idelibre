var idelibreConf = {
    version: 3.1
};

//constante créée en var et non en const à cause ie10


// etat de chargement d'un projet
var NOTLOADED = 0;
var PENDING = 1;
var LOADED = 2;
var LOAD_ERROR =3;


var CONVOCATION = 1;
var PROJET = 2;

var ACTEURS = 'b2f7a7e4-4ab1-4d93-ac13-d7ca540b4c16';
var ADMINISTRATIFS = 'db68a3c7-0119-40f1-b444-e96f568b3d67';
var INVITES = 'aa130693-e6d0-4893-ba31-98892b98581f';


var DocType = {
    PROJET : "Projet",
    CONVOCATION : "Convocation",
    ANNEXE  :"Annexe"
};
 

var OFFLINE = 0;
var ONLINE = 1;


// configuration du timeout AJX 
TIMEOUT = 70 * 1000;



var config ={};

config.API_LEVEL = "0.2.0";
// config.API_LEVEL = "0.1.0";



//si c'est l'application et non le navigateur
config.cordova = false;

var DEBUG = 1;
var INFO = 2;
var WARN = 3;

config.logLevel = DEBUG;
