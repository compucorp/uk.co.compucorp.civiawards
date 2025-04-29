(function (angular) {
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
   * @param {object} civicaseCrmUrl civicrm url service
   * @param {string} currentCaseCategory The current case type category name
   */
  function AddAwardDashboardActionButtonController ($scope, $routeParams,
    $window, canCreateOrEditAwards, isApplicationManagementScreen,
    CaseTypeCategory, civicaseCrmUrl, currentCaseCategory) {
    $scope.isVisible = isVisible;
    $scope.redirectToAwardsCreationScreen = redirectToAwardsCreationScreen;
    $scope.currentCaseCategory = CaseTypeCategory.findById(currentCaseCategory);

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
      var newAwardUrl = civicaseCrmUrl(
        'civicrm/award/a/#/awards/new/' + $routeParams.case_type_category + '/dashboard'
      );

      $window.location.href = newAwardUrl;
    }
  }
})(angular);
