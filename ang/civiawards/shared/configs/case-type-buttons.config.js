(function (angular, _, url) {
  var module = angular.module('civiawards');
  var AWARDS_CATEGORY_NAME = 'awards';
  var AWARD_CONFIG_URL = 'civicrm/a/#/awards/';

  module.config(function (DashboardCaseTypeButtonsProvider) {
    var awardCategory = getAwardCategory();
    var awardCaseTypes = getCaseTypesForCategory(awardCategory.value);

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
          url: caseTypeConfigUrl
        }]);
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

    /**
     * Returns a list of case types that belong to the given category.
     *
     * @param {number} categoryValue the case type category value.
     * @returns {object[]} a list of case types.
     */
    function getCaseTypesForCategory (categoryValue) {
      return _.filter(CRM['civicase-base'].caseTypes, function (caseType) {
        return caseType.case_type_category === categoryValue;
      });
    }
  });
})(angular, CRM._, CRM.url);
