(function (angular, ts) {
  var module = angular.module('civiawards-workflow');

  module.config(function (WorkflowListFiltersProvider) {
    var actionItems = [
      {
        filterIdentifier: 'managed_by',
        defaultValue: '',
        onlyVisibleForInstance: 'applicant_management',
        templateUrl: '~/civiawards-workflow/filters/directives/workflow-list-filter-manager.html'
      }, {
        filterSubObject: 'award_detail_params',
        filterIdentifier: 'award_subtype',
        defaultValue: '',
        onlyVisibleForInstance: 'applicant_management',
        templateUrl: '~/civiawards-workflow/filters/directives/workflow-list-filter-subtype.html'
      }, {
        filterIdentifier: 'is_active',
        defaultValue: true,
        onlyVisibleForInstance: 'applicant_management',
        templateUrl: '~/civiawards-workflow/filters/directives/workflow-list-filter-is-active.html'
      }, {
        filterSubObject: 'award_detail_params',
        filterIdentifier: 'is_template',
        defaultValue: false,
        onlyVisibleForInstance: 'applicant_management',
        templateUrl: '~/civiawards-workflow/filters/directives/workflow-list-filter-is-template.html'
      }
    ];

    WorkflowListFiltersProvider.addItems(actionItems);
  });
})(angular, CRM.ts('civicase'));
