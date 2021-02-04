(function (angular) {
  var module = angular.module('civiawards');

  module.config(function (CaseDetailsTabsProvider, CaseTypeCategoryProvider,
    currentCaseCategory, instancesFinanceSupport, isTruthyProvider, tsProvider) {
    var isTruthy = isTruthyProvider.$get();
    var ts = tsProvider.$get();
    var caseTypeCategory = CaseTypeCategoryProvider.findByName(currentCaseCategory);
    var supportsFinanceTab = isTruthy(instancesFinanceSupport[caseTypeCategory.value]);

    if (!supportsFinanceTab) {
      return;
    }

    CaseDetailsTabsProvider.addTabs([
      {
        name: 'Finance',
        label: ts('Finance'),
        weight: 101
      }
    ]);
  });
})(angular);
