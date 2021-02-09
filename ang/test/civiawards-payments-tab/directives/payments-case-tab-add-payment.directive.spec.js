/* eslint-env jasmine */

((_) => {
  describe('PaymentsCaseTabAddPaymnent', () => {
    let $controller, $rootScope, $scope, civicaseCrmLoadForm,
      loadFormOnListener, crmFormSuccessFunction;

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

    beforeEach(inject((_$controller_, _$rootScope_, _civicaseCrmLoadForm_) => {
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      civicaseCrmLoadForm = _civicaseCrmLoadForm_;

      spyOn($rootScope, '$broadcast');
    }));

    describe('controller definition', () => {
      it('defines the controller', () => {
        expect(() => initController()).not.toThrow();
      });
    });

    describe('when clicking on add payment button', () => {
      beforeEach(() => {
        initController();
        $scope.addPayment($scope.caseId);
      });

      it('opens a popup to create a new payment for current application', () => {
        expect(civicaseCrmLoadForm)
          .toHaveBeenCalledWith('civicrm/awardpayment?action=add&case_id=5&reset=1');
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

      $controller('civiawardsPaymentsCaseTabAddPaymnentController', {
        $scope
      });
    }
  });
})(CRM._);
