(function (angular, _, url) {
  var module = angular.module('civiawards');
  var AWARDS_CATEGORY_NAME = 'awards';
  var AWARD_CONFIG_URL = 'civicrm/a/#/awards/';

  module.config(function (CaseTypeProvider, CaseTypeCategoryProvider, DashboardCaseTypeButtonsProvider) {
    var allCaseTypes = CaseTypeProvider.getAll();
    var awardCategory = CaseTypeCategoryProvider.findByName(AWARDS_CATEGORY_NAME);
    var awardCaseTypes = filterCaseTypesByCategory(allCaseTypes, awardCategory.value);

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

    /**
     * Filters the given case types and returns the ones belonging to the given category.
     *
     * @param {object[]} caseTypes the list of case types.
     * @param {number} categoryValue the case type category value.
     * @returns {object[]} a list of case types.
     */
    function filterCaseTypesByCategory (caseTypes, categoryValue) {
      return _.filter(caseTypes, function (caseType) {
        return caseType.case_type_category === categoryValue;
      });
    }
  });
})(angular, CRM._, CRM.url);
