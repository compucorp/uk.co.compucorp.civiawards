/* eslint-env jasmine */

(function (_, getCrmUrl) {
  describe('Add Award Dashboard Action Button', () => {
    let $location, $window, $scope, $controller, $rootScope, canCreateOrEditAwards, isApplicationManagementScreen;

    beforeEach(module('civiawards', ($provide) => {
      $window = { location: { href: '' } };
      canCreateOrEditAwards = jasmine.createSpy('canCreateOrEditAwards');
      isApplicationManagementScreen = jasmine.createSpy('isApplicationManagementScreen');

      $provide.value('$window', $window);
      $provide.value('$routeParams', { case_type_category: 'awards' });
      $provide.value('canCreateOrEditAwards', canCreateOrEditAwards);
      $provide.value('isApplicationManagementScreen', isApplicationManagementScreen);
    }));

    beforeEach(inject((_$location_, _$controller_, _$rootScope_) => {
      $location = _$location_;
      $controller = _$controller_;
      $rootScope = _$rootScope_;

      initController();
    }));

    describe('button visibility', () => {
      var isButtonVisible;

      describe('when viewing the application management dashboard', () => {
        beforeEach(() => {
          isApplicationManagementScreen.and.returnValue(true);
        });

        describe('and the user can create awards', () => {
          beforeEach(() => {
            canCreateOrEditAwards.and.returnValue(true);

            isButtonVisible = $scope.isVisible();
          });

          it('displays the add award button', () => {
            expect(isButtonVisible).toBe(true);
          });
        });

        describe('and the user cannot create awards', () => {
          beforeEach(() => {
            canCreateOrEditAwards.and.returnValue(false);

            isButtonVisible = $scope.isVisible();
          });

          it('does not display the add award button', () => {
            expect(isButtonVisible).toBe(false);
          });
        });
      });

      describe('when viewing any other dashboard', () => {
        beforeEach(() => {
          isApplicationManagementScreen.and.returnValue(false);

          isButtonVisible = $scope.isVisible();
        });

        it('does not display the add award button', () => {
          expect(isButtonVisible).toBe(false);
        });
      });
    });

    describe('when clicking the action button', () => {
      const awardCaseTypeCategoryId = 3;
      const expectedUrl = getCrmUrl('civicrm/award/a/#/awards/new/' + awardCaseTypeCategoryId + '/dashboard');

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
