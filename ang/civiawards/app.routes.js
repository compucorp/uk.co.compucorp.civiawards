(function (angular, $, _) {
  var module = angular.module('civiawards');

  module.config(function ($routeProvider) {
    $routeProvider.when('/awards/new/:caseTypeCategoryId/:redirectTo', {
      template: function (params) {
        return '<civiaward case-type-category-id="' + params.caseTypeCategoryId + '" redirect-to="' + params.redirectTo + '"></civiaward>';
      }
    });
    $routeProvider.when('/awards/:caseTypeCategoryId/:awardId/:redirectTo/:focusedTabName?', {
      template: function (params) {
        return '<civiaward award-id="' + params.awardId + '" case-type-category-id="' + params.caseTypeCategoryId + '" focused-tab-name="' + params.focusedTabName + '" redirect-to="' + params.redirectTo + '"></civiaward>';
      }
    });
  });
})(angular, CRM.$, CRM._);
