(function (angular) {
  var module = angular.module('civiawards');

  module.config(function (DashboardActionButtonsProvider) {
    var awardsActionButtons = [
      {
        buttonClass: 'btn btn-primary civicase__dashboard__action-btn civicase__dashboard__action-btn--white',
        iconClass: 'add_circle',
        identifier: 'AddAward',
        label: 'Create new award',
        weight: 100
      }
    ];

    DashboardActionButtonsProvider.addButtons(awardsActionButtons);
  });
})(angular);
