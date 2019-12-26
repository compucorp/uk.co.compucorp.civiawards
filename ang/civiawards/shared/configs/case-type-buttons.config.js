(function (angular, _, url) {
  var module = angular.module('civiawards');
  var AWARDS_CATEGORY_NAME = 'awards';
  var AWARD_CONFIG_URL = 'civicrm/a/#/awards/';

  module.config(function (CaseTypeProvider, CaseTypeCategoryProvider, DashboardCaseTypeButtonsProvider) {
    var awardCategory = CaseTypeCategoryProvider.findByName(AWARDS_CATEGORY_NAME);
    var awardCaseTypes = CaseTypeProvider.getByCategory(awardCategory.value);

    addConfigurationButtonsToCaseTypes(awardCaseTypes);

    /**
     * Adds configuration buttons to the given case types. The buttons url point
     * towards the awards configuration form.
     *
     * @param {object[]} caseTypes a list of case types objects.
     */
    function addConfigurationButtonsToCaseTypes (caseTypes) {
      _.forEach(caseTypes, function (caseType) {
        var caseTypeConfigUrl = url(AWARD_CONFIG_URL + caseType.id);

        DashboardCaseTypeButtonsProvider.addButtons(caseType.name, [{
          icon: 'fa fa-cog',
          url: caseTypeConfigUrl
        }]);
      });
    }
  });
})(angular, CRM._, CRM.url);
