(function (angular, $, _) {
  var module = angular.module('civiawards');

  module.directive('civiaward', function () {
    return {
      scope: {
        awardId: '='
      },
      controller: 'CiviAwardCreateEditAward',
      templateUrl: '~/civiawards/directives/award.directive.html',
      restrict: 'E'
    };
  });

  module.controller('CiviAwardCreateEditAward', function ($scope, $window, crmApi, getSelect2Value, CaseStatus, AwardType, CaseTypeCategory) {
    var ts = CRM.ts('civicase');

    $scope.ts = ts;
    $scope.isNameDisabled = true;
    $scope.submitInProgress = false;
    $scope.awardTypes = [];
    $scope.awardDetailsID = null;
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

      mapAwardTypes();

      $scope.$watch('basicDetails.title', titleWatcher);

      if ($scope.awardId) {
        fetchAwardInformation()
          .then(function (result) {
            setBasicDetails(result.caseType);
            setAdditionalInformation(result.additionalDetails);
          });
      } else {
        enableAllAwardStage();
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
      $scope.basicDetails.title = caseType.title;
      $scope.basicDetails.name = caseType.name;
      $scope.basicDetails.description = caseType.description;
      $scope.basicDetails.isEnabled = caseType.is_active === '1';

      _.each(caseType.definition.statuses, function (stageName) {
        var awargStage = _.find($scope.awardStages, function (stage) {
          return stage.name === stageName;
        });
        $scope.basicDetails.selectedAwardStages[awargStage.value] = true;
      });
    }

    /**
     * Set Additional Details of award
     *
     * @param {object} additionalDetails additional details
     */
    function setAdditionalInformation (additionalDetails) {
      $scope.awardDetailsID = additionalDetails.id;
      $scope.basicDetails.startDate = additionalDetails.start_date;
      $scope.basicDetails.endDate = additionalDetails.end_date;
      $scope.basicDetails.awardType = additionalDetails.award_type;
      $scope.basicDetails.awardManagers = additionalDetails.award_manager.toString();
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
     * Map Award Types to be used in the UI
     */
    function mapAwardTypes () {
      _.each(AwardType.getAll(), function (awardType) {
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

      if ($scope.awardId) {
        params.id = $scope.awardId;
      }

      params.sequential = true;
      params.title = $scope.basicDetails.title;
      params.description = $scope.basicDetails.description;
      params.is_active = $scope.basicDetails.isEnabled;
      params.case_type_category = awardsCaseTypeCategoryValue;
      params.name = $scope.basicDetails.name;
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
        .on('crmFormSuccess', function (e, data) {
          $scope.awardStages[data.optionValue.value] = data.optionValue;
          $scope.basicDetails.selectedAwardStages[data.optionValue.value] = true;
          $scope.$digest();
        });
    }
  });
})(angular, CRM.$, CRM._);
