(function (angular, $, _) {
  var module = angular.module('civiawards');

  module.config(function ($routeProvider) {
    $routeProvider.when('/awards/new', {
      template: '<civiaward></civiaward>'
    });
  });
})(angular, CRM.$, CRM._);
