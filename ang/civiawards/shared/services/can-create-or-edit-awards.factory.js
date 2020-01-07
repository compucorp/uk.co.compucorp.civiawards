(function (angular, checkPermission) {
  var module = angular.module('civiawards');

  module.factory('canCreateOrEditAwards', canCreateOrEditAwardsFactory);

  /**
   * Is Award Screen factory.
   *
   * @returns {Function} The is award screen function.
   */
  function canCreateOrEditAwardsFactory () {
    var hasAdministerCivicasePermission = checkPermission('administer CiviCase');
    var hasCreateOrEditAwardsPermission = checkPermission('create/edit awards');

    return canCreateOrEditAwards;

    /**
     * Determines if the current screen belongs to awards.
     *
     * @returns {boolean} true when case type category url param is awards
     */
    function canCreateOrEditAwards () {
      return hasAdministerCivicasePermission ||
        hasCreateOrEditAwardsPermission || false;
    }
  }
})(angular, CRM.checkPerm);
