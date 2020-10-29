(function (_, angular) {
  var module = angular.module('civiawards-workflow');

  module.service('DuplicateWorkflowApplicantmanagement', DuplicateWorkflow);

  /**
   * Duplicate applicant management workflows service
   *
   * @param {Function} civicaseCrmApi civicrm api service
   */
  function DuplicateWorkflow (civicaseCrmApi) {
    this.create = create;

    /**
     * @param {object} workflow workflow object
     * @returns {Promise} promise
     */
    function create (workflow) {
      return civicaseCrmApi([
        ['AwardDetail', 'get', { sequential: 1, case_type_id: workflow.id }],
        ['CaseType', 'create', _.extend({}, workflow, { id: null })]
      ]).then(function (data) {
        var awardsDetailsData = data[0].values[0];

        awardsDetailsData.id = null;
        awardsDetailsData.case_type_id = data[1].values[0].id;

        return civicaseCrmApi([
          ['AwardDetail', 'create', awardsDetailsData]
        ]);
      });
    }
  }
})(CRM._, angular);
