(function (angular, $, _) {
  var module = angular.module('civiawards');

  module.directive('civiaward', function () {
    return {
      controller: 'CiviAwardCreateEditAward',
      templateUrl: '~/civiawards/directives/award.directive.html',
      restrict: 'E'
    };
  });

  module.controller('CiviAwardCreateEditAward', function ($scope, $window, crmApi, getSelect2Value, CaseStatus, AwardTypes, CaseTypeCategory) {
    var ts = CRM.ts('civicase');

    $scope.ts = ts;
    $scope.submitInProgress = false;
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
        $scope.awardTypes.push({ id: awardType.value, text: awardType.label, name: awardType.name });
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
      $scope.submitInProgress = true;
      saveCaseTypeBasicDetails()
        .then(function (data) {
          return saveAdditionAwardDetails(data.values[0]);
        })
        .then(function () {
          $window.location.href = '/civicrm/case/a/?case_type_category=awards#/case?case_type_category=awards';
        })
        .catch(function (error) {
          CRM.alert(error.error_message, ts('Error'), 'error');
        })
        .finally(function () {
          $scope.submitInProgress = false;
        });
    }

    /**
     * Save Basic Award Details using Case Type API
     *
     * @returns {Promise} promise
     */
    function saveCaseTypeBasicDetails () {
      var params = {};
      var selectedAwardStages = [];

      var awardsCaseTypeCategoryValue = _.find(CaseTypeCategory.getAll(), function (category) {
        return category.name === 'awards';
      }).value;

      _.each($scope.basicDetails.selectedAwardStages, function (value, key) {
        if (value) {
          selectedAwardStages.push($scope.awardStages[key].name);
        }
      });

      params.sequential = true;
      params.title = $scope.basicDetails.title;
      params.description = $scope.basicDetails.description;
      params.is_active = $scope.basicDetails.isEnabled;
      params.case_type_category = awardsCaseTypeCategoryValue;
      params.name = params.title.replace(/ /g, '_').replace(/[^a-zA-Z0-9_]/g, '').toLowerCase();
      params.definition = {};
      params.definition.statuses = selectedAwardStages.length === _.size($scope.awardStages) ? [] : selectedAwardStages;

      return crmApi('CaseType', 'create', params);
    }

    /**
     * Save Additional Award Details, which are saved using AwardDetail entity
     *
     * @param {object} award award object
     * @returns {Promise} promise
     */
    function saveAdditionAwardDetails (award) {
      var additionalAwardDetails = {
        award_manager: getSelect2Value($scope.basicDetails.awardManagers),
        case_type_id: award.id,
        start_date: $scope.basicDetails.startDate,
        end_date: $scope.basicDetails.endDate,
        award_type: $scope.basicDetails.awardType
      };

      return crmApi('AwardDetail', 'create', additionalAwardDetails);
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
