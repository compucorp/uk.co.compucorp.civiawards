(function (angular) {
  var module = angular.module('civiawards-payments-tab');

  module.directive('civiawardsPaymentFilters', function () {
    return {
      templateUrl: '~/civiawards-payments-tab/directives/payment-filters.directive.html',
      restrict: 'E',
      scope: {
        onFilter: '&'
      }
    };
  });
})(angular);
