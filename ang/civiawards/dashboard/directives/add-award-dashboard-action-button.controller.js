(function (angular, getCrmUrl) {
  var module = angular.module('civiawards');

  module.controller('AddAwardDashboardActionButtonController', AddAwardDashboardActionButtonController);

  /**
   * "Add Award" dashboard action button controller.
   *
   * @param {object} $scope the scope object
   * @param {object} $routeParams route paramters
   * @param {object} $window the window object reference
   * @param {Function} canCreateOrEditAwards can create or edit awards function
   * @param {Function} isApplicationManagementScreen is application management screen function
   * @param {object} CaseTypeCategory case type category service
   */
  function AddAwardDashboardActionButtonController ($scope, $routeParams, $window, canCreateOrEditAwards, isApplicationManagementScreen, CaseTypeCategory) {
    $scope.isVisible = isVisible;
    $scope.redirectToAwardsCreationScreen = redirectToAwardsCreationScreen;

    /**
     * Displays the Add Award button when on the awards dashboard and the user can create awards.
     *
     * @returns {boolean} the visibility of the button.
     */
    function isVisible () {
      return isApplicationManagementScreen() && canCreateOrEditAwards();
    }

    /**
     * Redirects the user to the awards creation screen.
     */
    function redirectToAwardsCreationScreen () {
      var currentCaseTypeCategoryValue = CaseTypeCategory.findByName($routeParams.case_type_category).value;
      var newAwardUrl = getCrmUrl('civicrm/award/a/#/awards/new/' + currentCaseTypeCategoryValue);

      $window.location.href = newAwardUrl;
    }
  }
})(angular, CRM.url);
