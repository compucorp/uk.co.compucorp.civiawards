(function ($, _, angular) {
  var module = angular.module('civiawards-workflow');

  module.controller('WorkflowAdvancedController', WorkflowAdvancedController);

  /**
   * @param {object} $scope scope object
   * @param {object} $window browsers window object
   * @param {Function} civicaseCrmUrl civicrm url service
   */
  function WorkflowAdvancedController ($scope, $window, civicaseCrmUrl) {
    $scope.clickHandler = clickHandler;

    /**
     * Redirects to the CiviCRM core edit workflow page
     *
     * @param {object} workflow workflow object
     */
    function clickHandler (workflow) {
      var url = civicaseCrmUrl(
        'civicrm/a/#/caseType/' + workflow.id
      );

      $window.location.href = url;
    }
  }
})(CRM.$, CRM._, angular);
