(function (angular, $, _, CRM) {
  var module = angular.module('civiawards');

  module.service('AwardTypes', AwardTypes);

  /**
   * Award Types Service
   */
  function AwardTypes () {
    this.getAll = function () {
      return CRM.civiawards.awardTypes;
    };
  }
})(angular, CRM.$, CRM._, CRM);
