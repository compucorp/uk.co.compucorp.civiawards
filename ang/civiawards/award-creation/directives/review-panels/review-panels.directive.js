(function (angular, $, _) {
  var module = angular.module('civiawards');

  module.directive('civiawardReviewPanels', function () {
    return {
      controller: 'CiviawardReviewPanelsController',
      templateUrl: '~/civiawards/award-creation/directives/review-panels/review-panels.directive.html',
      restrict: 'E',
      scope: {
        awardId: '='
      }
    };
  });

  module.controller('CiviawardReviewPanelsController', function (
    $q, $scope, ts, dialogService, crmApi, crmStatus, getSelect2Value,
    CaseStatus) {
    var relationshipTypesIndexed = {};
    var contactsIndexed = {};
    var groupsIndexed = {};
    var tagsIndexed = {};

    $scope.ts = ts;
    $scope.submitInProgress = false;
    $scope.isLoading = false;
    $scope.submitButtonClickedOnce = false;
    $scope.relationshipTypes = [];
    $scope.existingReviewPanels = [];
    $scope.tabs = [
      { name: 'people', label: ts('People') },
      { name: 'applications', label: ts('Applications') },
      { name: 'permissions', label: ts('Permissions') }
    ];

    $scope.openCreateReviewPanelPopup = openCreateReviewPanelPopup;
    $scope.addMoreRelations = addMoreRelations;
    $scope.removeRelation = removeRelation;
    $scope.handleEditReviewPanel = handleEditReviewPanel;
    $scope.handleDeleteReviewPanel = handleDeleteReviewPanel;
    $scope.handleActiveStateReviewPanel = handleActiveStateReviewPanel;
    $scope.getActiveStateLabel = getActiveStateLabel;
    $scope.selectTab = selectTab;

    (function init () {
      $scope.applicantStatusSelect2Options = getApplicantStatusSelect2Options();
      resetReviewPanelPopup();
      handleInitialDataLoad();
    }());

    /**
     * Get the tags for Activities from API end point
     *
     * @returns {Promise} api call promise
     */
    function getTags () {
      return crmApi('Tag', 'get', {
        sequential: 1,
        used_for: 'Cases',
        options: { limit: 0 }
      }).then(function (data) {
        return data.values;
      });
    }

    /**
     * Returns Applicant Status's to be used in the UI
     *
     * @returns {Array} applicant status's array in a format suitable for select 2
     */
    function getApplicantStatusSelect2Options () {
      return _.map(CaseStatus.getAll(), function (caseStatus) {
        return { id: caseStatus.value, text: caseStatus.label, name: caseStatus.name };
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
     * Get label for the active state for the given review panel
     *
     * @param {object} reviewPanel review panel
     * @returns {string} enable/disable
     */
    function getActiveStateLabel (reviewPanel) {
      return reviewPanel.is_active === '0' ? ts('Enable') : ts('Disable');
    }

    /**
     * Handles Initial loading of data from API
     */
    function handleInitialDataLoad () {
      $scope.isLoading = true;

      $q.all({
        tags: getTags(),
        groups: fetchGroups(),
        relationships: fetchRelationshipsTypes(),
        existingReviewPanels: fetchExistingReviewPanels($scope.awardId)
      }).then(function (fetchedData) {
        if (fetchedData.existingReviewPanels && fetchedData.existingReviewPanels.length === 0) {
          return fetchedData;
        }

        return fetchContactsFromPanels(fetchedData.existingReviewPanels)
          .then(storeContactsIndexedById)
          .then(function () {
            return fetchedData;
          });
      }).then(function (fetchedData) {
        $scope.allTags = fetchedData.tags;
        tagsIndexed = _.indexBy(fetchedData.tags, 'id');

        $scope.relationshipTypes = prepareRelationshipsTypes(fetchedData.relationships);

        relationshipTypesIndexed = _.indexBy(fetchedData.relationships, 'id');
        groupsIndexed = _.indexBy(fetchedData.groups, 'id');

        if (fetchedData.existingReviewPanels) {
          $scope.existingReviewPanels = formatReviewPanelDataForUI(fetchedData.existingReviewPanels);
        }
      }).finally(function () {
        $scope.isLoading = false;
      });
    }

    /**
     * Index list of contacts by id
     *
     * @param {Array} contactsData list of contacts
     */
    function storeContactsIndexedById (contactsData) {
      contactsIndexed = _.indexBy(contactsData, 'id');
    }

    /**
     * Fetch Contacts from Review Panels
     *
     * @param {Array} existingReviewPanels list of exisiting review panels
     * @returns {Promise} promise
     */
    function fetchContactsFromPanels (existingReviewPanels) {
      var contactIdsToFetch = [];

      _.each(existingReviewPanels, function (reviewPanel) {
        _.each(reviewPanel.contact_settings.relationship, function (relationship) {
          contactIdsToFetch = contactIdsToFetch.concat(relationship.contact_id);
        });
      });

      if (contactIdsToFetch.length === 0) {
        return $q.resolve([]);
      }

      return crmApi('Contact', 'get', {
        sequential: 1,
        return: ['display_name'],
        id: { IN: contactIdsToFetch },
        options: { limit: 0 }
      }).then(function (contactsData) {
        return contactsData.values;
      });
    }

    /**
     * Prepare relations before editing a review panel
     *
     * @param {object} reviewPanel review panel object
     * @returns {Array} list of relationships
     */
    function getRelationshipsForEditingReviewPanel (reviewPanel) {
      if (reviewPanel.contact_settings.relationship.length === 0) {
        return [{ contacts: '', type: '' }];
      }

      return reviewPanel.contact_settings.relationship.map(function (relation) {
        var relationType = relation.is_a_to_b === '1'
          ? relation.relationship_type_id + '_a_b'
          : relation.relationship_type_id + '_b_a';

        return {
          contacts: relation.contact_id,
          type: relationType
        };
      });
    }
    /**
     * Handle Editing of Review panel
     * It opens the modal to edit the review panel
     *
     * @param {object} reviewPanel review panel object
     */
    function handleEditReviewPanel (reviewPanel) {
      $scope.currentReviewPanel = {
        id: reviewPanel.id,
        isEnabled: reviewPanel.is_active === '1',
        title: reviewPanel.title,
        contactSettings: {
          groups: {
            include: reviewPanel.contact_settings.include_groups,
            exclude: reviewPanel.contact_settings.exclude_groups
          },
          relationships: getRelationshipsForEditingReviewPanel(reviewPanel)
        },
        visibilitySettings: {
          selectedApplicantStatus: reviewPanel.visibility_settings.application_status,
          anonymizeApplication: reviewPanel.visibility_settings.anonymize_application === '1',
          tags: reviewPanel.visibility_settings.application_tags,
          isApplicationStatusRestricted: reviewPanel.visibility_settings.is_application_status_restricted === '1',
          restrictedApplicationStatus: reviewPanel.visibility_settings.restricted_application_status
        }
      };

      openCreateReviewPanelPopup();
    }

    /**
     * Handle Deletion of Review panel
     *
     * @param {object} reviewPanel review panel
     */
    function handleDeleteReviewPanel (reviewPanel) {
      CRM.confirm({
        title: ts('Delete %1?', { 1: reviewPanel.title }),
        message: ts('Are you sure you want to delete this?')
      }).on('crmConfirm:yes', function () {
        $scope.submitInProgress = true;

        var promise = crmApi('AwardReviewPanel', 'delete', {
          id: reviewPanel.id
        }).then(refreshReviewPanelsList)
          .then(function () {
            if (dialogService.dialogs.ReviewPanels) {
              dialogService.close('ReviewPanels');
              resetReviewPanelPopup();
            }
          })
          .finally(function () {
            $scope.submitInProgress = false;
          });

        return crmStatus({
          start: ts('Deleting Review Panel...'),
          success: ts('Review Panel Deleted')
        }, promise);
      });
    }

    /**
     * Handle Enable/Disable review panel
     *
     * @param {object} reviewPanel review panel
     */
    function handleActiveStateReviewPanel (reviewPanel) {
      CRM.confirm({
        title: ts('%1 %2?', { 1: getActiveStateLabel(reviewPanel), 2: reviewPanel.title }),
        message: ts('Are you sure you want to %1 this?', { 1: getActiveStateLabel(reviewPanel).toLowerCase() })
      }).on('crmConfirm:yes', function () {
        $scope.submitInProgress = true;

        var promise = crmApi('AwardReviewPanel', 'create', {
          id: reviewPanel.id,
          is_active: reviewPanel.is_active === '0' ? '1' : '0'
        }).then(refreshReviewPanelsList)
          .finally(function () {
            $scope.submitInProgress = false;
          });

        return crmStatus({
          start: ts('Saving Review Panel...'),
          success: ts('Review Panel saved')
        }, promise);
      });
    }

    /**
     * Format Review panel data fetched from API to be shown on the UI
     *
     * @param {Array} reviewPanelData list of review panels fetched from API
     * @returns {Array} list of review panels formatted to be shown on the UI
     */
    function formatReviewPanelDataForUI (reviewPanelData) {
      var reviewPanelDataCopied = _.clone(reviewPanelData);

      _.each(reviewPanelDataCopied, function (reviewPanel) {
        reviewPanel.formattedContactSettings = formatContactSettings(reviewPanel);
        reviewPanel.formattedVisibilitySettings = formattedVisibilitySettings(reviewPanel);
      });

      return reviewPanelDataCopied;
    }

    /**
     * Format Review panel visibility settings
     *
     * @param {object} reviewPanel review panel object
     * @returns {object} formatted visibility settings
     */
    function formattedVisibilitySettings (reviewPanel) {
      var formattedVisibilitySettings = {
        applicationStatus: reviewPanel.visibility_settings.application_status.map(function (status) {
          return CaseStatus.getAll()[status].label;
        }),
        tags: reviewPanel.visibility_settings.application_tags.map(function (tagId) {
          return tagsIndexed[tagId].name;
        })
      };

      return formattedVisibilitySettings;
    }

    /**
     * Format Review panel contact settings
     *
     * @param {object} reviewPanel review panel object
     * @returns {object} formatted contact settings
     */
    function formatContactSettings (reviewPanel) {
      if (!reviewPanel.contact_settings) {
        return {};
      }

      var formattedContactSettings = {
        include: [], exclude: [], relation: []
      };

      _.each(reviewPanel.contact_settings.include_groups, function (includeGroupID) {
        formattedContactSettings.include.push(groupsIndexed[includeGroupID].title);
      });

      _.each(reviewPanel.contact_settings.exclude_groups, function (excludeGroupID) {
        formattedContactSettings.exclude.push(groupsIndexed[excludeGroupID].title);
      });

      _.each(reviewPanel.contact_settings.relationship, function (relationship) {
        var specificRelationDetails = { relationshipLabel: '', contacts: [] };

        specificRelationDetails.relationshipLabel = relationship.is_a_to_b === '1'
          ? relationshipTypesIndexed[relationship.relationship_type_id].label_a_b
          : relationshipTypesIndexed[relationship.relationship_type_id].label_b_a;

        _.each(relationship.contact_id, function (contactID) {
          specificRelationDetails.contacts.push(contactsIndexed[contactID].display_name);
        });

        formattedContactSettings.relation.push(specificRelationDetails);
      });

      return formattedContactSettings;
    }

    /**
     * Fetch all groups from API
     *
     * @returns {Promise} promise
     */
    function fetchGroups () {
      return crmApi('Group', 'get', {
        sequential: 1,
        return: ['id', 'title'],
        options: { limit: 0 }
      }).then(function (groupsData) {
        return groupsData.values;
      });
    }

    /**
     * Refreshes Review Panels list
     *
     * @returns {Promise} promise
     */
    function refreshReviewPanelsList () {
      return fetchExistingReviewPanels($scope.awardId)
        .then(function (existingReviewPanelsData) {
          return fetchContactsFromPanels(existingReviewPanelsData)
            .then(storeContactsIndexedById)
            .then(function () {
              return existingReviewPanelsData;
            });
        }).then(function (existingReviewPanelsData) {
          $scope.existingReviewPanels = formatReviewPanelDataForUI(existingReviewPanelsData);
        });
    }
    /**
     * Get all groups from API for the given award id
     *
     * @param {string/number} awardId id of the award
     * @returns {Promise} promise
     */
    function fetchExistingReviewPanels (awardId) {
      return crmApi('AwardReviewPanel', 'get', {
        sequential: 1,
        case_type_id: awardId,
        options: { limit: 0 }
      }).then(function (reviewPanelData) {
        return reviewPanelData.values;
      });
    }

    /**
     * Add More Relations
     */
    function addMoreRelations () {
      $scope.currentReviewPanel.contactSettings.relationships.push({
        contacts: '',
        type: ''
      });
    }

    /**
     * Remove sent Relations
     *
     * @param {number} index index of relation to be removed
     */
    function removeRelation (index) {
      $scope.currentReviewPanel.contactSettings.relationships.splice(index, 1);
    }

    /**
     * Call API to fetch Relationships Types
     *
     * @returns {Promise} promise
     */
    function fetchRelationshipsTypes () {
      return crmApi('RelationshipType', 'get', {
        sequential: 1,
        is_active: 1,
        options: { sort: 'label_a_b', limit: 0 }
      }).then(function (relationshipTypeData) {
        return relationshipTypeData.values;
      });
    }

    /**
     * Prepare list of relationships divided into a to b and b to a
     *
     * @param {Array} relationshipTypeData list of relationship types
     * @returns {Array} list of relationships divided into a to b and b to a
     */
    function prepareRelationshipsTypes (relationshipTypeData) {
      var relationshipTypes = [];

      _.each(relationshipTypeData, function (relationshipType) {
        relationshipTypes.push({
          text: relationshipType.label_a_b,
          id: relationshipType.id + '_a_b'
        });
        relationshipTypes.push({
          text: relationshipType.label_b_a,
          id: relationshipType.id + '_b_a'
        });
      });

      return relationshipTypes;
    }

    /**
     * Open the Popup to Create Review Panels
     *
     * @param {object} options options to open popup
     * @param {object} options.resetData if all the fields should be reset
     */
    function openCreateReviewPanelPopup (options) {
      if (dialogService.dialogs.ReviewPanels) {
        return;
      }

      options = options || {};
      if (options.resetData) {
        resetReviewPanelPopup();
      }

      dialogService.open(
        'ReviewPanels',
        '~/civiawards/award-creation/directives/review-panels/review-panel-popup.html',
        $scope,
        {
          autoOpen: false,
          height: 'auto',
          width: '650px',
          title: ts('Create Review Panel'),
          buttons: prepareButtonsForReviewPanelPopup()
        }
      );
    }

    /**
     * Prepare buttons for the review panel popup
     *
     * @returns {Array} list of buttons
     */
    function prepareButtonsForReviewPanelPopup () {
      var buttons = [{
        text: ts('Save'),
        icons: { primary: 'fa-check' },
        click: function () {
          $scope.$apply(function () {
            saveReviewPanel();
          });
        }
      }];

      if ($scope.currentReviewPanel.id) {
        buttons.push({
          text: ts('Delete'),
          icons: { primary: 'fa-times' },
          class: 'civiawards__award__review-panel-form__delete',
          click: function () {
            $scope.$apply(function () {
              handleDeleteReviewPanel($scope.currentReviewPanel);
            });
          }
        });
      }

      return buttons;
    }

    /**
     * Prepare relatiosnhip data before calling API to save
     *
     * @returns {Promise} promise
     */
    function prepareRelationshipsForSave () {
      return _.chain($scope.currentReviewPanel.contactSettings.relationships)
      // filter the extra realtionship added in the UI before saving
        .filter(function (relation) {
          return relation.type.length !== 0;
        })
        .map(function (relation) {
          var isAToB = relation.type.indexOf('a_b') !== -1;
          var relationshipTypeId = relation.type.substr(0, relation.type.indexOf('_'));

          return {
            is_a_to_b: isAToB ? '1' : '0',
            relationship_type_id: relationshipTypeId,
            contact_id: getSelect2Value(relation.contacts)
          };
        })
        .value();
    }

    /**
     * Get parameters for the Save Review Panel API Call
     *
     * @returns {object} parameters
     */
    function getParamsForSavingReviewPanel () {
      return {
        id: $scope.currentReviewPanel.id || null,
        title: $scope.currentReviewPanel.title,
        is_active: $scope.currentReviewPanel.isEnabled ? '1' : '0',
        case_type_id: $scope.awardId,
        contact_settings: {
          exclude_groups: $scope.currentReviewPanel.contactSettings.groups.exclude,
          include_groups: $scope.currentReviewPanel.contactSettings.groups.include,
          relationship: prepareRelationshipsForSave()
        },
        visibility_settings: {
          application_status: getSelect2Value($scope.currentReviewPanel.visibilitySettings.selectedApplicantStatus),
          anonymize_application: $scope.currentReviewPanel.visibilitySettings.anonymizeApplication ? '1' : '0',
          application_tags: $scope.currentReviewPanel.visibilitySettings.tags,
          is_application_status_restricted: $scope.currentReviewPanel.visibilitySettings.isApplicationStatusRestricted ? '1' : '0',
          restricted_application_status: $scope.currentReviewPanel.visibilitySettings.isApplicationStatusRestricted
            ? getSelect2Value($scope.currentReviewPanel.visibilitySettings.restrictedApplicationStatus)
            : []
        }
      };
    }

    /**
     * Save Review Panel
     *
     * @returns {Promise} promise
     */
    function saveReviewPanel () {
      $scope.submitButtonClickedOnce = true;

      if (ifSaveButtonDisabled()) {
        return;
      }

      $scope.submitInProgress = true;

      var promise = crmApi('AwardReviewPanel', 'create', getParamsForSavingReviewPanel())
        .then(function () {
          dialogService.close('ReviewPanels');
          resetReviewPanelPopup();

          return refreshReviewPanelsList();
        }).finally(function () {
          $scope.submitInProgress = false;
        });

      return crmStatus({
        start: ts('Saving Review Panel...'),
        success: ts('Review Panel Saved')
      }, promise);
    }

    /**
     * Check if Save button should be disabled
     *
     * @returns {boolean} if Save button should be disabled
     */
    function ifSaveButtonDisabled () {
      return !$scope.review_panel_form.$valid || $scope.submitInProgress;
    }

    /**
     * Resets Review panel add popup
     */
    function resetReviewPanelPopup () {
      $scope.submitButtonClickedOnce = false;
      $scope.activeTab = 'people';
      $scope.currentReviewPanel = {
        title: '',
        isEnabled: false,
        visibilitySettings: {
          selectedApplicantStatus: '',
          anonymizeApplication: true,
          isApplicationStatusRestricted: false,
          restrictedApplicationStatus: [],
          tags: []
        },
        contactSettings: {
          groups: { include: [], exclude: [] },
          relationships: [{
            contacts: '',
            type: ''
          }]
        }
      };
    }
  });
})(angular, CRM.$, CRM._);
