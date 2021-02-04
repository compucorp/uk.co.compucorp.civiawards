(function (angular) {
  var module = angular.module('civiawards');

  module.directive('civiawardsPaymentsCaseTabContent', function () {
    return {
      templateUrl: '~/civiawards/payments-tab/directives/payments-case-tab-content.directive.html'
    };
  });
})(angular);
