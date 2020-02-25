(function (angular, $, _) {
  var module = angular.module('civiawards');

  module.directive('civiawardReviewPanels', function () {
    return {
      controller: 'CiviawardReviewPanelsController',
      templateUrl: '~/civiawards/award-creation/directives/review-panels.directive.html',
      restrict: 'E'
    };
  });

  module.controller('CiviawardReviewPanelsController', function ($scope, ts, dialogService) {
    $scope.openCreateReviewPanelPopup = openCreateReviewPanelPopup;

    /**
     * Open the Popup to Select Review fields
     */
    function openCreateReviewPanelPopup () {
      dialogService.open('ReviewPanels', '~/civiawards/award-creation/directives/review-panel-popup.html', $scope, {
        autoOpen: false,
        height: 'auto',
        width: '600px',
        title: ts('Create Review Panel'),
        buttons: [{
          text: ts('Save'),
          icons: { primary: 'fa-check' },
          click: function () { }
        }]
      });
    }
  });
})(angular, CRM.$, CRM._);
