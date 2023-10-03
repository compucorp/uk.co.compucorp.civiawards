(function (angular, $, _) {
  var module = angular.module('civiawards');

  module.directive('civiawardReviewFieldsTable', function () {
    return {
      controller: 'CiviawardReviewFieldsTableController',
      templateUrl: '~/civiawards/award-creation/directives/review-fields/review-fields-table.directive.html',
      restrict: 'E'
    };
  });

  module.controller('CiviawardReviewFieldsTableController', function (
    $rootScope, $scope, civicaseCrmApi, dialogService, ts, isTruthy, crmApi4) {
    $scope.reviewFields = [];
    $scope.resourcesBaseUrl = CRM.config.resourceBase;
    $scope.toggleReviewField = toggleReviewField;
    $scope.openReviewFieldSelectionPopup = openReviewFieldSelectionPopup;
    $scope.removeReviewFieldFromSelection = removeReviewFieldFromSelection;
    $scope.findReviewFieldByID = findReviewFieldByID;
    $scope.toggleRequiredState = toggleRequiredState;
    $scope.moveUp = moveUp;
    $scope.moveDown = moveDown;
    $scope.moveToTop = moveToTop;
    $scope.moveToBottom = moveToBottom;
    $scope.getReviewFieldData = getReviewFieldData;

    (function init () {
      assignAllReviewFields();

      $rootScope.$on('civiawards::edit-award::details-fetched', setDetails);
    }());

    /**
     * Get the maximum weight from all review fields
     *
     * @returns {number} maximum weight
     */
    function getMaxWeight () {
      return _.max($scope.additionalDetails.selectedReviewFields, function (field) {
        return field.weight;
      }).weight;
    }

    /**
     * Get the minimum weight from all review fields
     *
     * @returns {number} minimum weight
     */
    function getMinWeight () {
      return _.min($scope.additionalDetails.selectedReviewFields, function (field) {
        return field.weight;
      }).weight;
    }

    /**
     * Returns the requested field data for the sent review field id
     *
     * @param {string} reviewFieldID review field id
     * @param {string} fieldName field name to be fetched
     * @returns {any} values of the field requested
     */
    function getReviewFieldData (reviewFieldID, fieldName) {
      var reviewField = _.find($scope.reviewFields, function (field) {
        return field.id === reviewFieldID;
      });

      return reviewField ? reviewField[fieldName] : null;
    }

    /**
     * Move the sent review field to the top
     *
     * @param {object} reviewField review field to be moved to the top
     */
    function moveToTop (reviewField) {
      var minWeight = getMinWeight();

      _.each($scope.additionalDetails.selectedReviewFields, function (field) {
        if (field.weight < reviewField.weight) {
          field.weight += 1;
        }
      });

      reviewField.weight = minWeight;
    }

    /**
     * Move the sent review field to the bottom
     *
     * @param {object} reviewField review field to be moved to the bottom
     */
    function moveToBottom (reviewField) {
      var maxWeight = getMaxWeight();

      _.each($scope.additionalDetails.selectedReviewFields, function (field) {
        if (field.weight > reviewField.weight) {
          field.weight = parseInt(field.weight) - 1;
        }
      });

      reviewField.weight = maxWeight;
    }

    /**
     * Move down the weight of sent review field
     *
     * @param {object} reviewField review field to be moved up
     */
    function moveDown (reviewField) {
      var reviewFieldNextToTheClickedOne = _.find($scope.additionalDetails.selectedReviewFields, function (searchedField) {
        return searchedField.weight === reviewField.weight + 1;
      });
      var tempWeight = reviewFieldNextToTheClickedOne.weight;

      reviewFieldNextToTheClickedOne.weight = reviewField.weight;
      reviewField.weight = tempWeight;
    }

    /**
     * Move up the weight of sent review field
     *
     * @param {object} reviewField review field to be moved up
     */
    function moveUp (reviewField) {
      var reviewFieldPreviousToTheClickedOne = _.find($scope.additionalDetails.selectedReviewFields, function (searchedField) {
        return searchedField.weight === reviewField.weight - 1;
      });
      var tempWeight = reviewFieldPreviousToTheClickedOne.weight;

      reviewFieldPreviousToTheClickedOne.weight = reviewField.weight;
      reviewField.weight = tempWeight;
    }

    /**
     * Toggle Required state of sent review field
     *
     * @param {object} reviewField review field
     */
    function toggleRequiredState (reviewField) {
      reviewField.required = !reviewField.required;
    }

    /**
     * Find the Review field by id
     *
     * @param {string} id review field id
     * @returns {object} review field
     */
    function findReviewFieldByID (id) {
      return _.find($scope.additionalDetails.selectedReviewFields, function (selectedField) {
        return selectedField.id === id;
      });
    }

    /**
     * Remove the sent Review field from selection
     *
     * @param {string} reviewField review field to be removed
     */
    function removeReviewFieldFromSelection (reviewField) {
      _.remove($scope.additionalDetails.selectedReviewFields, function (selectedField) {
        return selectedField.id === reviewField.id;
      });

      _.each($scope.additionalDetails.selectedReviewFields, function (field) {
        if (field.weight > reviewField.weight) {
          field.weight -= 1;
        }
      });
    }

    /**
     * Open the Popup to Select Review fields
     */
    function openReviewFieldSelectionPopup () {
      if (dialogService.dialogs.ReviewFields) {
        return;
      }

      dialogService.open('ReviewFields', '~/civiawards/award-creation/directives/review-fields/review-field-selection.html', $scope, {
        autoOpen: false,
        height: 'auto',
        width: '600px',
        title: 'Select Review Fields',
        buttons: [{
          text: ts('Done'),
          icons: { primary: 'fa-check' },
          click: function () {
            dialogService.close('ReviewFields');
          }
        }]
      });
    }

    /**
     * Fetch And assign all Review Fields
     *
     */
    function assignAllReviewFields () {
      crmApi4('CustomGroup', 'get', {
        select: ['name'],
        join: [['OptionValue AS option_value', 'INNER', ['extends_entity_column_value', '=', 'option_value.value']]],
        where: [['extends', '=', 'Activity'], ['option_value.option_group_id:name', '=', 'activity_type'], ['option_value.name', '=', 'Applicant Review']]
      }).then(function (customGroups) {
        if (customGroups.length === 0) {
          return [];
        }

        var applicantReviewCustomGroups = customGroups.map(function (customGroup) {
          return customGroup.name;
        });

        return civicaseCrmApi([['CustomField', 'get', {
          sequential: true,
          custom_group_id: { IN: applicantReviewCustomGroups },
          options: { limit: 0 }
        }]]);
      }).then(function (customFields) {
        $scope.reviewFields = customFields[0].values;
      });
    }

    /**
     * Toggle the selection of sent Review Field
     *
     * @param {object} reviewField review field object to be toggled
     */
    function toggleReviewField (reviewField) {
      var field = findReviewFieldByID(reviewField.id);

      if (field) {
        removeReviewFieldFromSelection(reviewField);
      } else {
        var weightToBeAssigned = 1 + (_.isEmpty($scope.additionalDetails.selectedReviewFields)
          ? 1
          : getMaxWeight());

        $scope.additionalDetails.selectedReviewFields.push({
          id: reviewField.id,
          required: false,
          weight: weightToBeAssigned
        });
      }
    }

    /**
     * Set Details
     *
     * @param {object} event event
     * @param {object} details details of the award
     */
    function setDetails (event, details) {
      _.each(details.additionalDetails.review_fields, function (field) {
        field.required = isTruthy(field.required);
        field.weight = parseInt(field.weight);
      });
      $scope.additionalDetails.selectedReviewFields = details.additionalDetails.review_fields;
    }
  });
})(angular, CRM.$, CRM._);
