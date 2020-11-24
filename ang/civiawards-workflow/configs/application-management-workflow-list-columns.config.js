(function (angular, ts) {
  var module = angular.module('civiawards-workflow');

  module.config(function (WorkflowListColumnsProvider) {
    var actionItems = [
      {
        label: ts('Subtype'),
        templateUrl: '~/civiawards-workflow/columns/directives/workflow-list-column-subtype.html',
        onlyVisibleForInstance: 'applicant_management',
        weight: 3
      }, {
        label: ts('Is Template?'),
        templateUrl: '~/civiawards-workflow/columns/directives/workflow-list-column-is-template.html',
        onlyVisibleForInstance: 'applicant_management',
        weight: 4
      }, {
        label: ts('Manager'),
        templateUrl: '~/civiawards-workflow/columns/directives/workflow-list-column-manager.html',
        onlyVisibleForInstance: 'applicant_management',
        weight: 5
      }
    ];

    WorkflowListColumnsProvider.addItems(actionItems);
  });
})(angular, CRM.ts('civicase'));
