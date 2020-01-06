/* eslint-env jasmine */

(function (_, getCrmUrl) {
  describe('Add Award Dashboard Action Button', () => {
    let $location, $window, $scope, $controller, $rootScope;

    beforeEach(module('civiawards', ($provide) => {
      $window = { location: { href: '' } };

      $provide.value('$window', $window);
    }));

    beforeEach(inject((_$location_, _$controller_, _$rootScope_) => {
      $location = _$location_;
      $controller = _$controller_;
      $rootScope = _$rootScope_;

      initController();
    }));

    describe('button visibility', () => {
      var isButtonVisible;

      describe('when viewing the awards dashboard', () => {
        beforeEach(() => {
          $location.search('case_type_category', 'awards');

          isButtonVisible = $scope.isVisible();
        });

        it('displays the add award button', () => {
          expect(isButtonVisible).toBe(true);
        });
      });

      describe('when viewing any other dashboard', () => {
        beforeEach(() => {
          $location.search('case_type_category', 'cases');

          isButtonVisible = $scope.isVisible();
        });

        it('does not display the add award button', () => {
          expect(isButtonVisible).toBe(false);
        });
      });
    });

    describe('when clicking the action button', () => {
      const expectedUrl = getCrmUrl('civicrm/a/#/awards/new');

      beforeEach(() => {
        spyOn($location, 'url');
        $scope.redirectToAwardsCreationScreen();
      });

      it('redirects the user to the create award screen', () => {
        expect($window.location.href).toBe(expectedUrl);
      });
    });

    /**
     * Initializes the add award controller.
     */
    function initController () {
      $scope = $rootScope.$new();

      $controller('AddAwardDashboardActionButtonController', { $scope: $scope });
    }
  });
})(CRM._, CRM.url);
