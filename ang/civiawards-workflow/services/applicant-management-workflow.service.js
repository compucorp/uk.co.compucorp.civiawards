(function (_, angular) {
  var module = angular.module('civiawards-workflow');

  module.service('ApplicantmanagementWorkflow', ApplicantmanagementWorkflow);

  /**
   * Duplicate applicant management workflows service
   *
   * @param {Function} civicaseCrmApi civicrm api service
   * @param {object} AwardSubtype award subtype service
   * @param {object} ContactsCache contacts cache service
   */
  function ApplicantmanagementWorkflow (civicaseCrmApi, AwardSubtype,
    ContactsCache) {
    var awardSubtypes = AwardSubtype.getAll();

    this.createDuplicate = createDuplicate;
    this.getWorkflowsList = getWorkflowsList;

    /**
     * Returns workflows list for applicant management
     *
     * @param {Array} caseTypeCategoryName case type category name
     * @returns {Array} api call parameters
     */
    function getWorkflowsList (caseTypeCategoryName) {
      return civicaseCrmApi('CaseType', 'get', {
        sequential: 1,
        case_type_category: caseTypeCategoryName,
        options: { limit: 0 },
        'api.AwardDetail.get': { case_type_id: '$value.id' }
      }).then(function (data) {
        return getFormattedAwardDetailsData(data.values);
      });
    }

    /**
     * @param {object} workflow workflow object
     * @returns {Promise} promise
     */
    function createDuplicate (workflow) {
      return civicaseCrmApi('CaseType', 'create', _.extend({}, workflow, { id: null }))
        .then(function (data) {
          // console.log(data);
          var awardsDetailsData = _.clone(workflow.awardDetails);

          awardsDetailsData.id = null;
          awardsDetailsData.case_type_id = data.values[0].id;

          return civicaseCrmApi([
            ['AwardDetail', 'create', awardsDetailsData]
          ]);
        });
    }

    /**
     * @param {object[]} workflows list of workflows
     * @returns {object[]} formatted list of workflows
     */
    function getFormattedAwardDetailsData (workflows) {
      var managerContacts = [];

      workflows = _.map(workflows, function (workflow) {
        workflow.awardDetails = workflow['api.AwardDetail.get'].values[0];
        delete workflow['api.AwardDetail.get'];

        managerContacts = managerContacts.concat(workflow.awardDetails.award_manager);

        workflow.awardDetailsFormatted = {
          subtypeLabel: awardSubtypes[workflow.awardDetails.award_subtype].label,
          managers: []
        };

        return workflow;
      });

      return ContactsCache.add(managerContacts)
        .then(function () {
          return _.map(workflows, function (workflow) {
            _.each(workflow.awardDetails.award_manager, function (managerID) {
              workflow.awardDetailsFormatted.managers.push(
                ContactsCache.getCachedContact(managerID).display_name
              );
            });

            return workflow;
          });
        });
    }
  }
})(CRM._, angular);
