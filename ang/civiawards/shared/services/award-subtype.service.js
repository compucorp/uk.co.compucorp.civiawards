(function (angular, $, _, CRM) {
  var module = angular.module('civiawards');

  module.service('AwardSubtype', AwardSubtype);

  /**
   * Award Subtypes Service
   */
  function AwardSubtype () {
    this.getAll = function () {
      return CRM.civiawards.awardSubtypes;
    };
  }
})(angular, CRM.$, CRM._, CRM);
