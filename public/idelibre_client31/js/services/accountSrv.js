
(function () {


    angular.module('idelibreApp').factory('accountSrv', function () {

        var accounts = {};

        var list = [];



        accounts.getList = function () {
            return list;
        };

        accounts.setList = function (accounts) {
            list = accounts;
        };

        accounts.add = function (account) {
            var accountsList = list;
            var alreadyIn = _.find(accountsList, function (el) {
                return el.id === account.id;
            });
            if (alreadyIn) {
                return;
            }
            list.push(account);
        };


        accounts.removePassword = () =>  {
            list.forEach(account => account.password = '' );
        }

        accounts.removeToken = () =>  {
            list.forEach(account => account.token = null );
        }

        accounts.delete = function (accountId) {
            var index = _.findIndex(list, function (account) {
                return account.id == accountId;
            });
            if (index > -1) {
                list.splice(index, 1);
            }
        };


        accounts.findAccountById = function (accountId) {
            var account = _.find(list, function (account) {
                return account.id == accountId;
            });
            return account;
        };


        var serialize = function (login) {
            return(JSON.stringify(login));
        };

        accounts.save = function () {
            localStorage.setItem('accountsStorage', serialize(this.getList()));
        };


        accounts.load = function () {
            var jsonAccounts = JSON.parse(localStorage.getItem('accountsStorage'));
            var accountDAO = new AccountDAO();
            if (jsonAccounts) {
                for (var i = 0; i < jsonAccounts.length; i++) {
                  this.add(accountDAO.unserialize(jsonAccounts[i]));
                }
            }
        };

        return accounts;

    });

})();