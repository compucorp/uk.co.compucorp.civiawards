/* eslint-env jasmine */
(function () {
  describe('isAwardsScreen', function () {
    let $location, isAwardsScreen;

    beforeEach(module('civiawards', ($provide) => {
      $location = jasmine.createSpyObj('$location', ['search']);

      $provide.value('$location', $location);
    }));

    beforeEach(inject((_isAwardsScreen_) => {
      isAwardsScreen = _isAwardsScreen_;
    }));

    describe('when the user is viewing an award screen', () => {
      beforeEach(() => {
        $location.search.and.returnValue({
          case_type_category: 'awards'
        });
      });

      it('returns true', () => {
        expect(isAwardsScreen()).toBe(true);
      });
    });

    describe('when the user is viewing a non award screen', () => {
      beforeEach(() => {
        $location.search.and.returnValue({
          case_type_category: 'case'
        });
      });

      it('returns false', () => {
        expect(isAwardsScreen()).toBe(false);
      });
    });
  });
}());
