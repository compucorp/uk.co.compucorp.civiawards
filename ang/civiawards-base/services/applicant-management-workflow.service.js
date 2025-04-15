(function (_, angular) {
  var module = angular.module('civiawards-base');

  module.service('ApplicantManagementWorkflow', ApplicantManagementWorkflow);

  /**
   * Applicant management workflows service
   *
   * @param {object} $q angulars queue service
   * @param {Function} civicaseCrmApi civicrm api service
   * @param {object} AwardSubtype award subtype service
   * @param {object} ContactsCache contacts cache service
   * @param {object} Select2Utils select 2 utility service
   * @param {object} $window window object of the browser
   */
  function ApplicantManagementWorkflow ($q, civicaseCrmApi, AwardSubtype,
    ContactsCache, Select2Utils, $window) {
    var awardSubtypes = AwardSubtype.getAll();

    this.getActivityFilters = getActivityFilters;
    this.createDuplicate = createDuplicate;
    this.getEditWorkflowURL = getEditWorkflowURL;
    this.getWorkflowsListForCaseOverview = getWorkflowsListForCaseOverview;
    this.getWorkflowsListForManageWorkflow = getWorkflowsListForManageWorkflow;
    this.redirectToWorkflowCreationScreen = redirectToWorkflowCreationScreen;

    /**
     * Get Initial Activity Filters to load dashboard
     *
     * @returns {object} filter
     */
    function getActivityFilters () {
      return {
        case_filter: {
          'case_type_id.is_active': 1,
          'case_type_id.managed_by': CRM.config.user_contact_id,
          contact_is_deleted: 0
        }
      };
    }

    /**
     * @param {string/number} workflow workflow object
     * @returns {string} url to edit workflow page
     */
    function getEditWorkflowURL (workflow) {
      return 'civicrm/award/a/#/awards/' + workflow.case_type_category + '/' + workflow.id + '/' + 'workflow';
    }

    /**
     * Returns workflows list for case overview page
     *
     * @param {object} selectedFilters selected filter values
     * @param {object} page page object needed for pagination
     * @returns {Array} api call parameters
     */
    function getWorkflowsListForCaseOverview (selectedFilters, page) {
      var params = getWorkflowsListApiCallParams(selectedFilters);

      var apiCalls = getApiCallParams(params, page);

      return civicaseCrmApi(apiCalls).then(function (data) {
        return {
          values: data[0].values,
          count: data[1]
        };
      });
    }

    /**
     * Returns formatted workflows list for for manage awards page
     *
     * @param {object} selectedFilters selected filter values
     * @param {object} page page object needed for pagination
     * @returns {Array} api call parameters
     */
    function getWorkflowsListForManageWorkflow (selectedFilters, page) {
      var params = getWorkflowsListApiCallParams(selectedFilters);

      params.case_type_params['api.AwardDetail.get'] = {
        case_type_id: '$value.id'
      };

      var apiCalls = getApiCallParams(params, page);

      return civicaseCrmApi(apiCalls).then(function (data) {
        return $q.all([
          getFormattedAwardDetailsData(data[0].values),
          data[1]
        ]);
      }).then(function (data) {
        return {
          values: data[0],
          count: data[1]
        };
      });
    }

    /**
     * @param {object} params parameters
     * @param {object} page page object
     * @returns {Array} list of api call parameters
     */
    function getApiCallParams (params, page) {
      var paramsWithLimit = _.cloneDeep(params);
      paramsWithLimit.case_type_params.options = {
        limit: page.size,
        offset: page.size * (page.num - 1)
      };

      return [
        ['Award', 'get', paramsWithLimit],
        ['Award', 'getcount', params]
      ];
    }

    /**
     * @param {object} selectedFilters selected filter values
     * @returns {object} parameters
     */
    function getWorkflowsListApiCallParams (selectedFilters) {
      var params = {};

      if (selectedFilters.managed_by !== 'all_awards') {
        params.managed_by = selectedFilters.managed_by;
      }

      if (selectedFilters.award_detail_params) {
        params.award_detail_params = prepareFilterObject(selectedFilters.award_detail_params, params);
      }

      params.case_type_params = _.extend(
        {},
        _.omit(selectedFilters, ['award_detail_params', 'managed_by']),
        { sequential: 1 }
      );

      return params;
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
     * @param {object[]} results results
     * @returns {object[]} formatted results
     */
    function getFormattedAwardDetailsData (results) {
      var managerContacts = getAwardManagersForWorkflows(results);

      return ContactsCache.add(managerContacts)
        .then(function () {
          results = _.map(results, function (workflow) {
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
                      return ContactsCache.getCachedContact(managerID)?.display_name ?? '';
                    }
                  )
                }
              }
            );
          });

          return results;
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
     * @param {object} awardTypeFilters selected award type filter values
     * @returns {object} filter object
     */
    function prepareFilterObject (awardTypeFilters) {
      var filters = _.extend({}, awardTypeFilters);

      if (awardTypeFilters.award_subtype &&
        awardTypeFilters.award_subtype !== '' &&
        awardTypeFilters.award_subtype.length > 0) {
        filters.award_subtype = {
          IN: Select2Utils.getSelect2Value(awardTypeFilters.award_subtype)
        };
      } else {
        delete filters.award_subtype;
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
