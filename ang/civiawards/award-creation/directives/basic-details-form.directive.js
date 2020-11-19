(function (angular, $, _) {
  var module = angular.module('civiawards');

  module.directive('civiawardBasicDetailsForm', function () {
    return {
      controller: 'CiviawardBasicDetailsFormController',
      templateUrl: '~/civiawards/award-creation/directives/basic-details-form.directive.html',
      restrict: 'E'
    };
  });

  module.controller('CiviawardBasicDetailsFormController', function ($rootScope,
    $scope, AwardSubtype, isTruthy) {
    var ts = CRM.ts('civicase');
    $scope.ts = ts;

    (function init () {
      $scope.awardSubtypeSelect2Options = getAwardSubtypeSelect2Options();
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
      $scope.basicDetails.isEnabled = isTruthy(caseType.is_active);
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
      $scope.additionalDetails.awardSubtype = additionalDetails.award_subtype;
      $scope.additionalDetails.awardManagers = additionalDetails.award_manager.join();
      $scope.additionalDetails.isTemplate = isTruthy(additionalDetails.is_template);
    }

    /**
     * Returns Award Subtype to be used in the UI
     *
     * @returns {Array} award subtype array in a format suitable for select 2
     */
    function getAwardSubtypeSelect2Options () {
      return _.map(AwardSubtype.getAll(), function (subType) {
        return { id: subType.value, text: subType.label, name: subType.name };
      });
    }
  });
})(angular, CRM.$, CRM._);
