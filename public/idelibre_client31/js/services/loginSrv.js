(function(){
    'use strict';
   /**
    * singleton de Login
    */
   angular.module('idelibreApp').factory('loginSrv', function(){
   return new Login; 
});
    
    
})();