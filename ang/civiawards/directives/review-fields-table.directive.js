(function (angular, $, _) {
  var module = angular.module('civiawards');

  module.directive('civiawardReviewFieldsTable', function () {
    return {
      controller: 'CiviawardReviewFieldsTableController',
      templateUrl: '~/civiawards/directives/review-fields-table.directive.html',
      restrict: 'E'
    };
  });

  module.controller('CiviawardReviewFieldsTableController', function ($rootScope, $scope, CaseStatus, crmApi) {
    var APPLICANT_REVIEW_CUSTOM_GROUP_NAME = 'Applicant_Review';

    $scope.reviewFields = [];
    $scope.toggleReviewField = toggleReviewField;

    (function init () {
      fetchAllReviewFields()
        .then(function (reviewFields) {
          $scope.reviewFields = reviewFields;
        });

      $rootScope.$on('civiawards::edit-award::details-fetched', setDetails);
    }());

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
      $scope.additionalDetails.selectedReviewFields[reviewField.id] =
        !$scope.additionalDetails.selectedReviewFields[reviewField.id];
    }

    /**
     * Set Details
     *
     * @param {object} event event
     * @param {object} details details of the award
     */
    function setDetails (event, details) {
      $scope.additionalDetails.selectedReviewFields = getSelectedReviewFields(details.additionalDetails.review_fields);
    }

    /**
     * Get Selected Review Fields
     *
     * @param {Array} reviewFieldIDS review field IDs fetched from api
     * @returns {object} selected review fields
     */
    function getSelectedReviewFields (reviewFieldIDS) {
      var selectedReviewFields = {};

      _.each(reviewFieldIDS, function (reviewFieldID) {
        selectedReviewFields[reviewFieldID] = true;
      });

      return selectedReviewFields;
    }
  });
})(angular, CRM.$, CRM._);
