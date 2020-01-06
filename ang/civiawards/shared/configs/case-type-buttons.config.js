(function (angular, _, checkPermission, url) {
  var module = angular.module('civiawards');
  var AWARDS_CATEGORY_NAME = 'awards';
  var AWARD_CONFIG_URL = 'civicrm/a/#/awards/';
  var EDIT_AWARD_PERMISSION = 'create/edit awards';

  module.config(function (CaseTypeProvider, CaseTypeCategoryProvider, DashboardCaseTypeButtonsProvider) {
    var awardCategory = CaseTypeCategoryProvider.findByName(AWARDS_CATEGORY_NAME);
    var awardCaseTypes = CaseTypeProvider.getByCategory(awardCategory.value);
    var canEditAwards = checkPermission(EDIT_AWARD_PERMISSION);

    (function () {
      if (!canEditAwards) {
        return;
      }

      _.forEach(awardCaseTypes, addConfigurationButtonsToCaseType);
    })();

    /**
     * Adds configuration buttons to the given case type. The buttons url point
     * towards the awards configuration form.
     *
     * @param {object} caseType case type data.
     */
    function addConfigurationButtonsToCaseType (caseType) {
      var caseTypeConfigUrl = url(AWARD_CONFIG_URL + caseType.id);

      DashboardCaseTypeButtonsProvider.addButtons(caseType.name, [{
        icon: 'fa fa-cog',
        url: caseTypeConfigUrl
      }]);
    }
  });
})(angular, CRM._, CRM.checkPerm, CRM.url);
