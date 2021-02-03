(function (angular, _) {
  var module = angular.module('civiawards-workflow');

  module.controller('CiviAwardWorkflowFilterController', CiviAwardWorkflowFilterController);

  /**
   * Edit Award Button Controller.
   *
   * @param {object} $scope the scope object.
   * @param {object} ts translation service
   * @param {object} AwardSubtype award sub types service.
   * @param {object} Select2Utils select 2 utility service
   */
  function CiviAwardWorkflowFilterController ($scope, ts, AwardSubtype,
    Select2Utils) {
    $scope.allSubtypes = _.map(AwardSubtype.getAll(), Select2Utils.mapSelectOptions);
    $scope.awardOptions = [
      { text: 'My ' + $scope.caseTypeCategory, id: CRM.config.user_contact_id },
      { text: 'All ' + $scope.caseTypeCategory, id: 'all_awards' }
    ];
  }
})(angular, CRM._);
