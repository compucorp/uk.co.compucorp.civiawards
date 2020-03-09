(function (angular, _, url) {
  var module = angular.module('civiawards');
  var AWARDS_CATEGORY_NAME = 'awards';

  module.config(function (CaseTypeProvider, CaseTypeCategoryProvider, DashboardCaseTypeItemsProvider) {
    var awardCategory = CaseTypeCategoryProvider.findByName(AWARDS_CATEGORY_NAME);
    var awardCaseTypes = CaseTypeProvider.getByCategory(awardCategory.value);

    (function () {
      _.forEach(awardCaseTypes, addConfigurationButtonsToCaseType);
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
})(angular, CRM._, CRM.url);
