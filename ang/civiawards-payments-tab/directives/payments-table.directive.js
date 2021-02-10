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
    var customFields = [];

    $scope.isLoading = false;

    $scope.filterPayments = filterPayments;

    (function init () {
      filterPayments();
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

      loadPaymentActivitiesAndCustomFields(realNameFilters)
        .then(function (results) {
          $scope.isLoading = false;
          customFields = results.customFields.values;
          $scope.payments = _.map(results.payments.values, formatPayment);
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
        var oldFiledName = 'custom_' + customField.id;

        paymentCustomFields[newFieldName] = payment[oldFiledName];

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
    function loadPaymentActivitiesAndCustomFields (activityFilters) {
      var paymentDefaultFilters = {
        sequential: 1,
        activity_type_id: 'Awards Payment',
        case_id: $scope.caseItem.id,
        return: ['id', 'target_contact_id', 'status_id.label',
          'activity_date_time', 'custom'],
        options: { limit: 0 }
      };

      return civicaseCrmApi({
        payments: ['Activity', 'get', _.extend(
          {},
          paymentDefaultFilters,
          _.pick(activityFilters, _.identity)
        )],
        customFields: ['CustomField', 'get', {
          custom_group_id: 'Awards_Payment_Information'
        }]
      });
    }
  }
})(CRM._, angular);
