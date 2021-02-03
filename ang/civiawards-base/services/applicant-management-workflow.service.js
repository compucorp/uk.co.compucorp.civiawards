(function (_, angular) {
  var module = angular.module('civiawards-base');

  module.service('ApplicantManagementWorkflow', ApplicantManagementWorkflow);

  /**
   * Applicant management workflows service
   *
   * @param {object} $q angulars $q service
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
    this.getWorkflowsList = getWorkflowsList;
    this.redirectToWorkflowCreationScreen = redirectToWorkflowCreationScreen;

    /**
     * Get Initial Activity Filters to load dashboard
     *
     * @returns {object} filter
     */
    function getActivityFilters () {
      return {
        case_filter: { 'case_type_id.is_active': 1, contact_is_deleted: 0 }
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
     * Returns workflows list for applicant management
     *
     * @param {object} selectedFilters selected filter values
     * @param {object} page page object needed for pagination
     * @param {boolean} formatResults if results should be formatted
     * @returns {Array} api call parameters
     */
    function getWorkflowsList (selectedFilters, page, formatResults) {
      var params = {};

      if (selectedFilters.managed_by !== 'all_awards') {
        params.managed_by = selectedFilters.managed_by;
      }

      if (selectedFilters.award_detail_params) {
        params.award_detail_params = prepareFilterObject(selectedFilters.award_detail_params, params);
      }

      params.case_type_params = _.extend(
        {},
        _.omit(selectedFilters, function (value, key) {
          return key === 'award_detail_params' || key === 'managed_by';
        }),
        { sequential: 1 }
      );

      if (formatResults) {
        params.case_type_params['api.AwardDetail.get'] = {
          case_type_id: '$value.id'
        };
      }

      var paramsWithLimit = _.cloneDeep(params);
      paramsWithLimit.case_type_params.options = {
        limit: page.size,
        offset: page.size * (page.num - 1)
      };

      var apiCalls = [
        ['Award', 'get', paramsWithLimit],
        ['Award', 'getcount', params]
      ];

      return civicaseCrmApi(apiCalls).then(function (data) {
        return formatResults ? getFormattedAwardDetailsData(data) : data;
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
     * @param {object[]} results results
     * @returns {object[]} formatted results
     */
    function getFormattedAwardDetailsData (results) {
      var managerContacts = getAwardManagersForWorkflows(results[0].values);

      return ContactsCache.add(managerContacts)
        .then(function () {
          results[0].values = _.map(results[0].values, function (workflow) {
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
