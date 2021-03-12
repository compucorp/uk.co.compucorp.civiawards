((_) => {
  describe('PaymentsActivityForm', () => {
    let PaymentsActivityForm, canHandle, civicaseCrmUrl, paymentActivity;

    beforeEach(module('civiawards-payments-tab', 'civiawards-payments-tab.mocks'));

    beforeEach(inject((_PaymentsActivityForm_,
      _civicaseCrmUrl_, _mockPayments_) => {
      PaymentsActivityForm = _PaymentsActivityForm_;
      civicaseCrmUrl = _civicaseCrmUrl_;

      paymentActivity = _.chain(_mockPayments_)
        .first()
        .cloneDeep()
        .value();
    }));

    describe('handling activity forms', () => {
      describe('when handling a payment activity type', () => {
        beforeEach(() => {
          canHandle = PaymentsActivityForm.canHandleActivity(paymentActivity);
        });

        it('can handle the application activity', () => {
          expect(canHandle).toBe(true);
        });
      });

      describe('when handling a non payment activity type', () => {
        beforeEach(() => {
          paymentActivity.type = 'Not Awards Payment';
          canHandle = PaymentsActivityForm.canHandleActivity(paymentActivity);
        });

        it('cant handle the application activity', () => {
          expect(canHandle).toBe(false);
        });
      });
    });

    describe('when getting the activity form url', () => {
      beforeEach(() => {
        PaymentsActivityForm.getActivityFormUrl(paymentActivity);
      });

      it('returns the url for the payment activity form', () => {
        expect(civicaseCrmUrl).toHaveBeenCalledWith('civicrm/awardpayment', {
          action: 'view',
          reset: 1,
          id: paymentActivity.id
        });
      });
    });
  });
})(CRM._);
