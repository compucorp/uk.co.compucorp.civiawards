/* eslint-env jasmine */
(function () {
  describe('isAwardScreen', function () {
    let $location, isAwardScreen;

    beforeEach(module('civiawards', ($provide) => {
      $location = jasmine.createSpyObj('$location', ['search']);

      $provide.value('$location', $location);
    }));

    beforeEach(inject((_isAwardScreen_) => {
      isAwardScreen = _isAwardScreen_;
    }));

    describe('when the user is viewing an award screen', () => {
      beforeEach(() => {
        $location.search.and.returnValue({
          case_type_category: 'awards'
        });
      });

      it('returns true', () => {
        expect(isAwardScreen()).toBe(true);
      });
    });

    describe('when the user is viewing a non award screen', () => {
      beforeEach(() => {
        $location.search.and.returnValue({
          case_type_category: 'case'
        });
      });

      it('returns false', () => {
        expect(isAwardScreen()).toBe(false);
      });
    });
  });
}());
