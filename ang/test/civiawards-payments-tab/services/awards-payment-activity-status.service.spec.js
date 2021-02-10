/* eslint-env jasmine */

describe('AwardsPaymentActivityStatus', () => {
  let AwardsPaymentActivityStatus;

  beforeEach(module('civiawards-payments-tab'));

  beforeEach(inject((_AwardsPaymentActivityStatus_) => {
    AwardsPaymentActivityStatus = _AwardsPaymentActivityStatus_;
  }));

  describe('when payment is complete', () => {
    it('delete action is not visible', () => {
      expect(AwardsPaymentActivityStatus.isDeleteVisible({ status_name: 'paid_complete' }))
        .toBe(false);
    });
  });

  describe('when payment has failed', () => {
    it('delete action is not visible', () => {
      expect(AwardsPaymentActivityStatus.isDeleteVisible({ status_name: 'failed_incomplete' }))
        .toBe(false);
    });
  });

  describe('when payment has been exported', () => {
    it('delete action is not visible', () => {
      expect(AwardsPaymentActivityStatus.isDeleteVisible({ status_name: 'exported_complete' }))
        .toBe(false);
    });
  });

  describe('when payment is not complete, failed or exported', () => {
    it('delete action is visible', () => {
      expect(AwardsPaymentActivityStatus.isDeleteVisible({ status_name: 'applied_for_incomplete' }))
        .toBe(true);
    });
  });
});
