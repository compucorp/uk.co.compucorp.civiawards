(function (_, angular) {
  var module = angular.module('civiawards-payments-tab');

  module.directive('civiawardsPaymentsTable', function () {
    return {
      templateUrl: '~/civiawards-payments-tab/directives/payments-table.directive.html',
      controller: 'civiawardsPaymentsTableController',
      restrict: 'E',
      scope: {
        caseItem: '<'
      }
    };
  });

  module.controller(
    'civiawardsPaymentsTableController',
    civiawardsPaymentsTableController
  );

  /**
   * Payments Table Controller.
   *
   * @param {object} $scope scope object.
   * @param {Function} civicaseCrmApi CRM API service.
   * @param {object} paymentTypes payment type objects indexed by value.
   */
  function civiawardsPaymentsTableController ($scope, civicaseCrmApi, paymentTypes) {
    var currentFilters = {};
    var customFields = [];

    $scope.isLoading = false;
    $scope.paging = { page: 1, pageSize: 25, total: 0, isDisabled: false };

    $scope.goToPage = goToPage;
    $scope.submitFilters = submitFilters;

    (function init () {
      filterPayments();

      $scope.$on('civiawards::paymentstable::refresh', function () {
        filterPayments(currentFilters);
      });
    })();

    /**
     * Loads the payments activities using the filters values.
     *
     * A loading state is also updated before and after the activities have
     * been loaded.
     *
     * @param {object} filters parameters to pass to the Activity endpoint.
     */
    function filterPayments (filters) {
      var realNameFilters = getFiltersRealNamesAndValues(filters);
      $scope.isLoading = true;
      $scope.paging.isDisabled = true;

      getPaymentActivitiesRequests(realNameFilters)
        .then(function (results) {
          $scope.isLoading = false;
          customFields = results.customFields.values;
          $scope.payments = _.map(results.payments.values, formatPayment);
          $scope.paging.total = results.total;
          $scope.paging.isDisabled = false;
        });
    }

    /**
     * @param {object} payment payment object.
     * @returns {object} The same payment object including custom fields in
     *   `custom_Name` form instead of `custom_1` and the target contact name is
     *   a string instead of an object.
     */
    function formatPayment (payment) {
      var paymentCustomFields = getCustomFieldsAsNamesAndValues(payment);
      var targetContactName = _.chain(payment.target_contact_name)
        .toArray().first().value();

      return _.extend(
        {},
        payment,
        paymentCustomFields,
        {
          target_contact_name: targetContactName,
          paymentTypeLabel: paymentTypes[paymentCustomFields.custom_Type].label
        }
      );
    }

    /**
     * @param {object} payment payment object.
     * @returns {object} the payment custom fields in `custom_Name` form instead
     *   of `custom_1`.
     */
    function getCustomFieldsAsNamesAndValues (payment) {
      return _.transform(customFields, function (paymentCustomFields, customField) {
        var newFieldName = 'custom_' + customField.name;
        var oldFieldName = 'custom_' + customField.id;

        paymentCustomFields[newFieldName] = payment[oldFieldName];

        return paymentCustomFields;
      }, {});
    }

    /**
     * This transform filter keys such as `custom_Type` into `custom_123` where
     * `123` is the ID of the custom field which is what the API understands.
     *
     * @param {object} filters a map of filters.
     * @returns {object} The filters with the proper field names for custom fields.
     */
    function getFiltersRealNamesAndValues (filters) {
      return _.transform(filters, function (realNameFilters, filterValue, filterName) {
        if (_.startsWith(filterName, 'custom_')) {
          var customFieldName = filterName.replace('custom_', '');
          var customField = _.find(customFields, { name: customFieldName });
          var customFieldIdName = 'custom_' + customField.id;

          realNameFilters[customFieldIdName] = filterValue;
        } else {
          realNameFilters[filterName] = filterValue;
        }

        return realNameFilters;
      }, {});
    }

    /**
     * @param {object} activityFilters list of parameters to use for filtering
     *   the payment activities.
     * @returns {Promise<object>} resolves to payment activites and custom
     *   fields for payment activities.
     */
    function getPaymentActivitiesRequests (activityFilters) {
      var parameters = _.extend(
        {},
        {
          activity_type_id: 'Awards Payment',
          case_id: $scope.caseItem.id
        },
        _.pick(activityFilters, _.identity)
      );
      var activityParameters = _.extend({}, parameters, {
        sequential: 1,
        return: ['id', 'target_contact_id', 'status_id.label',
          'activity_date_time', 'custom', 'status_id.name'],
        options: {
          offset: ($scope.paging.page - 1) * $scope.paging.pageSize,
          limit: $scope.paging.pageSize
        }
      });

      return civicaseCrmApi({
        payments: ['Activity', 'get', activityParameters],
        total: ['Activity', 'getcount', parameters],
        customFields: ['CustomField', 'get', {
          custom_group_id: 'Awards_Payment_Information'
        }]
      });
    }

    /**
     * Loads the payments belonging to the given page. Respect current selected
     * filters.
     *
     * @param {string} pageNumber the page number to navigate to.
     */
    function goToPage (pageNumber) {
      $scope.paging.page = pageNumber;

      filterPayments(currentFilters);
    }

    /**
     * Accepts payment filters and loads the payments that match
     * the given filters. It will always show the first filtered page by
     * default. The filters are stored temporarily in case the values are
     * needed for refreshing the payments list.
     *
     * @param {object} filters parameters used to filter the payments.
     */
    function submitFilters (filters) {
      currentFilters = filters;
      $scope.paging.page = 1;

      filterPayments(filters);
    }
  }
})(CRM._, angular);
