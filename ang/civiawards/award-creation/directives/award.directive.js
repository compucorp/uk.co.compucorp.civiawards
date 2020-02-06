(function (angular, $, _) {
  var module = angular.module('civiawards');

  module.directive('civiaward', function () {
    return {
      scope: {
        awardId: '=',
        focusedTabName: '@'
      },
      controller: 'CiviAwardCreateEditAwardController',
      templateUrl: '~/civiawards/award-creation/directives/award.directive.html',
      restrict: 'E'
    };
  });

  module.controller('CiviAwardCreateEditAwardController', function ($q, $window, $scope, $location, crmApi, getSelect2Value, CaseTypeCategory, crmStatus) {
    var ts = CRM.ts('civicase');

    $scope.ts = ts;
    $scope.pageTitle = 'New Award';
    $scope.isNameDisabled = true;
    $scope.submitInProgress = false;
    $scope.awardTypeSelect2Options = [];
    $scope.awardDetailsID = null;
    $scope.tabs = [
      { name: 'basicDetails', label: ts('Basic Details') },
      { name: 'stages', label: ts('Award Stages') },
      { name: 'reviewFields', label: ts('Review Fields') }
    ];
    $scope.basicDetails = {
      title: '',
      name: '',
      description: '',
      isEnabled: true
    };
    $scope.additionalDetails = {
      awardType: null,
      startDate: null,
      endDate: null,
      awardManagers: [],
      selectedReviewFields: []
    };

    $scope.ifSaveButtonDisabled = ifSaveButtonDisabled;
    $scope.selectTab = selectTab;
    $scope.saveAwardInBG = saveAwardInBG;
    $scope.saveNewAward = saveNewAward;
    $scope.saveAndNavigateToDashboard = saveAndNavigateToDashboard;
    $scope.navigateToDashboard = navigateToDashboard;

    (function init () {
      if ($scope.awardId) {
        $scope.activeTab = getDefaultTabName();
        fetchAwardInformation()
          .then(function (result) {
            $scope.$emit('civiawards::edit-award::details-fetched', result);
          });
      }
    }());

    /**
     * Returns default Tab to be focused on load
     *
     * @returns {string} default tab name to be focused on load
     */
    function getDefaultTabName () {
      if ($scope.focusedTabName === 'undefined') {
        return 'basicDetails';
      } else {
        return $scope.focusedTabName;
      }
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
     * Check if Save button should be disabled
     *
     * @returns {boolean} if Save button should be disabled
     */
    function ifSaveButtonDisabled () {
      return !$scope.basic_details_form.$valid || $scope.submitInProgress;
    }

    /**
     * Generate Award `name` from the sent text
     *
     * @param {string} text string from which name would be generated
     * @returns {string} name of the award
     */
    function generateAwardName (text) {
      return text
        .replace(/ /g, '_')
        .replace(/[^a-zA-Z0-9_]/g, '')
        .toLowerCase();
    }

    /**
     * Saves a New Award
     */
    function saveNewAward () {
      saveAward()
        .then(navigateToAwardEditPage)
        .then(showSucessNotification);
    }

    /**
     * Saves a New Award in the Background
     */
    function saveAwardInBG () {
      saveAward()
        .then(showSucessNotification);
    }

    /**
     * Saves an existing Award and navigates to dashboard
     */
    function saveAndNavigateToDashboard () {
      saveAward()
        .then(navigateToDashboard)
        .then(showSucessNotification);
    }

    /**
     * Show notification after award is saved successfully
     */
    function showSucessNotification () {
      CRM.alert('Award Successfully Saved.', ts('Saved'), 'success');
    }

    /**
     * Save Award
     *
     * @returns {Promise} promise
     */
    function saveAward () {
      $scope.submitInProgress = true;

      var promise = saveCaseTypeBasicDetails()
        .then(saveAdditionAwardDetails)
        .then(function (award) {
          return award.case_type_id;
        })
        .catch(function (error) {
          var errorMesssage = error.error_code === 'already exists'
            ? ts('This title is already in use. Please choose another')
            : error.error_message;

          CRM.alert(errorMesssage, ts('Error'), 'error');

          return $q.reject();
        })
        .finally(function () {
          $scope.submitInProgress = false;
        });

      return crmStatus({
        start: $scope.ts('Saving Award...'),
        success: $scope.ts('Saved')
      }, promise);
    }

    /**
     * Navigate to the Dashboard Page
     */
    function navigateToDashboard () {
      $window.location.href = '/civicrm/case/a/?case_type_category=awards#/case?case_type_category=awards';
    }

    /**
     * Navigate to edit the given award
     *
     * @param {string/number} awardID id of the award
     */
    function navigateToAwardEditPage (awardID) {
      $location.path('/awards/' + awardID + '/stages');
    }

    /**
     * Save Basic Award Details using Case Type API
     *
     * @returns {Promise} promise
     */
    function saveCaseTypeBasicDetails () {
      var params = {
        sequential: true,
        title: $scope.basicDetails.title,
        description: $scope.basicDetails.description,
        is_active: $scope.basicDetails.isEnabled,
        case_type_category: CaseTypeCategory.findByName('awards').value,
        name: generateAwardName($scope.basicDetails.title)
      };

      prepareAwardStagesWhenEditings(params);

      if ($scope.awardId) {
        params.id = $scope.awardId;
      }

      return crmApi('CaseType', 'create', params).then(function (caseTypeData) {
        return caseTypeData.values[0];
      });
    }

    /**
     * Prepares the Review Fields paramaters when editing an award
     *
     * @returns {object[]} list of selected review field ids
     */
    function prepareReviewFields () {
      return _.map($scope.additionalDetails.selectedReviewFields, function (reviewField) {
        return {
          id: reviewField.id,
          required: reviewField.required ? '1' : '0',
          weight: reviewField.weight
        };
      });
    }

    /**
     * Prepare the Award Stages paramaters when editing an award
     *
     * @param {object} params parameters
     */
    function prepareAwardStagesWhenEditings (params) {
      if (!$scope.awardId) {
        return;
      }

      var selectedAwardStageNames = _.chain($scope.awardStages)
        .filter(function (stage) {
          return $scope.basicDetails.selectedAwardStages[stage.value];
        })
        .map(function (stage) { return stage.name; })
        .value();
      var areAllAwardsSelected = selectedAwardStageNames.length === _.size($scope.awardStages);

      params.definition = {
        statuses: areAllAwardsSelected ? [] : selectedAwardStageNames
      };
    }

    /**
     * Save Additional Award Details, which are saved using AwardDetail entity
     *
     * @param {object} award award object
     * @returns {Promise} promise
     */
    function saveAdditionAwardDetails (award) {
      var params = {
        sequential: true,
        award_manager: getSelect2Value($scope.additionalDetails.awardManagers),
        case_type_id: award.id,
        start_date: $scope.additionalDetails.startDate,
        end_date: $scope.additionalDetails.endDate,
        award_type: $scope.additionalDetails.awardType,
        review_fields: prepareReviewFields()
      };

      if ($scope.awardDetailsID) {
        params.id = $scope.awardDetailsID;
      }

      return crmApi('AwardDetail', 'create', params)
        .then(function (awardData) {
          return awardData.values[0];
        });
    }
  });
})(angular, CRM.$, CRM._);
