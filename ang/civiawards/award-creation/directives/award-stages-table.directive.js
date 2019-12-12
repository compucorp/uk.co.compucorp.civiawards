(function (angular, $, _) {
  var module = angular.module('civiawards');

  module.directive('civiawardAwardStagesTable', function () {
    return {
      controller: 'CiviawardAwardStagesTableController',
      templateUrl: '~/civiawards/award-creation/directives/award-stages-table.directive.html',
      restrict: 'E'
    };
  });

  module.controller('CiviawardAwardStagesTableController', function ($rootScope, $scope, CaseStatus) {
    $scope.awardStages = CaseStatus.getAll();
    $scope.toggleAwardStage = toggleAwardStage;

    (function init () {
      $rootScope.$on('civiawards::edit-award::details-fetched', setDetails);
    }());

    /**
     * Toggle the selection of sent Award Stage
     *
     * @param {object} awardStage award stage object to be toggled
     */
    function toggleAwardStage (awardStage) {
      $scope.basicDetails.selectedAwardStages[awardStage.value] =
        !$scope.basicDetails.selectedAwardStages[awardStage.value];
    }

    /**
     * Set Details
     *
     * @param {object} event event
     * @param {object} details details of the award
     */
    function setDetails (event, details) {
      $scope.basicDetails.selectedAwardStages = getSelectedAwardStages(details.caseType.definition.statuses);
    }

    /**
     * Get Selected Award stages
     *
     * @param {Array} awardStages award stages fetched from api
     * @returns {object} selected award stages
     */
    function getSelectedAwardStages (awardStages) {
      var selectedAwardStages = {};

      _.each(awardStages, function (stageName) {
        var awardStage = _.find($scope.awardStages, function (stage) {
          return stage.name === stageName;
        });
        selectedAwardStages[awardStage.value] = true;
      });

      return selectedAwardStages;
    }
  });
})(angular, CRM.$, CRM._);
