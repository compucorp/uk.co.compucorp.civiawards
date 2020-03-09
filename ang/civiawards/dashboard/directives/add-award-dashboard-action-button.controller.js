(function (angular, getCrmUrl) {
  var module = angular.module('civiawards');

  module.controller('AddAwardDashboardActionButtonController', AddAwardDashboardActionButtonController);

  /**
   * "Add Award" dashboard action button controller.
   *
   * @param {object} $scope the scope object
   * @param {object} $window the window object reference
   * @param {Function} canCreateOrEditAwards can create or edit awards function
   * @param {Function} isAwardsScreen is award screen function
   */
  function AddAwardDashboardActionButtonController ($scope, $window, canCreateOrEditAwards, isAwardsScreen) {
    $scope.isVisible = isVisible;
    $scope.redirectToAwardsCreationScreen = redirectToAwardsCreationScreen;

    /**
     * Displays the Add Award button when on the awards dashboard and the user can create awards.
     *
     * @returns {boolean} the visibility of the button.
     */
    function isVisible () {
      return isAwardsScreen() && canCreateOrEditAwards();
    }

    /**
     * Redirects the user to the awards creation screen.
     */
    function redirectToAwardsCreationScreen () {
      var newAwardUrl = getCrmUrl('civicrm/award/a/#/awards/new');

      $window.location.href = newAwardUrl;
    }
  }
})(angular, CRM.url);
