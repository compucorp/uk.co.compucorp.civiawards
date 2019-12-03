(function (angular, $, _) {
  var module = angular.module('civiawards');

  module.directive('civiawardBasicDetailsForm', function () {
    return {
      controller: 'CiviawardBasicDetailsFormController',
      templateUrl: '~/civiawards/directives/basic-details-form.directive.html',
      restrict: 'E'
    };
  });

  module.controller('CiviawardBasicDetailsFormController', function ($rootScope, $scope, AwardType) {
    var ts = CRM.ts('civicase');
    $scope.ts = ts;

    (function init () {
      $scope.awardTypeSelect2Options = getAwardTypeSelect2Options();
      $rootScope.$on('civiawards::edit-award::details-fetched', setDetails);
    }());

    /**
     * Set Details
     *
     * @param {object} event event
     * @param {object} details details of the award
     */
    function setDetails (event, details) {
      setBasicDetails(details.caseType);
      setAdditionalInformation(details.additionalDetails);
    }

    /**
     * Set basic details
     *
     * @param {object} caseType case type
     */
    function setBasicDetails (caseType) {
      $scope.pageTitle = 'Configure Award - ' + caseType.title;
      $scope.basicDetails.title = caseType.title;
      $scope.basicDetails.name = caseType.name;
      $scope.basicDetails.description = caseType.description;
      $scope.basicDetails.isEnabled = caseType.is_active === '1';
    }

    /**
     * Set Additional Details of award
     *
     * @param {object} additionalDetails additional details
     */
    function setAdditionalInformation (additionalDetails) {
      $scope.awardDetailsID = additionalDetails.id;
      $scope.additionalDetails.startDate = additionalDetails.start_date;
      $scope.additionalDetails.endDate = additionalDetails.end_date;
      $scope.additionalDetails.awardType = additionalDetails.award_type;
      $scope.additionalDetails.awardManagers = additionalDetails.award_manager.join();
    }

    /**
     * Returns Award Types to be used in the UI
     *
     * @returns {Array} award types array in a format suitable for select 2
     */
    function getAwardTypeSelect2Options () {
      return _.map(AwardType.getAll(), function (awardType) {
        return { id: awardType.value, text: awardType.label, name: awardType.name };
      });
    }
  });
})(angular, CRM.$, CRM._);
