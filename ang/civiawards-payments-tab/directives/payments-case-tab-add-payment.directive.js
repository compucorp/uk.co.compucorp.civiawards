(function (angular) {
  var module = angular.module('civiawards-payments-tab');

  module.directive('civiawardsPaymentsCaseTabAddPayment', function () {
    return {
      templateUrl: '~/civiawards-payments-tab/directives/payments-case-tab-add-payment.directive.html',
      restrict: 'E',
      scope: {
        caseId: '<'
      },
      controller: 'civiawardsPaymentsCaseTabAddPaymentController'
    };
  });

  module.controller('civiawardsPaymentsCaseTabAddPaymentController', civiawardsPaymentsCaseTabAddPaymentController);

  /**
   * @param {object} $scope scope of the controller
   * @param {object} $rootScope rootscope of the application
   * @param {Function} civicaseCrmLoadForm crm load form service
   */
  function civiawardsPaymentsCaseTabAddPaymentController ($scope, $rootScope,
    civicaseCrmLoadForm) {
    var CRM_FORM_SUCCESS_EVENT = 'crmFormSuccess.crmPopup crmPopupFormSuccess.crmPopup';

    $scope.addPayment = addPayment;

    /**
     * Open Add Payment Form
     *
     * @param {string} caseId case id
     */
    function addPayment (caseId) {
      var url = CRM.url('civicrm/awardpayment', {
        action: 'add',
        reset: 1,
        case_id: caseId
      });

      civicaseCrmLoadForm(url)
        .on(CRM_FORM_SUCCESS_EVENT, function () {
          $rootScope.$broadcast('civiawards::paymentstable::refresh');
        });
    }
  }
})(angular);
