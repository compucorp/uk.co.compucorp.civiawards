(function (angular) {
  var module = angular.module('civiawards');
  var INSTANCE_NAME = 'applicant_management';

  module.config(function ($windowProvider, CaseDetailsTabsProvider, CaseTypeCategoryProvider, tsProvider) {
    var $window = $windowProvider.$get();
    var ts = tsProvider.$get();
    var caseTypeCategory = getCaseTypeCategory();
    var reviewsTab = {
      name: 'Reviews',
      label: ts('Reviews'),
      weight: 100
    };

    if (caseTypeCategory &&
      CaseTypeCategoryProvider.isInstance(caseTypeCategory, INSTANCE_NAME)) {
      CaseDetailsTabsProvider.addTabs([
        reviewsTab
      ]);
    }

    /**
     * Returns the current case type category parameter. This is used instead of
     * the $location service because the later is not available at configuration
     * time.
     *
     * @returns {string|null} the name of the case type category, or null.
     */
    function getCaseTypeCategory () {
      var urlParamRegExp = /case_type_category=([^&]+)/i;
      var currentSearch = decodeURIComponent($window.location.search);
      var results = urlParamRegExp.exec(currentSearch);

      return results && results[1];
    }
  });
})(angular);
