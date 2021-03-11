describe('AddPaymentsTabConfig', () => {
  let CaseDetailsTabsProvider, ts;

  describe('when the module is configured', () => {
    beforeEach(() => setupModulesAndDependencies({
      currentCaseCategory: 'mock-instance-4'
    }));

    it('adds the payments tab', () => {
      expect(CaseDetailsTabsProvider.addTabs).toHaveBeenCalledWith([
        {
          name: 'Payments',
          label: ts('Payments'),
          weight: 101
        }
      ]);
    });
  });

  /**
   * Setups the civiaward modules and injects mock dependencies.
   *
   * @param {object} mockValues mock values to replace the original definitions.
   */
  function setupModulesAndDependencies (mockValues = {}) {
    module('civicase-base', ($provide) => {
      CaseDetailsTabsProvider = jasmine.createSpyObj('CaseDetailsTabsProvider', [
        '$get',
        'addTabs'
      ]);

      $provide.provider('CaseDetailsTabs', CaseDetailsTabsProvider);
    }, 'civiawards-payments-tab');

    inject((_ts_) => {
      ts = _ts_;
    });
  }
});
