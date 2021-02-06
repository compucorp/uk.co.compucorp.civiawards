(function (angular) {
  var module = angular.module('civiawards-payments-tab');

  module.directive('civiawardsPaymentsCaseTabContent', function () {
    return {
      templateUrl: '~/civiawards-payments-tab/directives/payments-case-tab-content.directive.html',
      restrict: 'E',
      scope: {
        caseItem: '<'
      }
    };
  });
})(angular);
