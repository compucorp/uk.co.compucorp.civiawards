(function (angular, ts) {
  var module = angular.module('civiawards-workflow');

  module.config(function (WorkflowListActionItemsProvider) {
    var actionItems = [
      {
        templateUrl: '~/civiawards-workflow/action-links/directives/workflow-list-advanced-action.html',
        onlyVisibleForInstance: 'applicant_management',
        weight: 5
      }
    ];

    WorkflowListActionItemsProvider.addItems(actionItems);
  });
})(angular, CRM.ts('civicase'));
