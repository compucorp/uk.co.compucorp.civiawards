(function (angular, _) {
  var module = angular.module('civiawards');

  module.controller('DashboardActionSeparatorController', DashboardActionSeparatorController);

  /**
   * Handles the visibility and click event for the "Add Award" dashboard action button.
   *
   * @param {object} $scope scope object
   * @param {object} $location the location service
   */
  function DashboardActionSeparatorController ($scope, $location) {
    $scope.isVisible = isVisible;

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
})(angular, CRM._);
