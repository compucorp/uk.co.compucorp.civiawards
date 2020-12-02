(function (angular) {
  var module = angular.module('civiawards-base');

  module.factory('isApplicationManagementScreen', isApplicationManagementScreenFactory);

  /**
   * Is Application Management Screen factory.
   *
   * @param {object} $location the location service.
   * @param {object} CaseTypeCategory case type category service.
   * @returns {Function} The is application management screen function.
   */
  function isApplicationManagementScreenFactory ($location, CaseTypeCategory) {
    return isApplicationManagementScreen;

    /**
     * Determines if the current screen belongs to a case type category
     * from applicant management.
     *
     * @returns {boolean} true when case type category url param is from applicant management
     */
    function isApplicationManagementScreen () {
      var urlParams = $location.search();

      return urlParams.case_type_category &&
        CaseTypeCategory.isInstance(urlParams.case_type_category, 'applicant_management');
    }
  }
})(angular);
