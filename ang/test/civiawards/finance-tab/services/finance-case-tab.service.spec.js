/* eslint-env jasmine */

describe('FinanceCaseTab', () => {
  let FinanceCaseTab;

  beforeEach(module('civiawards'));

  beforeEach(inject((_FinanceCaseTab_) => {
    FinanceCaseTab = _FinanceCaseTab_;
  }));

  describe('when getting the template for the Finance tab', () => {
    it('returns the path to the Finance tab template', () => {
      expect(FinanceCaseTab.activeTabContentUrl())
        .toBe('~/civiawards/finance-tab/services/finance-case-tab-content.html');
    });
  });
});
