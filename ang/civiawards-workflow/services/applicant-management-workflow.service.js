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
     * Returns workflows list for applicant management
     *
     * @param {object} caseTypeCategoryName case type category name
     * @param {object} selectedFilters selected filter values
     * @param {object} page page object needed for pagination
     * @returns {Array} api call parameters
     */
    function getWorkflowsList (caseTypeCategoryName, selectedFilters, page) {
      var params = {};

      if (selectedFilters.awardFilter === 'my_awards') {
        params.managed_by = CRM.config.user_contact_id;
      }

      params.award_detail_params = prepareFilterObject(selectedFilters, params);
      params.case_type_params = {
        sequential: 1,
        is_active: selectedFilters.is_active,
        case_type_category: caseTypeCategoryName,
        'api.AwardDetail.get': {
          case_type_id: '$value.id'
        }
      };

      var paramsWithLimit = _.cloneDeep(params);
      paramsWithLimit.case_type_params.options = {
        limit: page.size,
        offset: page.size * (page.num - 1)
      };

      var apiCalls = [
        [
          'Award',
          'get',
          paramsWithLimit

        ],
        [
          'Award',
          'getcount', params
        ]
      ];

      return civicaseCrmApi(apiCalls).then(function (data) {
        return getFormattedAwardDetailsData(data);
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
     * @param {object} selectedFilters selected filter values
     * @returns {object} filter object
     */
    function prepareFilterObject (selectedFilters) {
      var filters = {};

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
