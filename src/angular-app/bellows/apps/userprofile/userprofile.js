'use strict';

angular.module('userprofile', ['jsonRpc', 'ui.bootstrap', 'bellows.services', 'palaso.ui.notice',
  'palaso.ui.intlTelInput'])
.controller('userProfileCtrl', ['$scope', 'userService', 'sessionService', 'utilService',
  'silNoticeService', '$window',
function ($scope, userService, ss, util, notice, $window) {
  $scope.getAvatarUrl = util.getAvatarUrl;

  function getAvatarRef(color, shape) {
    if (!color || !shape) {
      return 'anonymoose.png';
    }

    return color + '-' + shape + '-128x128.png';
  }

  var initColor = ''; var initShape = '';

  $scope.user = { avatar_color: '', avatar_shape: '' };
  $scope.user.avatar_ref = getAvatarRef('', '');

  $scope.$watch('user.avatar_color', function () {
    $scope.user.avatar_ref = getAvatarRef($scope.user.avatar_color, $scope.user.avatar_shape);
  });

  $scope.$watch('user.avatar_shape', function () {
    $scope.user.avatar_ref = getAvatarRef($scope.user.avatar_color, $scope.user.avatar_shape);
  });

  var loadUser = function () {
    userService.readProfile(function (result) {
      if (result.ok) {
        $scope.user = result.data.userProfile;
        initColor = $scope.user.avatar_color;
        initShape = $scope.user.avatar_shape;
        $scope.projectsSettings = result.data.projectsSettings;

        // populate the project pickList default values with the userProfile picked values
        for (var i = 0; i < $scope.projectsSettings.length; i++) {
          var project = $scope.projectsSettings[i];
          if (project.userProperties && project.userProperties.userProfilePickLists) {
            angular.forEach(project.userProperties.userProfilePickLists,
              function (pickList, pickListId) {
                if ($scope.user.projectUserProfiles[project.id]) {  // ensure user has profile data
                  if ($scope.user.projectUserProfiles[project.id][pickListId])
                    $scope.projectsSettings[i].userProperties.userProfilePickLists[pickListId]
                      .defaultKey = $scope.user.projectUserProfiles[project.id][pickListId];
                }
              }
            );
          }
        }
      }
    });
  };

  $scope.updateUser = function () {
    // populate the userProfile picked values from the project pickLists
    for (var i = 0; i < $scope.projectsSettings.length; i++) {
      var project = $scope.projectsSettings[i];
      $scope.user.projectUserProfiles[project.id] = {};
      if (project.userProperties && project.userProperties.userProfilePickLists) {
        angular.forEach(project.userProperties.userProfilePickLists,
          function (pickList, pickListId) {
            $scope.user.projectUserProfiles[project.id][pickListId] = pickList.defaultKey;
          }
        );
      }
    }

    userService.updateProfile($scope.user, function (result) {
      if (result.ok) {
        if ($scope.user.avatar_color != initColor || $scope.user.avatar_shape != initShape) {
          var newAvatarUrl = $scope.getAvatarUrl($scope.user.avatar_ref);
          ['mobileSmallAvatarURL', 'smallAvatarURL'].forEach(function (id) {
            $window.document.getElementById(id).src = newAvatarUrl;
          })
        }
        notice.push(notice.SUCCESS, 'Profile updated successfully');
      }
    });
  };

  loadUser(); // load the user data right away

  $scope.dropdown = {};

  $scope.dropdown.avatarColors = [
    { value:'purple4', label:'Purple' },
    { value:'green', label:'Green' },
    { value:'chocolate4', label:'Chocolate' },
    { value:'turquoise4', label:'Turquoise' },
    { value:'LightSteelBlue4', label:'Steel Blue' },
    { value:'DarkOrange', label:'Dark Orange' },
    { value:'HotPink', label:'Hot Pink' },
    { value:'DodgerBlue', label:'Blue' },
    { value:'plum', label:'Plum' },
    { value:'red', label:'Red' },
    { value:'gold', label:'Gold' },
    { value:'salmon', label:'Salmon' },
    { value:'DarkGoldenrod3', label:'Dark Golden' },
    { value:'chartreuse', label:'Chartreuse' },
    { value:'LightBlue', label:'Light Blue' },
    { value:'LightYellow', label:'Light Yellow' }
  ];

  $scope.dropdown.avatarShapes = [
    { value:'camel', label:'Camel' },
    { value:'cow', label:'Cow' },
    { value:'dog', label:'Dog' },
    { value:'elephant', label:'Elephant' },
    { value:'frog', label:'Frog' },
    { value:'gorilla', label:'Gorilla' },
    { value:'hippo', label:'Hippo' },
    { value:'horse', label:'Horse' },
    { value:'kangaroo', label:'Kangaroo' },
    { value:'mouse', label:'Mouse' },
    { value:'otter', label:'Otter' },
    { value:'pig', label:'Pig' },
    { value:'rabbit', label:'Rabbit' },
    { value:'rhino', label:'Rhino' },
    { value:'sheep', label:'Sheep' },
    { value:'tortoise', label:'Tortoise' }
  ];

}])

;
