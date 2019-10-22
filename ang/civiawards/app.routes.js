(function (angular, $, _) {
  var module = angular.module('civiawards');

  module.config(function ($routeProvider) {
    $routeProvider.when('/awards/new', {
      controller: 'CiviawardsAwardsNew',
      templateUrl: '~/civiawards/awards/AwardsNew.html'
    });
  });
})(angular, CRM.$, CRM._);
