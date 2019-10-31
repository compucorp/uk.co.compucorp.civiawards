(function (angular, $, _) {
  var module = angular.module('civiawards');

  module.directive('civiaward', function () {
    return {
      scope: {
        awardId: '='
      },
      controller: 'CiviAwardCreateEditAwardController',
      templateUrl: '~/civiawards/directives/award.directive.html',
      restrict: 'E'
    };
  });

  module.controller('CiviAwardCreateEditAwardController', function ($scope, $window, crmApi, getSelect2Value, CaseStatus, AwardType, CaseTypeCategory) {
    var ts = CRM.ts('civicase');

    $scope.ts = ts;
    $scope.pageTitle = 'New Award';
    $scope.isNameDisabled = true;
    $scope.submitInProgress = false;
    $scope.awardTypeSelect2Options = [];
    $scope.awardDetailsID = null;
    $scope.awardStages = CaseStatus.getAll();
    $scope.tabs = [
      { name: 'stages', label: ts('Award Stages') },
      { name: 'additionaltab', label: ts('Additional Tab') }
    ];
    $scope.basicDetails = {
      title: '',
      name: '',
      description: '',
      isEnabled: true,
      selectedAwardStages: {}
    };
    $scope.additionalDetails = {
      awardType: null,
      startDate: null,
      endDate: null,
      awardManagers: []
    };

    $scope.createNewAwardStage = createNewAwardStage;
    $scope.saveAward = saveAward;
    $scope.selectTab = selectTab;

    (function init () {
      $scope.activeTab = $scope.tabs[0].name;
      $scope.awardTypeSelect2Options = getAwardTypeSelect2Options();

      $scope.$watch('basicDetails.title', titleWatcher);

      if ($scope.awardId) {
        fetchAwardInformation()
          .then(function (result) {
            setBasicDetails(result.caseType);
            setAdditionalInformation(result.additionalDetails);
          });
      } else {
        enableAllAwardStages();
      }
    }());

    /**
     * Watcher for Title form field
     */
    function titleWatcher () {
      if (!$scope.awardId && $scope.basic_details_form.awardName.$pristine) {
        $scope.basicDetails.name = $scope.basicDetails.title.replace(/ /g, '_').replace(/[^a-zA-Z0-9_]/g, '').toLowerCase();
      }
    }

    /**
     * Set basic details
     *
     * @param {object} caseType case type
     */
    function setBasicDetails (caseType) {
      $scope.pageTitle = caseType.title;
      $scope.basicDetails.title = caseType.title;
      $scope.basicDetails.name = caseType.name;
      $scope.basicDetails.description = caseType.description;
      $scope.basicDetails.isEnabled = caseType.is_active === '1';
      $scope.basicDetails.selectedAwardStages = getSelectedAwardStages(caseType.definition.statuses);
    }

    /**
     * Get Selected Award stages
     *
     * @param {Array} awardStages award stages fetched from api
     * @returns {object} selected award stages
     */
    function getSelectedAwardStages (awardStages) {
      var selectedAwardStages = {};

      _.each(awardStages, function (stageName) {
        var awardStage = _.find($scope.awardStages, function (stage) {
          return stage.name === stageName;
        });
        selectedAwardStages[awardStage.value] = true;
      });

      return selectedAwardStages;
    }

    /**
     * Set Additional Details of award
     *
     * @param {object} additionalDetails additional details
     */
    function setAdditionalInformation (additionalDetails) {
      $scope.awardDetailsID = additionalDetails.id;
      $scope.additionalDetails.startDate = additionalDetails.start_date;
      $scope.additionalDetails.endDate = additionalDetails.end_date;
      $scope.additionalDetails.awardType = additionalDetails.award_type;
      $scope.additionalDetails.awardManagers = additionalDetails.award_manager.join();
    }

    /**
     * Fetch Exisiting Awards Information
     *
     * @returns {Promise} promise
     */
    function fetchAwardInformation () {
      return crmApi({
        caseType: ['CaseType', 'getsingle', { sequential: true, id: $scope.awardId }],
        additionalDetails: ['AwardDetail', 'getsingle', { sequential: true, case_type_id: $scope.awardId }]
      });
    }

    /**
     * Returns Award Types to be used in the UI
     *
     * @returns {Array} award types array in a format suitable for select 2
     */
    function getAwardTypeSelect2Options () {
      return _.map(AwardType.getAll(), function (awardType) {
        return { id: awardType.value, text: awardType.label, name: awardType.name };
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
    function enableAllAwardStages () {
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
        .then(saveAdditionAwardDetails)
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
      var selectedAwardStageNames = _.chain($scope.awardStages)
        .filter(function (stage) {
          return $scope.basicDetails.selectedAwardStages[stage.value];
        })
        .map(function (stage) { return stage.name; })
        .value();
      var areAllAwardsSelected = selectedAwardStageNames.length === _.size($scope.awardStages);
      var params = {
        sequential: true,
        title: $scope.basicDetails.title,
        description: $scope.basicDetails.description,
        is_active: $scope.basicDetails.isEnabled,
        case_type_category: CaseTypeCategory.findByName('awards').value,
        name: $scope.basicDetails.name,
        definition: {
          statuses: areAllAwardsSelected ? [] : selectedAwardStageNames
        }
      };

      if ($scope.awardId) {
        params.id = $scope.awardId;
      }

      return crmApi('CaseType', 'create', params).then(function (caseTypeData) {
        return caseTypeData.values[0];
      });
    }

    /**
     * Save Additional Award Details, which are saved using AwardDetail entity
     *
     * @param {object} award award object
     * @returns {Promise} promise
     */
    function saveAdditionAwardDetails (award) {
      var additionalAwardDetails = {
        award_manager: getSelect2Value($scope.additionalDetails.awardManagers),
        case_type_id: award.id,
        start_date: $scope.additionalDetails.startDate,
        end_date: $scope.additionalDetails.endDate,
        award_type: $scope.additionalDetails.awardType
      };

      if ($scope.awardDetailsID) {
        additionalAwardDetails.id = $scope.awardDetailsID;
      }

      return crmApi('AwardDetail', 'create', additionalAwardDetails);
    }

    /**
     * Create a New Award Stage
     */
    function createNewAwardStage () {
      CRM.loadForm(CRM.url('civicrm/admin/options/case_status',
        { action: 'add', reset: 1 }))
        .on('crmFormSuccess', function (event, data) {
          $scope.awardStages[data.optionValue.value] = data.optionValue;
          $scope.basicDetails.selectedAwardStages[data.optionValue.value] = true;
          $scope.$digest();
        });
    }
  });
})(angular, CRM.$, CRM._);
