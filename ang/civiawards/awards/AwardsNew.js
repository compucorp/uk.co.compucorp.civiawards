(function (angular, $, _) {
  var module = angular.module('civiawards');

  module.controller('CiviawardsAwardsNew', function ($scope, CaseStatus, AwardTypes) {
    var ts = CRM.ts('civicase');

    $scope.ts = ts;
    $scope.title = 'New Award';
    $scope.awardTypes = [];
    $scope.awardStages = CaseStatus.getAll();
    $scope.tabs = [
      { name: 'stages', label: ts('Award Stages') },
      { name: 'additionaltab', label: ts('Additional Tab') }
    ];
    $scope.basicDetails = {
      title: '',
      description: '',
      awardType: null,
      isEnabled: true,
      startDate: null,
      endDate: null,
      awardManagers: [],
      selectedAwardStages: {}
    };

    $scope.createNewAwardStage = createNewAwardStage;
    $scope.saveAward = saveAward;
    $scope.selectTab = selectTab;

    (function init () {
      $scope.activeTab = $scope.tabs[0].name;
      enableAllAwardStage();
      mapAwardTypes();
    }());

    /**
     * Map Award Types to be used in the UI
     */
    function mapAwardTypes () {
      _.each(AwardTypes.getAll(), function (awardType) {
        $scope.awardTypes.push({ id: awardType.value, text: awardType.label });
      });
    }
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
      console.log($scope.basicDetails);
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
