(function (angular, _, url) {
  var module = angular.module('civiawards');
  var AWARDS_CATEGORY_NAME = 'awards';
  var AWARD_CONFIG_URL = 'civicrm/a/#/awards/';

  module.config(function (CaseTypeProvider, DashboardCaseTypeButtonsProvider) {
    var allCaseTypes = CaseTypeProvider.getAll();
    var awardCategory = getAwardCategory();
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

    /**
     * Returns the award case type category.
     *
     * @returns {object} the case type category.
     */
    function getAwardCategory () {
      return _.find(CRM['civicase-base'].caseTypeCategories, function (category) {
        return category.name === AWARDS_CATEGORY_NAME;
      });
    }
  });
})(angular, CRM._, CRM.url);
