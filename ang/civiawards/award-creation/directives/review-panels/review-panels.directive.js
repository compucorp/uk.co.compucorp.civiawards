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
    $scope, ts, dialogService, crmApi, crmStatus, getSelect2Value) {
    $scope.submitInProgress = false;
    $scope.relationshipTypes = [];
    $scope.reviewPanel = {
      groups: { include: [], exclude: [] },
      isEnabled: false,
      title: '',
      relationships: [{
        contacts: '',
        type: ''
      }]
    };

    $scope.openCreateReviewPanelPopup = openCreateReviewPanelPopup;
    $scope.addMoreRelations = addMoreRelations;
    $scope.removeRelation = removeRelation;

    (function init () {
      getRelationshipsTypes()
        .then(function (relationshipTypeData) {
          $scope.relationshipTypes = prepareRelationshipsTypes(relationshipTypeData.values);
        });
    }());

    /**
     * Add More Relations
     */
    function addMoreRelations () {
      $scope.reviewPanel.relationships.push({
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
      $scope.reviewPanel.relationships.splice(index, 1);
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
        return relationshipTypeData;
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
            click: saveReviewPanel
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
      return _.map($scope.reviewPanel.relationships, function (relation) {
        var isAToB = relation.type.indexOf('a_b') !== -1;
        var relationshipTypeId = relation.type.substr(0, relation.type.indexOf('_'));

        return {
          is_a_to_b: isAToB,
          relationship_type_id: relationshipTypeId,
          contact_id: getSelect2Value(relation.contacts)
        };
      });
    }

    /**
     * Save Review Panel
     *
     * @returns {Promise} promise
     */
    function saveReviewPanel () {
      if (ifSaveButtonDisabled()) {
        return;
      }

      var params = {
        title: $scope.reviewPanel.title,
        is_active: $scope.reviewPanel.isEnabled ? '1' : '0',
        case_type_id: $scope.awardId,
        exclude_groups: $scope.reviewPanel.groups.exclude,
        include_groups: $scope.reviewPanel.groups.include,
        relationship: prepareRelationshipsForSave()
      };

      $scope.submitInProgress = true;

      var promise = crmApi('AwardReviewPanel', 'create', params)
        .then(function () {
          dialogService.close('ReviewPanels');
          $scope.reviewPanel = {};
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
  });
})(angular, CRM.$, CRM._);
