((_) => {
  var module = angular.module('civiawards.data');

  module.config((AwardMockData, CaseTypesMockDataProvider) => {
    _.each(AwardMockData, CaseTypesMockDataProvider.add);
  });
})(CRM._);
