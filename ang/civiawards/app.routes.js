(function (angular, $, _) {
  var module = angular.module('civiawards');

  module.config(function ($routeProvider) {
    $routeProvider.when('/awards/new/:caseTypeCategoryId', {
      template: function (params) {
        return '<civiaward case-type-category-id="' + params.caseTypeCategoryId + '"></civiaward>';
      }
    });
    $routeProvider.when('/awards/:caseTypeCategoryId/:awardId/:focusedTabName?', {
      template: function (params) {
        return '<civiaward award-id="' + params.awardId + '" case-type-category-id="' + params.caseTypeCategoryId + '" focused-tab-name="' + params.focusedTabName + '"></civiaward>';
      }
    });
  });
})(angular, CRM.$, CRM._);
