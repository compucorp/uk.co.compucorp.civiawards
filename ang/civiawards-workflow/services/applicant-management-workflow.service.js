(function (_, angular) {
  var module = angular.module('civiawards-workflow');

  module.service('ApplicantManagementWorkflow', ApplicantManagementWorkflow);

  /**
   * Duplicate applicant management workflows service
   *
   * @param {object} $q angulars $q service
   * @param {Function} civicaseCrmApi civicrm api service
   * @param {object} AwardSubtype award subtype service
   * @param {object} ContactsCache contacts cache service
   * @param {Function} processMyAwardsFilter service to process my awards filters
   * @param {object} Select2Utils select 2 utility service
   * @param {Function} isTruthy service to check if value is truthy
   */
  function ApplicantManagementWorkflow ($q, civicaseCrmApi, AwardSubtype,
    ContactsCache, processMyAwardsFilter, Select2Utils, isTruthy) {
    var awardSubtypes = AwardSubtype.getAll();

    this.createDuplicate = createDuplicate;
    this.getWorkflowsList = getWorkflowsList;

    /**
     * @param {object} scope scope object of thw workflow list controller
     * @returns {Promise} Promise which resolved to a list of case type ids
     */
    function applyFilter (scope) {
      return processMyAwardsFilter(scope.selectedFilters.awardFilter)
        .then(function (caseTypeIDs) {
          var filters = prepareFilterObject(scope, caseTypeIDs);

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
     * @param {Array} scope scope object of the workflows list controller
     * @returns {Array} api call parameters
     */
    function getWorkflowsList (scope) {
      return applyFilter(scope)
        .then(function (caseTypeIds) {
          var params = {
            sequential: 1,
            is_active: scope.selectedFilters.is_active,
            case_type_category: scope.caseTypeCategory,
            options: { limit: 0 },
            'api.AwardDetail.get': {
              case_type_id: '$value.id'
            }
          };

          if (caseTypeIds) {
            if (caseTypeIds.length === 0) {
              return $q.resolve([]);
            }
            params.id = { IN: caseTypeIds };
          }

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

    /**
     * @param {object} scope scope object of thw workflow list controller
     * @param {string[]} caseTypeIDs list of case type ids
     * @returns {object} filter object
     */
    function prepareFilterObject (scope, caseTypeIDs) {
      var filters = {
        sequential: 1
      };

      if (caseTypeIDs.length === 0) {
        return $q.resolve([]);
      } else {
        filters.case_type_id = { IN: caseTypeIDs };
      }

      if (scope.selectedFilters.award_subtype !== '') {
        filters.award_subtype = {
          IN: Select2Utils.getSelect2Value(scope.selectedFilters.award_subtype)
        };
      }

      if (scope.selectedFilters.is_template !== '') {
        filters.is_template = scope.selectedFilters.is_template;
      }

      return filters;
    }
  }
})(CRM._, angular);
