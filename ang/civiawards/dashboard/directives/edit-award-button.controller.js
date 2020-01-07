(function (angular, getCrmUrl) {
  var module = angular.module('civiawards');

  module.controller('EditAwardButtonController', EditAwardButtonController);

  /**
   * Edit Award Button Controller.
   *
   * @param {object} $scope the scope object.
   * @param {Function} canCreateOrEditAwards can create or edit awards function.
   */
  function EditAwardButtonController ($scope, canCreateOrEditAwards) {
    $scope.canEditAwards = canCreateOrEditAwards;
  }
})(angular, CRM.url);
