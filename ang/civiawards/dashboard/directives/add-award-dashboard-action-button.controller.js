(function (angular, getCrmUrl) {
  var module = angular.module('civiawards');

  module.controller('AddAwardDashboardActionButtonController', AddAwardDashboardActionButtonController);

  /**
   * "Add Award" dashboard action button controller.
   *
   * @param {object} $scope the scope object
   * @param {object} $location the location service
   * @param {object} $window the window object reference
   */
  function AddAwardDashboardActionButtonController ($scope, $location, $window) {
    $scope.redirectToAwardsCreationScreen = redirectToAwardsCreationScreen;
    $scope.isVisible = isVisible;

    /**
     * Redirects the user to the awards creation screen.
     */
    function redirectToAwardsCreationScreen () {
      var newAwardUrl = getCrmUrl('civicrm/a/#/awards/new');

      $window.location.href = newAwardUrl;
    }

    /**
     * Is only visible on the Awards dashboard.
     *
     * @returns {boolean} true when case type category url param is awards
     */
    function isVisible () {
      var urlParams = $location.search();

      return urlParams.case_type_category === 'awards';
    }
  }
})(angular, CRM.url);
