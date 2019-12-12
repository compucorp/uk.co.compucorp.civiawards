(function (angular, $, _) {
  var module = angular.module('civiawards');

  module.directive('civiawardReviewFieldsTable', function () {
    return {
      controller: 'CiviawardReviewFieldsTableController',
      templateUrl: '~/civiawards/award-creation/directives/review-fields-table.directive.html',
      restrict: 'E'
    };
  });

  module.controller('CiviawardReviewFieldsTableController', function ($rootScope, $scope, CaseStatus, crmApi, dialogService) {
    var APPLICANT_REVIEW_CUSTOM_GROUP_NAME = 'Applicant_Review';

    $scope.reviewFields = [];
    $scope.resourceURL = CRM.config.resourceBase;
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
      fetchAllReviewFields()
        .then(function (reviewFields) {
          $scope.reviewFields = reviewFields;
        });

      $rootScope.$on('civiawards::edit-award::details-fetched', setDetails);
    }());

    /**
     * Returns the requested field data for the sent review field id
     *
     * @param {string} reviewFieldID review field id
     * @param {*} fieldName field name to be fetched
     * @returns {any} values of the field requested
     */
    function getReviewFieldData (reviewFieldID, fieldName) {
      return _.find($scope.reviewFields, function (field) {
        return field.id === reviewFieldID;
      })[fieldName];
    }

    /**
     * Move the sent review field to the top
     *
     * @param {object} reviewField review field to be moved to the top
     */
    function moveToTop (reviewField) {
      _.each($scope.additionalDetails.selectedReviewFields, function (field) {
        if (field.weight < reviewField.weight) {
          field.weight += 1;
        }
      });

      reviewField.weight = 1;
    }

    /**
     * Move the sent review field to the bottom
     *
     * @param {object} reviewField review field to be moved to the bottom
     */
    function moveToBottom (reviewField) {
      _.each($scope.additionalDetails.selectedReviewFields, function (field) {
        if (field.weight > reviewField.weight) {
          field.weight -= 1;
        }
      });

      reviewField.weight = $scope.additionalDetails.selectedReviewFields.length;
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
      dialogService.open('ReviewFields', '~/civiawards/award-creation/directives/review-field-selection.html', $scope, {
        autoOpen: false,
        height: 'auto',
        width: '600px',
        title: 'Select Review Fields'
      });
    }

    /**
     * Fetch All Review Fields
     *
     * @returns {Promise} promise containing all review fields
     */
    function fetchAllReviewFields () {
      return crmApi([['CustomField', 'get', {
        sequential: true, custom_group_id: APPLICANT_REVIEW_CUSTOM_GROUP_NAME
      }]]).then(function (customFieldData) {
        return customFieldData[0].values;
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
        $scope.additionalDetails.selectedReviewFields.push({
          id: reviewField.id,
          required: false,
          weight: $scope.additionalDetails.selectedReviewFields.length + 1
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
      $scope.additionalDetails.selectedReviewFields = details.additionalDetails.review_fields;
    }
  });
})(angular, CRM.$, CRM._);
