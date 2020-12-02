(function (angular, $, _, CRM) {
  var module = angular.module('civiawards-base');

  module.service('AwardSubtype', AwardSubtype);

  /**
   * Award Subtypes Service
   */
  function AwardSubtype () {
    this.getAll = function () {
      return CRM['civiawards-base'].awardSubtypes;
    };
  }
})(angular, CRM.$, CRM._, CRM);
