(function (angular, _) {
  var module = angular.module('civiawards');

  module.controller('MoreFiltersDashboardActionButtonController', MoreFiltersDashboardActionButtonController);

  /**
   * Handles the visibility and click event for the "Add Award" dashboard action button.
   *
   * @param {object} $rootScope rootscope object
   * @param {object} $scope scope object
   * @param {object} Select2Utils select 2 utility service
   * @param {object} dialogService the dialog service
   * @param {object} ts translation service
   * @param {object} CaseStatus Case Status service
   * @param {object} AwardSubtype Award Sub Type service
   * @param {Function} isApplicationManagementScreen is application management screen function
   */
  function MoreFiltersDashboardActionButtonController ($rootScope, $scope,
    Select2Utils, dialogService, ts, CaseStatus, AwardSubtype,
    isApplicationManagementScreen) {
    var DEFAULT_FILTERS = {
      managed_by: CRM.config.user_contact_id,
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
        { text: ts('My Awards'), id: CRM.config.user_contact_id },
        { text: ts('All Awards'), id: 'all_awards' }
      ],
      selectedFilters: _.clone(DEFAULT_FILTERS),
      applyFilterAndCloseDialog: applyFilterAndCloseDialog
    };

    $scope.openMoreFiltersDialog = openMoreFiltersDialog;
    $scope.isNotificationVisible = isNotificationVisible;
    $scope.isVisible = isApplicationManagementScreen;

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
      var isCaseTypeActive = model.selectedFilters.onlyShowDisabledAwards
        ? '0'
        : '1';

      $scope.activityFilters.case_filter['case_type_id.is_active'] = isCaseTypeActive;

      if (model.selectedFilters.statuses.length > 0) {
        var statusIds = Select2Utils.getSelect2Value(model.selectedFilters.statuses);
        var statusGroupings = getGroupingsForCaseStatuses(statusIds);

        $scope.activityFilters.case_filter.status_id = { IN: statusIds };
        $scope.activityFilters.case_filter['status_id.grouping'] = { IN: statusGroupings };
      } else {
        delete $scope.activityFilters.case_filter['status_id.grouping'];
        $scope.activityFilters.case_filter.status_id = { 'IS NOT NULL': 1 };
      }

      if (model.selectedFilters.managed_by !== 'all_awards') {
        $scope.activityFilters.case_filter['case_type_id.managed_by'] = model.selectedFilters.managed_by;
      } else {
        delete $scope.activityFilters.case_filter['case_type_id.managed_by'];
      }

      $scope.activityFilters.case_filter['case_type_id.award_detail_params'] = getAwardApiFilters();
      console.log($scope.activityFilters);

      // case_type_id: awardSubtypeIds.length > 0
      //     ? { IN: awardSubtypeIds }
      //     : { 'IS NULL': 1 }
      $rootScope.$broadcast('civicase::dashboard-filters::updated');
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
     * @returns {object} filters
     */
    function getAwardApiFilters () {
      var filters = {};

      if (model.selectedFilters.award_subtypes !== '') {
        filters.award_subtype = model.selectedFilters.award_subtypes;
      }
      if (model.selectedFilters.start_date) {
        filters.start_date = model.selectedFilters.start_date;
      }
      if (model.selectedFilters.end_date) {
        filters.end_date = model.selectedFilters.end_date;
      }

      return filters;
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
