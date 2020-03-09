(function () {
  var module = angular.module('civiawards.data');

  module.config((AwardMockData, CaseTypesMockDataProvider) => {
    CaseTypesMockDataProvider.add({
      [AwardMockData.id]: AwardMockData[0]
    });
  });
}());
