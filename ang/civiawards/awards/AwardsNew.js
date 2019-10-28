(function (angular, $, _) {
  var module = angular.module('civiawards');

  module.controller('CiviawardsAwardsNew', function ($scope, CaseStatus) {
    var ts = CRM.ts('civicase');

    $scope.ts = ts;
    $scope.title = 'New Award';
    $scope.activityTypes = [
      { id: '1', text: ts('Activity Type 1') },
      { id: '2', text: ts('Activity Type 2') }
    ];
    $scope.tabs = [
      { name: 'stages', label: ts('Award Stages') },
      { name: 'additionaltab', label: ts('Additional Tab') }
    ];
    $scope.basicDetails = {
      title: '',
      description: '',
      awardType: null,
      isEnabled: false,
      startDate: null,
      endDate: null,
      awardManagers: [],
      selectedAwardStages: {}
    };

    $scope.activeTab = $scope.tabs[0].name;
    $scope.awardStages = CaseStatus.getAll();

    $scope.createNewAwardStage = createNewAwardStage;
    $scope.saveAward = saveAward;
    $scope.selectTab = selectTab;

    (function init () {
      enableAllAwardStage();
    }());
    /**
     * Selects a tab as active
     *
     * @param {string} tab tab name
     */
    function selectTab (tab) {
      $scope.activeTab = tab;
    }

    /**
     * Enable All Award Stages
     */
    function enableAllAwardStage () {
      _.each($scope.awardStages, function (awardStage) {
        $scope.basicDetails.selectedAwardStages[awardStage.value] = true;
      });
    }
    /**
     * Save Award
     */
    function saveAward () {
      console.log($scope.basicDetails.selectedAwardStages);
    }

    /**
     * Create a New Award Stage
     */
    function createNewAwardStage () {
      CRM.loadForm(CRM.url('civicrm/admin/options/case_status',
        { action: 'add', reset: 1 }))
        .on('crmFormSuccess', function (e, data) {
          $scope.awardStages[data.optionValue.value] = data.optionValue;
          $scope.basicDetails.selectedAwardStages[data.optionValue.value] = true;
          $scope.$digest();
        });
    }
  });
})(angular, CRM.$, CRM._);
