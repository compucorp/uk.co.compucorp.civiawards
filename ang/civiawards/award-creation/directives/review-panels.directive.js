(function (angular, $, _) {
  var module = angular.module('civiawards');

  module.directive('civiawardReviewPanels', function () {
    return {
      controller: 'CiviawardReviewPanelsController',
      templateUrl: '~/civiawards/award-creation/directives/review-panels.directive.html',
      restrict: 'E',
      scope: {
        awardId: '='
      }
    };
  });

  module.controller('CiviawardReviewPanelsController', function (
    $scope, ts, dialogService, crmApi, crmStatus) {
    $scope.reviewPanel = {};
    $scope.submitInProgress = false;

    $scope.openCreateReviewPanelPopup = openCreateReviewPanelPopup;

    /**
     * Open the Popup to Create Review Panels
     */
    function openCreateReviewPanelPopup () {
      dialogService.open(
        'ReviewPanels',
        '~/civiawards/award-creation/directives/review-panel-popup.html',
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
        is_active: $scope.reviewPanel.isEnabled,
        case_type_id: $scope.awardId
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
