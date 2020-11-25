(function (angular, $, _) {
  var module = angular.module('civiawards');

  module.directive('civiaward', function () {
    return {
      scope: {
        awardId: '=',
        caseTypeCategoryId: '=',
        focusedTabName: '@'
      },
      controller: 'CiviAwardCreateEditAwardController',
      templateUrl: '~/civiawards/award-creation/directives/award.directive.html',
      restrict: 'E'
    };
  });

  module.controller('CiviAwardCreateEditAwardController', function (
    $location, $q, $scope, $window, CaseTypeCategory, CaseStatus, crmApi, crmStatus,
    getSelect2Value, ts) {
    var DEFAULT_ACTIVITY_TYPES = [
      { name: 'Applicant Review' },
      { name: 'Email' },
      { name: 'Follow up' },
      { name: 'Meeting' },
      { name: 'Phone Call' }
    ];

    $scope.applicationStatusOptions = [];
    $scope.pageTitle = 'New Award';
    $scope.isNameDisabled = true;
    $scope.submitInProgress = false;
    $scope.awardSubtypeSelect2Options = [];
    $scope.awardDetailsID = null;
    $scope.tabs = [
      { name: 'basicDetails', label: ts('Basic Details') },
      { name: 'stages', label: ts('Award Stages') },
      { name: 'customFieldSets', label: ts('Custom Field Sets') },
      { name: 'reviewPanels', label: ts('Panels') },
      { name: 'reviewFields', label: ts('Review Fields') }
    ];
    $scope.basicDetails = {
      title: '',
      name: '',
      description: '',
      isEnabled: true
    };
    $scope.additionalDetails = {
      awardSubtype: null,
      startDate: null,
      endDate: null,
      awardManagers: [],
      selectedReviewFields: [],
      isTemplate: false
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
          .then(function (award) {
            updateApplicationStatusOptions(award.caseType);

            $scope.$emit('civiawards::edit-award::details-fetched', award);
          });
      }
    }());

    /**
     * @param {object} awardType An award type data as provided by the API.
     * @returns {object[]} A list of status objects belonging to the given award
     *   type. This data is taken from the list of status names stored in the
     *   award type. If no status names are stored, all statuses are returned.
     *   This is in accordance to core behaviour.
     */
    function getApplicationStatusesFromAwardType (awardType) {
      var statusNames = awardType.definition.statuses;
      var shouldIncludeAllStatuses = _.isEmpty(statusNames);

      return shouldIncludeAllStatuses
        ? CaseStatus.getAll()
        : getStatusesFilteredByName(statusNames);
    }

    /**
     * @param {object[]} statuses Application Statuses.
     * @returns {object[]} A list of application statuses as expected by the
     *   select2 component.
     */
    function getApplicantStatusSelect2Options (statuses) {
      return _.map(statuses, function (status) {
        return { id: status.value, text: status.label, name: status.name };
      });
    }

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
     * @param {string[]} statusNames A list of application status names.
     * @returns {object[]} A list of application status objects belonging to the
     *   given status names.
     */
    function getStatusesFilteredByName (statusNames) {
      return _.filter(CaseStatus.getAll(), function (status) {
        return _.includes(statusNames, status.name);
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

      var promise = saveIndividualTabsIfApplicable()
        .then(saveCaseTypeBasicDetails)
        .then(saveAdditionAwardDetails)
        .then(function (award) {
          updateApplicationStatusOptions(award.caseType);

          return award.additionalDetails.case_type_id;
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
     * Run save logic for individual tabs if present
     *
     * @returns {Promise} promise
     */
    function saveIndividualTabsIfApplicable () {
      var promises = [];

      _.each($scope.tabs, function (tabObj) {
        if (tabObj.save) {
          promises.push(tabObj.save());
        }
      });

      return $q.all(promises);
    }

    /**
     * Navigate to the Dashboard Page
     */
    function navigateToDashboard () {
      var caseTypeCategoryName = CaseTypeCategory.findById($scope.caseTypeCategoryId).name;

      $window.location.href = '/civicrm/case/a/?case_type_category=' + caseTypeCategoryName + '#/case?case_type_category=' + caseTypeCategoryName;
    }

    /**
     * Navigate to edit the given award
     *
     * @param {string/number} awardID id of the award
     */
    function navigateToAwardEditPage (awardID) {
      $location.path('/awards/' + $scope.caseTypeCategoryId + '/' + awardID + '/stages');
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
        case_type_category: $scope.caseTypeCategoryId,
        name: generateAwardName($scope.basicDetails.title),
        definition: {
          activityTypes: DEFAULT_ACTIVITY_TYPES,
          caseRoles: [{
            name: 'Application Manager',
            manager: 1
          }]
        }
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

      params.definition.statuses = areAllAwardsSelected ? [] : selectedAwardStageNames;
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
        award_subtype: $scope.additionalDetails.awardSubtype,
        review_fields: prepareReviewFields(),
        is_template: $scope.additionalDetails.isTemplate
      };

      if ($scope.awardDetailsID) {
        params.id = $scope.awardDetailsID;
      }

      return crmApi('AwardDetail', 'create', params)
        .then(function (awardData) {
          return {
            caseType: award,
            additionalDetails: awardData.values[0]
          };
        });
    }

    /**
     * Update the application status options based on the values stored in the
     * given case type.
     *
     * @param {object} caseType A Case Type object including its definition.
     */
    function updateApplicationStatusOptions (caseType) {
      var applicationStatuses = getApplicationStatusesFromAwardType(caseType);
      $scope.applicationStatusOptions = getApplicantStatusSelect2Options(applicationStatuses);
    }
  });
})(angular, CRM.$, CRM._);
