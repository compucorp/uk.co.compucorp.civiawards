(function (angular) {
  var module = angular.module('civiawards');

  module.config(function (DashboardActionItemsProvider) {
    var awardsActionItems = [
      {
        templateUrl: '~/civiawards/dashboard/directives/add-award-dashboard-action-button.html',
        weight: 100
      },
      {
        templateUrl: '~/civiawards/dashboard/directives/dashboard-action-separator.html',
        weight: -5
      },
      {
        templateUrl: '~/civiawards/dashboard/directives/more-filters-dashboard-action-button.html',
        weight: -10
      }
    ];

    DashboardActionItemsProvider.addItems(awardsActionItems);
  });
})(angular);
