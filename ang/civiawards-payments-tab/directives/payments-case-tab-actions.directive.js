(function (angular, confirm) {
  var module = angular.module('civiawards-payments-tab');

  module.directive('civiawardsPaymentsCaseTabActions', function () {
    return {
      templateUrl: '~/civiawards-payments-tab/directives/payments-case-tab-actions.directive.html',
      restrict: 'E',
      scope: {
        payment: '<'
      },
      controller: 'civiawardsPaymentsCaseTabActionsController'
    };
  });

  module.controller('civiawardsPaymentsCaseTabActionsController', civiawardsPaymentsCaseTabActionsController);

  /**
   * @param {Function} ts translation service
   * @param {object} $scope scope of the controller
   * @param {object} $rootScope rootscope of the application
   * @param {Function} civicaseCrmApi service to use civicrm api
   * @param {Function} crmStatus crm status service
   * @param {Function} civicaseCrmLoadForm crm load form service
   * @param {object} AwardsPaymentActivityStatus awards payment activity status service
   * @param {Function} civicaseCrmUrl civicrm url service
   * @param {object} PaymentsActivityForm payments activity form service
   */
  function civiawardsPaymentsCaseTabActionsController (
    ts, $scope, $rootScope, civicaseCrmApi, crmStatus, civicaseCrmLoadForm,
    AwardsPaymentActivityStatus, civicaseCrmUrl, PaymentsActivityForm) {
    var CRM_FORM_SUCCESS_EVENT = 'crmFormSuccess.crmPopup crmPopupFormSuccess.crmPopup';

    $scope.handleViewActivity = handleViewActivity;
    $scope.handleEditActivity = handleEditActivity;
    $scope.handleDeleteActivity = handleDeleteActivity;
    $scope.isDeleteActionVisible = isDeleteActionVisible;
    $scope.isEditActionVisible = isEditActionVisible;

    /**
     * Open View Payment Form
     *
     * @param {string} paymentId payment id
     */
    function handleViewActivity (paymentId) {
      var url = PaymentsActivityForm.getActivityFormUrl({ id: paymentId });

      civicaseCrmLoadForm(url);
    }

    /**
     * Open Edit Payment Form
     *
     * @param {string} paymentId payment id
     */
    function handleEditActivity (paymentId) {
      var url = civicaseCrmUrl('civicrm/awardpayment', {
        action: 'update',
        reset: 1,
        id: paymentId
      });

      civicaseCrmLoadForm(url)
        .on(CRM_FORM_SUCCESS_EVENT, function () {
          $rootScope.$broadcast('civiawards::paymentstable::refresh');
        });
    }

    /**
     * Delete Payment
     *
     * @param {string} paymentId payment id
     */
    function handleDeleteActivity (paymentId) {
      confirm({ title: ts('Delete Payment') })
        .on('crmConfirm:yes', function () {
          var promise = deletePaymentActivity(paymentId)
            .then(function () {
              $rootScope.$broadcast('civiawards::paymentstable::refresh');
            });

          return crmStatus({
            start: $scope.ts('Deleting...'),
            success: $scope.ts('Deleted')
          }, promise);
        });
    }

    /**
     * Deletes the given payment.
     *
     * @param {number} paymentId the payment's id.
     * @returns {Promise} resolves after the payment has been deleted.
     */
    function deletePaymentActivity (paymentId) {
      return civicaseCrmApi('Activity', 'delete', {
        id: paymentId
      });
    }

    /**
     * Checks if the delete action should be visible.
     *
     * @param {object} payment payment activity object
     * @returns {boolean} if visible
     */
    function isDeleteActionVisible (payment) {
      return AwardsPaymentActivityStatus.isDeleteVisible({ status_name: payment['status_id.name'] });
    }

    /**
     * Checks if the delete action should be visible.
     *
     * @param {object} payment payment activity object
     * @returns {boolean} if visible
     */
    function isEditActionVisible (payment) {
      return AwardsPaymentActivityStatus.isEditVisible({ status_name: payment['status_id.name'] });
    }
  }
})(angular, CRM.confirm);
