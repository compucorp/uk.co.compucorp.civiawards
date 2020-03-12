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
    $q, $scope, ts, dialogService, crmApi, crmStatus, getSelect2Value) {
    var relationshipTypesIndexed = {};
    var contactsIndexed = {};

    $scope.ts = ts;
    $scope.submitInProgress = false;
    $scope.isLoading = false;
    $scope.submitButtonClickedOnce = false;
    $scope.relationshipTypes = [];
    $scope.existingReviewPanels = [];

    $scope.openCreateReviewPanelPopup = openCreateReviewPanelPopup;
    $scope.addMoreRelations = addMoreRelations;
    $scope.removeRelation = removeRelation;
    $scope.handleEditReviewPanel = handleEditReviewPanel;

    (function init () {
      resetReviewPanelPopup();
      $scope.isLoading = true;

      $q.all({
        groups: getGroups(),
        relationships: getRelationshipsTypes(),
        existingReviewPanels: $scope.awardId ? fetchExistingReviewPanels($scope.awardId) : null
      }).then(function (fetchedData) {
        if (fetchedData.existingReviewPanels && fetchedData.existingReviewPanels.length === 0) {
          return fetchedData;
        }

        return fetchContactsFromPanelsAndIndexById(fetchedData.existingReviewPanels)
          .then(function () {
            return fetchedData;
          });
      }).then(function (fetchedData) {
        $scope.relationshipTypes = prepareRelationshipsTypes(fetchedData.relationships);
        relationshipTypesIndexed = _.indexBy(fetchedData.relationships, 'id');

        $scope.groupsIndexed = _.indexBy(fetchedData.groups, 'id');

        if (fetchedData.existingReviewPanels) {
          $scope.existingReviewPanels = formatReviewPanelDataForUI(fetchedData.existingReviewPanels);
        }
      }).finally(function () {
        $scope.isLoading = false;
      });
    }());

    /**
     * Fetch Contacts from panels and indexes them by id
     *
     * @param {Array} existingReviewPanels list of exisiting review panels
     * @returns {Promise} promise
     */
    function fetchContactsFromPanelsAndIndexById (existingReviewPanels) {
      return fetchContactsFromPanels(existingReviewPanels)
        .then(function (contactsData) {
          contactsIndexed = _.indexBy(contactsData, 'id');
        });
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
     * Handle Editing of Review panel
     * It opens the modal to edit the review panel
     *
     * @param {object} reviewPanel review panel object
     */
    function handleEditReviewPanel (reviewPanel) {
      $scope.currentReviewPanel = {
        id: reviewPanel.id,
        groups: {
          include: reviewPanel.contact_settings.include_groups,
          exclude: reviewPanel.contact_settings.exclude_groups
        },
        isEnabled: reviewPanel.is_active === '1',
        title: reviewPanel.title,
        relationships: reviewPanel.contact_settings.relationship.map(function (relation) {
          var relationType = relation.is_a_to_b === '1'
            ? relation.relationship_type_id + '_a_b'
            : relation.relationship_type_id + '_b_a';

          return {
            contacts: relation.contact_id,
            type: relationType
          };
        })
      };

      openCreateReviewPanelPopup();
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
        if (!reviewPanel.contact_settings) {
          return;
        }

        reviewPanel.formattedContactSettings = {
          include: [], exclude: [], relation: []
        };

        _.each(reviewPanel.contact_settings.include_groups, function (includeGroupID) {
          reviewPanel.formattedContactSettings.include.push(
            $scope.groupsIndexed[includeGroupID].title
          );
        });

        _.each(reviewPanel.contact_settings.exclude_groups, function (excludeGroupID) {
          reviewPanel.formattedContactSettings.exclude.push(
            $scope.groupsIndexed[excludeGroupID].title
          );
        });

        _.each(reviewPanel.contact_settings.relationship, function (relationship) {
          var specificRelationDetails = { relationshipLabel: '', contacts: [] };

          specificRelationDetails.relationshipLabel = relationship.is_a_to_b === '1'
            ? relationshipTypesIndexed[relationship.relationship_type_id].label_a_b
            : relationshipTypesIndexed[relationship.relationship_type_id].label_b_a;

          _.each(relationship.contact_id, function (contactID) {
            specificRelationDetails.contacts.push(contactsIndexed[contactID].display_name);
          });

          reviewPanel.formattedContactSettings.relation.push(specificRelationDetails);
        });
      });

      return reviewPanelDataCopied;
    }

    /**
     * Get all groups from API
     *
     * @returns {Promise} promise
     */
    function getGroups () {
      return crmApi('Group', 'get', {
        sequential: 1,
        return: ['id', 'title'],
        options: { limit: 0 }
      }).then(function (groupsData) {
        return groupsData.values;
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
      $scope.currentReviewPanel.relationships.push({
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
      $scope.currentReviewPanel.relationships.splice(index, 1);
    }

    /**
     * Call API to get Relationships Types
     *
     * @returns {Promise} promise
     */
    function getRelationshipsTypes () {
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
     */
    function openCreateReviewPanelPopup () {
      if (dialogService.dialogs.ReviewPanels) {
        return;
      }

      dialogService.open(
        'ReviewPanels',
        '~/civiawards/award-creation/directives/review-panels/review-panel-popup.html',
        $scope,
        {
          autoOpen: false,
          height: 'auto',
          width: '600px',
          title: ts('Create Review Panel'),
          buttons: [{
            text: ts('Save'),
            icons: { primary: 'fa-check' },
            click: function () {
              $scope.$apply(function () {
                saveReviewPanel();
              });
            }
          }]
        }
      );
    }

    /**
     * Prepare relatiosnhip data before calling API to save
     *
     * @returns {Promise} promise
     */
    function prepareRelationshipsForSave () {
      return _.map($scope.currentReviewPanel.relationships, function (relation) {
        var isAToB = relation.type.indexOf('a_b') !== -1;
        var relationshipTypeId = relation.type.substr(0, relation.type.indexOf('_'));

        return {
          is_a_to_b: isAToB ? '1' : '0',
          relationship_type_id: relationshipTypeId,
          contact_id: getSelect2Value(relation.contacts)
        };
      });
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
          exclude_groups: $scope.currentReviewPanel.groups.exclude,
          include_groups: $scope.currentReviewPanel.groups.include,
          relationship: prepareRelationshipsForSave()
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

          return fetchExistingReviewPanels($scope.awardId);
        }).then(function (existingReviewPanelsData) {
          return fetchContactsFromPanelsAndIndexById(existingReviewPanelsData)
            .then(function () {
              return existingReviewPanelsData;
            });
        }).then(function (existingReviewPanelsData) {
          $scope.existingReviewPanels = formatReviewPanelDataForUI(existingReviewPanelsData);
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
      $scope.currentReviewPanel = {
        groups: { include: [], exclude: [] },
        isEnabled: false,
        title: '',
        relationships: [{
          contacts: '',
          type: ''
        }]
      };
    }
  });
})(angular, CRM.$, CRM._);
