/* eslint-env jasmine */

(($, _) => {
  describe('PaymentsCaseTabActions', () => {
    let $q, $controller, $rootScope, $scope, mockPayments, crmStatus,
      civicaseCrmApi, mockedConfirmElement, loadFormOnListener,
      crmFormSuccessFunction, civicaseCrmLoadForm;
    const CRM_FORM_SUCCESS_EVENT = 'crmFormSuccess.crmPopup crmPopupFormSuccess.crmPopup';

    beforeEach(module('civiawards-payments-tab', ($provide) => {
      civicaseCrmApi = jasmine.createSpy('civicaseCrmApi');

      $provide.value('civicaseCrmApi', civicaseCrmApi);

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

    beforeEach(inject((_$q_, _$controller_, _$rootScope_, _crmStatus_,
      _civicaseCrmLoadForm_) => {
      $q = _$q_;
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      crmStatus = _crmStatus_;
      civicaseCrmLoadForm = _civicaseCrmLoadForm_;

      civicaseCrmApi.and.returnValue($q.resolve());

      mockPayments = {
        id: _.uniqueId(),
        custom_11: 'Stipend/Salary',
        custom_12: 'GBP',
        custom_13: '1099',
        custom_14: 'N9TT-9G0A-B7FQ-RANC',
        activity_date_time: '2000-01-01 12:00:00',
        target_contact_name: { 10: 'Jon Snow' },
        'status_id.label': 'paid_complete'
      };

      initController();

      mockedConfirmElement = $('<span></span>');

      CRM.confirm.and.returnValue(mockedConfirmElement);

      spyOn($rootScope, '$broadcast');
    }));

    describe('when viewing a payment', () => {
      beforeEach(() => {
        $scope.handleViewActivity(mockPayments.id);
      });

      it('opens a popup to view the activity', () => {
        expect(civicaseCrmLoadForm)
          .toHaveBeenCalledWith(`civicrm/awardpayment?action=view&id=${mockPayments.id}&reset=1`);
      });
    });

    describe('when editing a payment', () => {
      beforeEach(() => {
        $scope.handleEditActivity(mockPayments.id);
      });

      it('opens a popup to edit the activity', () => {
        expect(civicaseCrmLoadForm)
          .toHaveBeenCalledWith(`civicrm/awardpayment?action=update&id=${mockPayments.id}&reset=1`);
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

    describe('when deleting a payment', () => {
      beforeEach(() => {
        $scope.handleDeleteActivity(mockPayments.id);
      });

      it('displays a confirmation prompt', () => {
        expect(CRM.confirm).toHaveBeenCalledWith({ title: 'Delete Payment' });
      });

      describe('when confirming the payment deletion', () => {
        beforeEach(() => {
          mockedConfirmElement.trigger('crmConfirm:yes');
          $rootScope.$digest();
        });

        it('deletes the selected payment', () => {
          expect(civicaseCrmApi).toHaveBeenCalledWith('Activity', 'delete', {
            id: mockPayments.id
          });
        });

        it('reloads the payments', () => {
          expect($rootScope.$broadcast).toHaveBeenCalledWith('civiawards::paymentstable::refresh');
        });

        it('shows a loading progress', () => {
          expect(crmStatus).toHaveBeenCalledWith({
            start: 'Deleting...',
            success: 'Deleted'
          }, jasmine.any(Object));
        });
      });
    });
    /**
     * Initialises the add payments case tab actions controller.
     */
    function initController () {
      $scope = $rootScope.$new();
      $scope.payment = mockPayments;

      $controller('civiawardsPaymentsCaseTabActionsController', {
        $scope
      });
    }
  });
})(CRM.$, CRM._);
