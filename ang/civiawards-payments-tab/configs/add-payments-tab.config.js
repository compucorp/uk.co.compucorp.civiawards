(function (angular) {
  var module = angular.module('civiawards-payments-tab');

  module.config(function (CaseDetailsTabsProvider, tsProvider) {
    var ts = tsProvider.$get();

    CaseDetailsTabsProvider.addTabs([
      {
        name: 'Payments',
        label: ts('Payments'),
        weight: 101
      }
    ]);
  });
})(angular);
