((_) => {
  describe('PaymentsCaseTabAddPayment', () => {
    let $controller, $rootScope, $scope, civicaseCrmLoadForm,
      loadFormOnListener, crmFormSuccessFunction, civicaseCrmUrl;

    const CRM_FORM_SUCCESS_EVENT = 'crmFormSuccess.crmPopup crmPopupFormSuccess.crmPopup';

    beforeEach(module('civiawards-payments-tab', ($provide) => {
      var civicaseCrmLoadFormSpy = jasmine.createSpy('loadForm');
      loadFormOnListener = jasmine.createSpyObj('', ['on']);
      loadFormOnListener.on.and.callFake(function () {
        if (arguments[0] === CRM_FORM_SUCCESS_EVENT) {
          crmFormSuccessFunction = arguments[1];
        }

        return loadFormOnListener;
      });

      civicaseCrmLoadFormSpy.and.returnValue(loadFormOnListener);
      $provide.service('civicaseCrmLoadForm', function () {
        return civicaseCrmLoadFormSpy;
      });
    }));

    beforeEach(inject((_$controller_, _$rootScope_, _civicaseCrmLoadForm_,
      _civicaseCrmUrl_) => {
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      civicaseCrmLoadForm = _civicaseCrmLoadForm_;
      civicaseCrmUrl = _civicaseCrmUrl_;

      spyOn($rootScope, '$broadcast');
    }));

    describe('controller definition', () => {
      it('defines the controller', () => {
        expect(() => initController()).not.toThrow();
      });
    });

    describe('when clicking on add payment button', () => {
      var expectedUrl = 'add/payment/url';

      beforeEach(() => {
        initController();
        civicaseCrmUrl.and.returnValue(expectedUrl);
        $scope.addPayment($scope.caseId);
      });

      it('opens a popup to create a new payment for current application', () => {
        expect(civicaseCrmUrl).toHaveBeenCalledWith('civicrm/awardpayment', {
          action: 'add',
          reset: 1,
          case_id: $scope.caseId
        });
        expect(civicaseCrmLoadForm).toHaveBeenCalledWith(expectedUrl);
      });

      describe('when saving the form', () => {
        beforeEach(() => {
          crmFormSuccessFunction();
        });

        it('reloads the payments', () => {
          expect($rootScope.$broadcast).toHaveBeenCalledWith('civiawards::paymentstable::refresh');
        });
      });
    });

    /**
     * Initialises the add payments controller.
     */
    function initController () {
      $scope = $rootScope.$new();
      $scope.caseId = '5';

      $controller('civiawardsPaymentsCaseTabAddPaymentController', {
        $scope
      });
    }
  });
})(CRM._);
