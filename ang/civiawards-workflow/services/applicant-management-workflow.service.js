(function (_, angular) {
  var module = angular.module('civiawards-workflow');

  module.service('ApplicantManagementWorkflow', ApplicantManagementWorkflow);

  /**
   * Applicant management workflows service
   *
   * @param {object} $q angulars $q service
   * @param {Function} civicaseCrmApi civicrm api service
   * @param {object} AwardSubtype award subtype service
   * @param {object} ContactsCache contacts cache service
   * @param {Function} processMyAwardsFilter service to process my awards filters
   * @param {object} Select2Utils select 2 utility service
   * @param {object} $window window object of the browser
   */
  function ApplicantManagementWorkflow ($q, civicaseCrmApi, AwardSubtype,
    ContactsCache, processMyAwardsFilter, Select2Utils, $window) {
    var awardSubtypes = AwardSubtype.getAll();

    this.createDuplicate = createDuplicate;
    this.getEditWorkflowURL = getEditWorkflowURL;
    this.getWorkflowsList = getWorkflowsList;
    this.redirectToWorkflowCreationScreen = redirectToWorkflowCreationScreen;

    /**
     * @param {string/number} workflow workflow object
     * @returns {string} url to edit workflow page
     */
    function getEditWorkflowURL (workflow) {
      return 'civicrm/award/a/#/awards/' + workflow.case_type_category + '/' + workflow.id + '/' + 'workflow';
    }

    /**
     * @param {object} selectedFilters selected filter values
     * @returns {Promise} Promise which resolved to a list of case type ids
     */
    function fetchFilteredWorkflowIds (selectedFilters) {
      return processMyAwardsFilter(selectedFilters.awardFilter)
        .then(function (caseTypeIDs) {
          if (caseTypeIDs.length === 0) {
            return $q.resolve([]);
          }

          var filters = prepareFilterObject(selectedFilters, caseTypeIDs);

          return civicaseCrmApi('AwardDetail', 'get', filters)
            .then(function (awardsData) {
              return _.map(awardsData.values, function (award) {
                return award.case_type_id;
              });
            });
        });
    }

    /**
     * Returns workflows list for applicant management
     *
     * @param {object} caseTypeCategoryName case type category name
     * @param {object} selectedFilters selected filter values
     * @returns {Array} api call parameters
     */
    function getWorkflowsList (caseTypeCategoryName, selectedFilters) {
      return fetchFilteredWorkflowIds(selectedFilters)
        .then(function (workflowIds) {
          if (workflowIds.length === 0) {
            return $q.resolve([]);
          }

          var params = {
            id: { IN: workflowIds },
            sequential: 1,
            is_active: selectedFilters.is_active,
            case_type_category: caseTypeCategoryName,
            options: { limit: 0 },
            'api.AwardDetail.get': {
              case_type_id: '$value.id'
            }
          };

          return civicaseCrmApi('CaseType', 'get', params).then(function (data) {
            return getFormattedAwardDetailsData(data.values);
          });
        });
    }

    /**
     * @param {object} workflow workflow object
     * @returns {Promise} promise
     */
    function createDuplicate (workflow) {
      return civicaseCrmApi('CaseType', 'create', _.extend({}, workflow, { id: null }))
        .then(function (data) {
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
      var managerContacts = getAwardManagersForWorkflows(workflows);

      return ContactsCache.add(managerContacts)
        .then(function () {
          return _.map(workflows, function (workflow) {
            var awardDetails = workflow['api.AwardDetail.get'].values[0];

            return _.assign(
              {},
              _.omit(workflow, ['api.AwardDetail.get']),
              {
                awardDetails: awardDetails,
                awardDetailsFormatted: {
                  subtypeLabel: awardSubtypes[awardDetails.award_subtype].label,
                  managers: _.map(
                    awardDetails.award_manager,
                    function (managerID) {
                      return ContactsCache.getCachedContact(managerID).display_name;
                    }
                  )
                }
              }
            );
          });
        });
    }

    /**
     *
     * @param {object[]} workflows list of workflows
     * @returns {string[]} list of award manager contact ids
     */
    function getAwardManagersForWorkflows (workflows) {
      return _.chain(workflows)
        .map(function (workflow) {
          return workflow['api.AwardDetail.get'].values[0].award_manager;
        })
        .flatten()
        .value();
    }

    /**
     * @param {object} selectedFilters selected filter values
     * @param {string[]} caseTypeIDs list of case type ids
     * @returns {object} filter object
     */
    function prepareFilterObject (selectedFilters, caseTypeIDs) {
      var filters = {
        sequential: 1
      };

      filters.case_type_id = { IN: caseTypeIDs };

      if (selectedFilters.award_subtype !== '') {
        filters.award_subtype = {
          IN: Select2Utils.getSelect2Value(selectedFilters.award_subtype)
        };
      }

      if (selectedFilters.is_template !== '') {
        filters.is_template = selectedFilters.is_template;
      }

      return filters;
    }

    /**
     * Redirect to the workflow creation screen
     *
     * @param {object} caseTypeCategory case type category object
     */
    function redirectToWorkflowCreationScreen (caseTypeCategory) {
      $window.location.href = '/civicrm/award/a/#/awards/new/' + caseTypeCategory.value + '/workflow';
    }
  }
})(CRM._, angular);
