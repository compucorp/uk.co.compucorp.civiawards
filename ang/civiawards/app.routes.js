(function (angular, $, _) {
  var module = angular.module('civiawards');

  module.config(function ($routeProvider) {
    $routeProvider.when('/awards/new', {
      template: '<civiaward></civiaward>'
    });
    $routeProvider.when('/awards/:awardId', {
      template: function (params) {
        return '<civiaward award-id="' + params.awardId + '"></civiaward>';
      }
    });
  });
})(angular, CRM.$, CRM._);
