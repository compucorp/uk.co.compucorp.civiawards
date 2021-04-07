(function () {
  describe('isApplicationManagementScreen', function () {
    let $location, isApplicationManagementScreen;

    beforeEach(module('civiawards-base', ($provide) => {
      $location = jasmine.createSpyObj('$location', ['search']);

      $provide.value('$location', $location);
    }));

    beforeEach(inject((_isApplicationManagementScreen_) => {
      isApplicationManagementScreen = _isApplicationManagementScreen_;
    }));

    describe('when the user is viewing an applicant management screen', () => {
      beforeEach(() => {
        $location.search.and.returnValue({
          case_type_category: '3'
        });
      });

      it('returns true', () => {
        expect(isApplicationManagementScreen()).toBe(true);
      });
    });

    describe('when the user is viewing a non applicant management screen', () => {
      beforeEach(() => {
        $location.search.and.returnValue({
          case_type_category: '1'
        });
      });

      it('returns false', () => {
        expect(!!isApplicationManagementScreen()).toBe(false);
      });
    });
  });
}());
