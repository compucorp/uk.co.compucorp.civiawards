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
   * @param {object} ActivityStatus Activity Status service.
   * @param {object} paymentTypes Payment Types map.
   * @param {object} Select2Utils Select2 Utils service.
   */
  function paymentFiltersController ($scope, ActivityStatus, paymentTypes, Select2Utils) {
    $scope.filters = {};
    $scope.paymentStatusOptions = [];
    $scope.paymentTypeOptions = [];
    $scope.clearFilters = clearFilters;

    (function init () {
      $scope.paymentStatusOptions = _.map(getPaymentStatuses(), Select2Utils.mapSelectOptions);
      $scope.paymentTypeOptions = _.map(paymentTypes, Select2Utils.mapSelectOptions);
    })();

    /**
     * Resets the filter object.
     */
    function clearFilters () {
      $scope.filters = {};
    }

    /**
     * @returns {object[]} Activity status related to payments.
     */
    function getPaymentStatuses () {
      return _.filter(ActivityStatus.getAll(), {
        grouping: 'Awards Payments'
      });
    }
  }
})(CRM._, angular);
