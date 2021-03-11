(() => {
  describe('Reviews Tab configuration', () => {
    let $window, CaseDetailsTabsProvider;

    beforeEach(module('civicase-base', ($provide, _CaseDetailsTabsProvider_) => {
      CaseDetailsTabsProvider = _CaseDetailsTabsProvider_;
      $window = {
        location: {
          search: ''
        }
      };

      $provide.value('$window', $window);
      spyOn(CaseDetailsTabsProvider, 'addTabs');
    }));

    describe('when viewing award applications', () => {
      beforeEach(module(() => {
        $window.location.search = '?case_type_category=awards';
      }, 'civiawards'));

      beforeEach(inject);

      it('adds the awards reviews tab', () => {
        expect(CaseDetailsTabsProvider.addTabs).toHaveBeenCalledWith([
          {
            name: 'Reviews',
            label: 'Reviews',
            weight: 100
          }
        ]);
      });
    });

    describe('when viewing cases', () => {
      beforeEach(module(() => {
        $window.location.search = '?case_type_category=case';
      }, 'civiawards'));

      beforeEach(inject);

      it('does not add the awards reviews tab', () => {
        expect(CaseDetailsTabsProvider.addTabs).not.toHaveBeenCalled();
      });
    });
  });
})();
