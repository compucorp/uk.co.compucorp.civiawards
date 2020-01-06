(function (angular, getCrmUrl) {
  var module = angular.module('civiawards');

  module.controller('AddAwardDashboardActionButtonController', AddAwardDashboardActionButtonController);

  /**
   * "Add Award" dashboard action button controller.
   *
   * @param {object} $scope the scope object
   * @param {object} $window the window object reference
   * @param {Function} isAwardScreen is award screen function
   */
  function AddAwardDashboardActionButtonController ($scope, $window, isAwardScreen) {
    $scope.redirectToAwardsCreationScreen = redirectToAwardsCreationScreen;
    $scope.isVisible = isAwardScreen;

    /**
     * Redirects the user to the awards creation screen.
     */
    function redirectToAwardsCreationScreen () {
      var newAwardUrl = getCrmUrl('civicrm/a/#/awards/new');

      $window.location.href = newAwardUrl;
    }
  }
})(angular, CRM.url);
