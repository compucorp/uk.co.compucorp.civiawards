(function (angular, $, _) {
  var module = angular.module('civiawards');

  module.config(function ($routeProvider) {
    $routeProvider.when('/awards/new', {
      template: '<civiaward></civiaward>'
    });
    $routeProvider.when('/awards/:awardId/:focusedTabName?', {
      template: function (params) {
        return '<civiaward award-id="' + params.awardId + '" focused-tab-name="' + params.focusedTabName + '"></civiaward>';
      }
    });
  });
})(angular, CRM.$, CRM._);
