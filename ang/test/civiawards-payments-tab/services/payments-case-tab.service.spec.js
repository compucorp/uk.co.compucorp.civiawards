describe('PaymentsCaseTab', () => {
  let PaymentsCaseTab;

  beforeEach(module('civiawards-payments-tab'));

  beforeEach(inject((_PaymentsCaseTab_) => {
    PaymentsCaseTab = _PaymentsCaseTab_;
  }));

  describe('when getting the template for the Payments tab', () => {
    it('returns the path to the Payments tab template', () => {
      expect(PaymentsCaseTab.activeTabContentUrl())
        .toBe('~/civiawards-payments-tab/services/payments-case-tab-content.html');
    });
  });
});
