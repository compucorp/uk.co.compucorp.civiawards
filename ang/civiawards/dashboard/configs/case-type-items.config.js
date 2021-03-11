(function (angular, _) {
  var module = angular.module('civiawards');
  var INSTANCE_NAME = 'applicant_management';

  module.config(function (CaseTypeProvider, CaseTypeCategoryProvider, DashboardCaseTypeItemsProvider) {
    var applicationManagementCategories = CaseTypeCategoryProvider.findAllByInstance(INSTANCE_NAME);

    (function () {
      _.forEach(applicationManagementCategories, function (applicationManagementCategory) {
        var caseTypes = CaseTypeProvider.getByCategory(applicationManagementCategory.value);
        _.forEach(caseTypes, addConfigurationButtonsToCaseType);
      });
    })();

    /**
     * Adds edit buttons to the given award case type. The buttons url point
     * towards the awards configuration form.
     *
     * @param {object} awardCaseType award case type data.
     */
    function addConfigurationButtonsToCaseType (awardCaseType) {
      DashboardCaseTypeItemsProvider.addItems(awardCaseType.name, [{
        templateUrl: '~/civiawards/dashboard/directives/edit-award-button.html'
      }]);
    }
  });
})(angular, CRM._);
