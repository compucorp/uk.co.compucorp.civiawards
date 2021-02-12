/* eslint-env jasmine */

describe('AwardsPaymentActivityStatus', () => {
  let AwardsPaymentActivityStatus;

  beforeEach(module('civiawards-payments-tab'));

  beforeEach(inject((_AwardsPaymentActivityStatus_) => {
    AwardsPaymentActivityStatus = _AwardsPaymentActivityStatus_;
  }));

  describe('when payment is complete', () => {
    it('hides the delete action', () => {
      expect(AwardsPaymentActivityStatus.isDeleteVisible({ status_name: 'paid_complete' }))
        .toBe(false);
    });
  });

  describe('when payment has failed', () => {
    it('hides the delete action', () => {
      expect(AwardsPaymentActivityStatus.isDeleteVisible({ status_name: 'failed_incomplete' }))
        .toBe(false);
    });
  });

  describe('when payment has been exported', () => {
    it('hides the delete action', () => {
      expect(AwardsPaymentActivityStatus.isDeleteVisible({ status_name: 'exported_complete' }))
        .toBe(false);
    });
  });

  describe('when payment is not complete, failed or exported', () => {
    it('hides the delete action', () => {
      expect(AwardsPaymentActivityStatus.isDeleteVisible({ status_name: 'applied_for_incomplete' }))
        .toBe(true);
    });
  });
});
