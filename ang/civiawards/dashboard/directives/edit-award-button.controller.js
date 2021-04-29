(function (angular) {
  var module = angular.module('civiawards');

  module.controller('EditAwardButtonController', EditAwardButtonController);

  /**
   * Edit Award Button Controller.
   *
   * @param {object} $scope the scope object.
   * @param {Function} $routeParams route parameters.
   * @param {Function} canCreateOrEditAwards can create or edit awards function.
   * @param {object} CaseTypeCategory Case Type Category service.
   */
  function EditAwardButtonController ($scope, $routeParams,
    canCreateOrEditAwards, CaseTypeCategory) {
    $scope.canEditAwards = canCreateOrEditAwards;
    $scope.editAwardUrl = 'civicrm/award/a/#/awards/' +
      $routeParams.case_type_category + '/' + $scope.caseType.id + '/' + 'dashboard';
  }
})(angular);
