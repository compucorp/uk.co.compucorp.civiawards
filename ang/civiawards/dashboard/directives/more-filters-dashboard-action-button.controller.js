(function (angular, _) {
  var module = angular.module('civiawards');

  module.controller('MoreFiltersDashboardActionButtonController', MoreFiltersDashboardActionButtonController);

  /**
   * Handles the visibility and click event for the "Add Award" dashboard action button.
   *
   * @param {object} $rootScope rootscope object
   * @param {object} $scope scope object
   * @param {object} $q angular promise object
   * @param {object} Select2Utils select 2 utility service
   * @param {object} civicaseCrmApi the service to fetch civicrm backend
   * @param {object} dialogService the dialog service
   * @param {object} ts translation service
   * @param {object} CaseStatus Case Status service
   * @param {object} AwardSubtype Award Sub Type service
   * @param {Function} isApplicationManagementScreen is application management screen function
   * @param {Function} processMyAwardsFilter service to process my awards filters
   */
  function MoreFiltersDashboardActionButtonController ($rootScope, $scope, $q,
    Select2Utils, civicaseCrmApi, dialogService, ts, CaseStatus, AwardSubtype,
    isApplicationManagementScreen, processMyAwardsFilter) {
    var DEFAULT_FILTERS = {
      awardFilter: 'my_awards',
      statuses: '',
      award_subtypes: '',
      start_date: null,
      end_date: null,
      onlyShowDisabledAwards: false
    };
    var model = {
      statuses: _.map(CaseStatus.getAll(), mapSelectOptions),
      award_subtypes: _.map(AwardSubtype.getAll(), mapSelectOptions),
      awardOptions: [
        { text: ts('My Awards'), id: 'my_awards' },
        { text: ts('All Awards'), id: 'all_awards' }
      ],
      selectedFilters: _.clone(DEFAULT_FILTERS),
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
     * Checks if Notification should be visible. This is true when the user
     * has selected a new filter value different from the default state.
     *
     * @returns {boolean} if Notification should be visible
     */
    function isNotificationVisible () {
      return !_.isEqual(model.selectedFilters, DEFAULT_FILTERS);
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
      processMyAwardsFilter(model.selectedFilters.awardFilter)
        .then(processAwardSubtypeFilters)
        .then(function (awardSubtypeIds) {
          var isCaseTypeActive = model.selectedFilters.onlyShowDisabledAwards
            ? '0'
            : '1';
          var param = {
            'case_type_id.is_active': isCaseTypeActive,
            case_type_id: awardSubtypeIds.length > 0
              ? { IN: awardSubtypeIds }
              : { 'IS NULL': 1 }
          };

          if (model.selectedFilters.statuses.length > 0) {
            var statusIds = Select2Utils.getSelect2Value(model.selectedFilters.statuses);
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
     * Process Award Sub Type Filters
     *
     * @param {number[]} caseTypeIDs case type ids
     * @returns {Promise<number[]>} promise
     */
    function processAwardSubtypeFilters (caseTypeIDs) {
      var filters = {
        sequential: 1,
        options: { limit: 0 }
      };

      if (caseTypeIDs.length === 0) {
        return $q.resolve([]);
      } else {
        filters.case_type_id = { IN: caseTypeIDs };
      }

      if (model.selectedFilters.award_subtypes !== '') {
        filters.award_subtype = { IN: Select2Utils.getSelect2Value(model.selectedFilters.award_subtypes) };
      }
      if (model.selectedFilters.start_date) {
        filters.start_date = model.selectedFilters.start_date;
      }
      if (model.selectedFilters.end_date) {
        filters.end_date = model.selectedFilters.end_date;
      }

      return civicaseCrmApi('AwardDetail', 'get', filters)
        .then(function (awardsData) {
          return awardsData.values.map(function (award) {
            return award.case_type_id;
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
