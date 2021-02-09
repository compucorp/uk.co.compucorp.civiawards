(function (_, angular) {
  var module = angular.module('civiawards-payments-tab');

  module.directive('civiawardsPaymentFilters', function () {
    return {
      controller: 'civiawardsPaymentFiltersController',
      templateUrl: '~/civiawards-payments-tab/directives/payment-filters.directive.html',
      restrict: 'E',
      scope: {
        onFilter: '&'
      }
    };
  });

  module.controller('civiawardsPaymentFiltersController', paymentFiltersController);

  /**
   * Payment Filters Controller.
   *
   * @param {object} $scope Scope object reference.
   * @param {object} paymentTypes Payment Types map.
   * @param {object} Select2Utils Select2 Utils service.
   */
  function paymentFiltersController ($scope, paymentTypes, Select2Utils) {
    $scope.paymentTypeOptions = [];

    (function init () {
      $scope.paymentTypeOptions = _.map(paymentTypes, Select2Utils.mapSelectOptions);
    })();
  }
})(CRM._, angular);
