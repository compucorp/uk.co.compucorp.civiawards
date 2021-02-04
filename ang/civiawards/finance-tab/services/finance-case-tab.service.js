(function (angular) {
  var module = angular.module('civiawards');

  module.service('FinanceCaseTab', FinanceCaseTab);

  /**
   * Finance Case Tab service.
   */
  function FinanceCaseTab () {
    this.activeTabContentUrl = function () {
      return '~/civiawards/finance-tab/services/finance-case-tab-content.html';
    };
  }
})(angular);
