(function (angular, _) {
  var module = angular.module('civiawards');

  module.controller('MoreFiltersDashboardActionButtonController', MoreFiltersDashboardActionButtonController);

  /**
   * Handles the visibility and click event for the "Add Award" dashboard action button.
   *
   * @param {object} $rootScope rootscope object
   * @param {object} $scope scope object
   * @param {object} $q angular promise object
   * @param {object} getSelect2Value service to fetch select 2 values
   * @param {object} crmApi the service to fetch civicrm backend
   * @param {object} dialogService the dialog service
   * @param {object} ts translation service
   * @param {object} CaseStatus Case Status service
   * @param {object} AwardType Award Type service
   * @param {Function} isApplicationManagementScreen is application management screen function
   */
  function MoreFiltersDashboardActionButtonController ($rootScope, $scope, $q,
    getSelect2Value, crmApi, dialogService, ts, CaseStatus, AwardType, isApplicationManagementScreen) {
    var model = {
      statuses: _.map(CaseStatus.getAll(), mapSelectOptions),
      award_types: _.map(AwardType.getAll(), mapSelectOptions),
      awardOptions: [
        { text: ts('My Awards'), id: 'my_awards' },
        { text: ts('All Awards'), id: 'all_awards' }
      ],
      selectedFilters: {
        awardFilter: 'my_awards',
        statuses: '',
        award_types: '',
        start_date: null,
        end_date: null
      },
      applyFilterAndCloseDialog: applyFilterAndCloseDialog
    };

    $scope.openMoreFiltersDialog = openMoreFiltersDialog;
    $scope.isNotificationVisible = isNotificationVisible;
    $scope.isVisible = isApplicationManagementScreen;

    (function init () {
      if (isApplicationManagementScreen()) {
        applyFilter();
      }
    }());

    /**
     * Checks if Notification should be visible
     *
     * @returns {boolean} if Notification should be visible
     */
    function isNotificationVisible () {
      return !_.isEqual(model.selectedFilters, {
        awardFilter: 'my_awards',
        statuses: '',
        award_types: '',
        start_date: null,
        end_date: null
      });
    }

    /**
     * Open more filtes dialog
     */
    function openMoreFiltersDialog () {
      if (!dialogService.dialogs.MoreFilters) {
        dialogService.open('MoreFilters', '~/civiawards/dashboard/directives/more-filters-popup.html', model, {
          autoOpen: false,
          height: 'auto',
          width: '350px',
          title: 'More Filters'
        });
      }
    }

    /**
     * Apply filter and close more filters dialog
     */
    function applyFilterAndCloseDialog () {
      applyFilter();
      dialogService.close('MoreFilters');
    }

    /**
     * Apply filter
     */
    function applyFilter () {
      processMyAwardsFilter()
        .then(processAwardTypeFilters)
        .then(function (awardTypeIds) {
          var param = {
            case_type_id: awardTypeIds.length > 0 ? { IN: awardTypeIds } : { 'IS NULL': 1 }
          };

          if (model.selectedFilters.statuses.length > 0) {
            var statusIds = getSelect2Value(model.selectedFilters.statuses);
            var statusGroupings = getGroupingsForCaseStatuses(statusIds);

            param.status_id = { IN: statusIds };
            param['status_id.grouping'] = { IN: statusGroupings };
          } else {
            param.status_id = { 'IS NOT NULL': 1 };
          }

          $rootScope.$broadcast('civicase::dashboard-filters::updated', param);
        });
    }

    /**
     * Given a list of status IDs, it will return the groupings that apply to
     * all of them.
     *
     * @param {string[]} statusIds a list of status IDs.
     * @returns {string[]} a list of status groupings.
     */
    function getGroupingsForCaseStatuses (statusIds) {
      var allCaseStatuses = CaseStatus.getAll();

      return _.chain(statusIds)
        .map(function (statusId) {
          return allCaseStatuses[statusId].grouping;
        })
        .unique()
        .value();
    }

    /**
     * Process Award Type Filters
     *
     * @param {number[]} caseTypeIDs case type ids
     * @returns {Promise<number[]>} promise
     */
    function processAwardTypeFilters (caseTypeIDs) {
      var filters = { sequential: 1 };

      if (caseTypeIDs.length === 0) {
        return $q.resolve([]);
      } else {
        filters.case_type_id = { IN: caseTypeIDs };
      }

      if (model.selectedFilters.award_types !== '') {
        filters.award_type = { IN: getSelect2Value(model.selectedFilters.award_types) };
      }
      if (model.selectedFilters.start_date) {
        filters.start_date = model.selectedFilters.start_date;
      }
      if (model.selectedFilters.end_date) {
        filters.end_date = model.selectedFilters.end_date;
      }

      return crmApi('AwardDetail', 'get', filters)
        .then(function (awardsData) {
          return awardsData.values.map(function (award) {
            return award.case_type_id;
          });
        });
    }

    /**
     * Process My Awards/All Awards filters
     *
     * @returns {Promise<number[]>} promise
     */
    function processMyAwardsFilter () {
      var filters = {
        sequential: 1
      };

      if (model.selectedFilters.awardFilter === 'my_awards') {
        filters.contact_id = CRM.config.user_contact_id;
      }

      return crmApi('AwardManager', 'get', filters)
        .then(function (awardsData) {
          return awardsData.values.map(function (awards) {
            return awards.case_type_id;
          });
        });
    }

    /**
     * Map the option parameter from API
     * to show up correctly on the UI.
     *
     * @param {object} opt object for caseTypes
     * @returns {object} mapped value to be used in UI
     */
    function mapSelectOptions (opt) {
      return {
        id: opt.value || opt.name,
        text: opt.label || opt.title,
        color: opt.color,
        icon: opt.icon
      };
    }
  }
})(angular, CRM._);
