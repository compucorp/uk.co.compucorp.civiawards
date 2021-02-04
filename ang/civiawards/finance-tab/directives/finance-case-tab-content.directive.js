(function (angular) {
  var module = angular.module('civiawards');

  module.directive('civiawardsFinanceCaseTabContent', function () {
    return {
      templateUrl: '~/civiawards/finance-tab/directives/finance-case-tab-content.directive.html'
    };
  });
})(angular);
