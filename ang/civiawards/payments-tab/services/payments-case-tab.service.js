(function (angular) {
  var module = angular.module('civiawards');

  module.service('PaymentsCaseTab', PaymentsCaseTab);

  /**
   * Payments Case Tab service.
   */
  function PaymentsCaseTab () {
    this.activeTabContentUrl = function () {
      return '~/civiawards/payments-tab/services/payments-case-tab-content.html';
    };
  }
})(angular);
