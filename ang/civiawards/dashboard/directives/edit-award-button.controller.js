(function (angular, getCrmUrl) {
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
    var currentCaseTypeCategoryValue =
      CaseTypeCategory.findByName($routeParams.case_type_category).value;

    $scope.canEditAwards = canCreateOrEditAwards;
    $scope.editAwardUrl = 'civicrm/award/a/#/awards/' +
     currentCaseTypeCategoryValue + '/' + $scope.caseType.id;
  }
})(angular, CRM.url);
