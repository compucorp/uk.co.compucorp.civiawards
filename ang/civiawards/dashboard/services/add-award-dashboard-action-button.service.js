(function (angular, getCrmUrl) {
  var module = angular.module('civiawards');

  module.service('AddAwardDashboardActionButton', AddAwardDashboardActionButton);

  /**
   * Handles the visibility and click event for the "Add Award" dashboard action button.
   *
   * @param {object} $location the location service.
   * @param {object} $window the window object reference.
   */
  function AddAwardDashboardActionButton ($location, $window) {
    this.clickHandler = clickHandler;
    this.isVisible = isVisible;

    /**
     * Redirects the user to the awards creation screen.
     */
    function clickHandler () {
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
