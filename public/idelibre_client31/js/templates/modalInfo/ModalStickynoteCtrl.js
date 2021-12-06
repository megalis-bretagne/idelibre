(function () {
    angular.module('idelibreApp').controller('ModalStickyNote', function ($scope, $modalInstance, stickynote, users, isSharedAnnotation, $rootScope) {

        $scope.users = users;
        $scope.groupesPolitiques = getgroupesPolitiques();
        $scope.isSharedAnnotation = isSharedAnnotation;

        // Unselect every users;
        $scope.users.forEach(function (user) {
            if (stickynote.sharedUserIdList.indexOf(user.id) > -1) {
                user.isShared = true;
            } else {
                user.isShared = false;
            }
        });


        _.each($scope.groupesPolitiques, function (group) {
            group.isSelected = false;
        });
        
        
        if(stickynote.sharedUserIdList.length > 0){
            $scope.shared = {status: true}
        }else{
            $scope.shared = {status: false}
        }



        $scope.stickyNote = stickynote;
        $scope.stickyNote.formatedDate = DateUtils.formatedDateWithTime($scope.stickyNote.timestamp);
        
        $scope.sticky = {};
        $scope.sticky.text = stickynote.text;
        $scope.color = "notSelected";


        $scope.sharedChange = function (data) {
        };

        $scope.cancel = function () {
            $modalInstance.dismiss('cancel');

        };


        $scope.ok = function () {
            stickynote.text = $scope.sticky.text;
            addSharedUserIds();
            $rootScope.$broadcast("refreshSticky", {stickynote: stickynote});
            $modalInstance.dismiss('cancel');

        };


        function addSharedUserIds() {
            stickynote.sharedUserIdList = [];
            $scope.users.forEach(function (user) {
                if (user.isShared)
                    stickynote.sharedUserIdList.push(user.id);
            });
        }


        $scope.deleteSticky = function () {
            $rootScope.$broadcast("deleteSticky", {stickynote: stickynote})
            $modalInstance.dismiss('cancel');
        };


        $scope.clickOnUser = function (user) {
            user.isShared = !user.isShared;
        }

        $scope.clickOnGP = function (groupePol) {
            groupePol.isSelected = !groupePol.isSelected;

            $scope.users.forEach(function (user) {
                if (user.groupepolitique_id === groupePol.id)
                    user.isShared = groupePol.isSelected;
            });
        }


        function getgroupesPolitiques() {
            var groupesPolitiques = {};
            users.forEach(function (user) {
                if (!groupesPolitiques[user.groupepolitique_id]) {
                    groupesPolitiques[user.groupepolitique_id] = {id: user.groupepolitique_id, name: user.groupepolitique_name};
                }
            });
            return groupesPolitiques;
        }





    });

})();
