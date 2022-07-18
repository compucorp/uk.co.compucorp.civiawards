(function (angular, $, _) {
  var module = angular.module('civiawards');

  module.directive('civiawardReviewPanels', function () {
    return {
      controller: 'CiviawardReviewPanelsController',
      templateUrl: '~/civiawards/award-creation/directives/review-panels/review-panels.directive.html',
      restrict: 'E',
      scope: {
        awardId: '<',
        statusOptions: '<'
      }
    };
  });

  module.controller('CiviawardReviewPanelsController', function (
    $q, $scope, $rootScope, ts, dialogService, civicaseCrmApi, crmStatus, Select2Utils,
    CaseStatus, isTruthy, crmUiHelp) {
    var relationshipTypesIndexed = {};
    var contactsIndexed = {};
    var groupsIndexed = {};
    var tagsIndexed = {};
    var caseStatusesIndexed = CaseStatus.getAll();

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
      resetReviewPanelPopup();
      handleInitialDataLoad();
    }());

    /**
     * Get the tags for Activities from API end point
     *
     * @returns {Promise} api call promise
     */
    function getTags () {
      return civicaseCrmApi('Tag', 'get', {
        sequential: 1,
        used_for: 'Cases',
        options: { limit: 0 }
      }).then(function (data) {
        return data.values;
      });
    }

    /**
     * Get the roles attached to the current Award
     *
     * @returns {Promise} api call promise
     */
    function getRoles () {
      return civicaseCrmApi('Award', 'get', {
        sequential: 1,
        case_type_params: { id: $scope.awardId },
        options: { limit: 1 }
      }).then(function (data) {
        const award = data.values[$scope.awardId];
        if (!award) {
          return [];
        }

        return _.map(award.definition.caseRoles, function (role) {
          return {
            text: role.name,
            name: role.name,
            id: role.name
          };
        });
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
      return !isTruthy(reviewPanel.is_active) ? ts('Enable') : ts('Disable');
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
        existingReviewPanels: fetchExistingReviewPanels($scope.awardId),
        roles: getRoles()
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
        $scope.caseRoles = fetchedData.roles;
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

      return civicaseCrmApi('Contact', 'get', {
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
        var relationType = isTruthy(relation.is_a_to_b)
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
        isEnabled: isTruthy(reviewPanel.is_active),
        title: reviewPanel.title,
        contactSettings: {
          groups: {
            include: reviewPanel.contact_settings.include_groups,
            exclude: reviewPanel.contact_settings.exclude_groups
          },
          relationships: getRelationshipsForEditingReviewPanel(reviewPanel),
          caseRoles: reviewPanel.contact_settings.case_roles
        },
        visibilitySettings: {
          selectedApplicantStatus: reviewPanel.visibility_settings.application_status,
          anonymizeApplication: isTruthy(reviewPanel.visibility_settings.anonymize_application),
          tags: reviewPanel.visibility_settings.application_tags,
          isApplicationStatusRestricted: isTruthy(reviewPanel.visibility_settings.is_application_status_restricted),
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

        var promise = civicaseCrmApi('AwardReviewPanel', 'delete', {
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

        var promise = civicaseCrmApi('AwardReviewPanel', 'create', {
          id: reviewPanel.id,
          is_active: isTruthy(reviewPanel.is_active) ? '0' : '1'
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
        reviewPanel = removeObsoleteData(reviewPanel);

        reviewPanel.formattedContactSettings = getFormattedContactSettings(reviewPanel);
        reviewPanel.formattedVisibilitySettings = getFormattedVisibilitySettings(reviewPanel);
      });

      return reviewPanelDataCopied;
    }

    /**
     * Remove obsolete data from the review panel.
     *
     * @param {Array} reviewPanel list of review panels fetched from API
     * @returns {Array} list of review panels
     */
    function removeObsoleteData (reviewPanel) {
      reviewPanel.visibility_settings.application_status = _.filter(reviewPanel.visibility_settings.application_status, function (statusId) {
        return !!caseStatusesIndexed[statusId];
      });
      reviewPanel.visibility_settings.application_tags = _.filter(reviewPanel.visibility_settings.application_tags, function (tagId) {
        return !!tagsIndexed[tagId];
      });

      reviewPanel.contact_settings.include_groups = _.filter(reviewPanel.contact_settings.include_groups, function (groupId) {
        return !!groupsIndexed[groupId];
      });

      reviewPanel.contact_settings.exclude_groups = _.filter(reviewPanel.contact_settings.exclude_groups, function (groupId) {
        return !!groupsIndexed[groupId];
      });

      return reviewPanel;
    }

    /**
     * Format Review panel visibility settings data from api
     * and make it ready to be displayed in the Review Panel Table.
     *
     * Api sends ids for application status, tags and those are replaced
     * with proper labels of each item
     *
     * @param {object} reviewPanel review panel object
     * @returns {object} formatted visibility settings
     */
    function getFormattedVisibilitySettings (reviewPanel) {
      var formattedVisibilitySettings = {
        applicationStatus: reviewPanel.visibility_settings.application_status.map(function (statusId) {
          return caseStatusesIndexed[statusId].label;
        }),
        tags: reviewPanel.visibility_settings.application_tags.map(function (tagId) {
          return tagsIndexed[tagId].name;
        })
      };

      return formattedVisibilitySettings;
    }

    /**
     * Format Review panel contact settings data from api
     * and make it ready to be displayed in the Review Panel Table.
     *
     * Api sends ids for contacts, relationships, groups, those are replaced
     * with proper labels of each item
     *
     * @param {object} reviewPanel review panel object
     * @returns {object} formatted contact settings
     */
    function getFormattedContactSettings (reviewPanel) {
      if (!reviewPanel.contact_settings) {
        return {};
      }

      var formattedContactSettings = {
        caseRoles: [], include: [], exclude: [], relation: []
      };

      _.each(reviewPanel.contact_settings.include_groups, function (includeGroupID) {
        formattedContactSettings.include.push(groupsIndexed[includeGroupID].title);
      });

      _.each(reviewPanel.contact_settings.exclude_groups, function (excludeGroupID) {
        formattedContactSettings.exclude.push(groupsIndexed[excludeGroupID].title);
      });

      _.each(reviewPanel.contact_settings.relationship, function (relationship) {
        var specificRelationDetails = { relationshipLabel: '', contacts: [] };

        specificRelationDetails.relationshipLabel = isTruthy(relationship.is_a_to_b)
          ? relationshipTypesIndexed[relationship.relationship_type_id].label_a_b
          : relationshipTypesIndexed[relationship.relationship_type_id].label_b_a;

        _.each(relationship.contact_id, function (contactID) {
          specificRelationDetails.contacts.push(contactsIndexed[contactID].display_name);
        });

        formattedContactSettings.relation.push(specificRelationDetails);
      });

      formattedContactSettings.caseRoles = reviewPanel.contact_settings.case_roles;

      return formattedContactSettings;
    }

    /**
     * Fetch all groups from API
     *
     * @returns {Promise} promise
     */
    function fetchGroups () {
      return civicaseCrmApi('Group', 'get', {
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
      return civicaseCrmApi('AwardReviewPanel', 'get', {
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
      return civicaseCrmApi('RelationshipType', 'get', {
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

      $rootScope.hs = crmUiHelp({});
      const dialogPromise = dialogService.open(
        'ReviewPanels',
        '~/civiawards/award-creation/directives/review-panels/review-panel-popup.html',
        $scope,
        {
          autoOpen: false,
          height: 'auto',
          width: '650px',
          title: ts('Create Review Panel'),
          buttons: prepareButtonsForReviewPanelPopup(),
          statusOptions: $scope.statusOptions
        }
      );

      dialogPromise
        .catch(function () {})
        .finally(function () {
          $rootScope.hs = null;
        });
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
            contact_id: Select2Utils.getSelect2Value(relation.contacts)
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
          relationship: prepareRelationshipsForSave(),
          case_roles: Select2Utils.getSelect2Value($scope.currentReviewPanel.contactSettings.caseRoles)
        },
        visibility_settings: {
          application_status: Select2Utils.getSelect2Value($scope.currentReviewPanel.visibilitySettings.selectedApplicantStatus),
          anonymize_application: $scope.currentReviewPanel.visibilitySettings.anonymizeApplication ? '1' : '0',
          application_tags: $scope.currentReviewPanel.visibilitySettings.tags,
          is_application_status_restricted: $scope.currentReviewPanel.visibilitySettings.isApplicationStatusRestricted ? '1' : '0',
          restricted_application_status: $scope.currentReviewPanel.visibilitySettings.isApplicationStatusRestricted
            ? Select2Utils.getSelect2Value($scope.currentReviewPanel.visibilitySettings.restrictedApplicationStatus)
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

      var promise = civicaseCrmApi('AwardReviewPanel', 'create', getParamsForSavingReviewPanel())
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
      return !$scope.review_panel_form.$valid || !$scope.review_panel_form_visibility.$valid || $scope.submitInProgress;
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
          }],
          caseRoles: []
        }
      };
    }
  });
})(angular, CRM.$, CRM._);
