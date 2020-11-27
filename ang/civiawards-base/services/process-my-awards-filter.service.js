(function (angular, $, _, CRM) {
  var module = angular.module('civiawards-base');

  module.service('processMyAwardsFilter', processMyAwardsFilter);

  /**
   * @param {Function} civicaseCrmApi service to use civicrm api
   * @returns {Function} function to process my awards filters
   */
  function processMyAwardsFilter (civicaseCrmApi) {
    return processMyAwardsFilter;

    /**
     * Process My Awards/All Awards filters
     *
     * @param {object} managerFilter manager filter values
     * @returns {Promise<string[]>} promise
     */
    function processMyAwardsFilter (managerFilter) {
      var filters = {
        sequential: 1
      };

      if (managerFilter === 'my_awards') {
        filters.contact_id = CRM.config.user_contact_id;
      }

      return civicaseCrmApi('AwardManager', 'get', filters)
        .then(function (awardsData) {
          return awardsData.values.map(function (awards) {
            return awards.case_type_id;
          });
        });
    }
  }
})(angular, CRM.$, CRM._, CRM);
