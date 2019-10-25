(function (angular, $, _) {
  var module = angular.module('civiawards');

  module.controller('CiviawardsAwardsNew', function ($scope) {
    var ts = CRM.ts('civicase');

    $scope.ts = ts;
    $scope.title = 'New Award';
    $scope.activityTypes = [
      { id: '1', text: ts('Activity Type 1') },
      { id: '2', text: ts('Activity Type 2') }
    ];
    $scope.awardManagers = [
      { id: '1', text: ts('Award Manager 1') },
      { id: '2', text: ts('Award Manager 2') }
    ];
    $scope.tabs = [
      { name: 'stages', label: ts('Award Stages') },
      { name: 'additionaltab', label: ts('Additional Tab') }
    ];

    $scope.activeTab = $scope.tabs[0].name;
    $scope.selectTab = selectTab;

    /**
     * Selects a tab as active
     *
     * @param {string} tab tab name
     */
    function selectTab (tab) {
      $scope.activeTab = tab;
    }
  });
})(angular, CRM.$, CRM._);
