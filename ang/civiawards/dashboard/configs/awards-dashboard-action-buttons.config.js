(function (angular, checkPermission) {
  var module = angular.module('civiawards');
  var EDIT_AWARD_PERMISSION = 'create/edit awards';

  module.config(function (DashboardActionButtonsProvider, tsProvider) {
    var ts = tsProvider.$get();
    var canCreateAwards = checkPermission(EDIT_AWARD_PERMISSION);
    var awardsActionButtons = [
      {
        buttonClass: 'btn btn-primary civicase__dashboard__action-btn civicase__dashboard__action-btn--light',
        iconClass: 'add_circle',
        identifier: 'AddAward',
        label: ts('Create new award'),
        weight: 100
      }
    ];

    (function () {
      if (!canCreateAwards) {
        return;
      }

      DashboardActionButtonsProvider
        .addButtons(awardsActionButtons);
    })();
  });
})(angular, CRM.checkPerm);
