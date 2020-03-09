(function (angular, $, _, CRM) {
  var module = angular.module('civiawards');

  module.service('AwardType', AwardType);

  /**
   * Award Types Service
   */
  function AwardType () {
    this.getAll = function () {
      return CRM.civiawards.awardTypes;
    };
  }
})(angular, CRM.$, CRM._, CRM);
