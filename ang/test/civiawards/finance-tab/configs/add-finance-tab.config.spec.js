/* eslint-env jasmine */

describe('AddFinanceTabConfig', () => {
  let CaseDetailsTabsProvider, CaseTypeCategoryProvider, ts;
  const mockInstances = {
    'mock-instance-4': { value: 4 },
    'mock-instance-5': { value: 5 }
  };

  describe('when the current instance support finances', () => {
    beforeEach(() => setupModulesAndDependencies({
      currentCaseCategory: 'mock-instance-4'
    }));

    it('adds the finance tab', () => {
      expect(CaseDetailsTabsProvider.addTabs).toHaveBeenCalledWith([
        {
          name: 'Finance',
          label: ts('Finance'),
          weight: 101
        }
      ]);
    });
  });

  describe('when the current instance does not support finances', () => {
    beforeEach(() => setupModulesAndDependencies({
      currentCaseCategory: 'mock-instance-5'
    }));

    it('does not add the finance tab', () => {
      expect(CaseDetailsTabsProvider.addTabs).not.toHaveBeenCalled();
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
      CaseTypeCategoryProvider = jasmine.createSpyObj('CaseTypeCategoryProvider', [
        '$get',
        'findByName',
        'findAllByInstance'
      ]);

      CaseTypeCategoryProvider.findByName.and.callFake((instanceName) => {
        return mockInstances[instanceName];
      });

      $provide.provider('CaseDetailsTabs', CaseDetailsTabsProvider);
      $provide.provider('CaseTypeCategory', CaseTypeCategoryProvider);
      $provide.constant('currentCaseCategory', mockValues.currentCaseCategory);
    }, 'civiawards');

    inject((_ts_) => {
      ts = _ts_;
    });
  }
});
