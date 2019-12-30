/* eslint-env jasmine */

(function (_) {
  describe('More Filters Dashboard Action Button', () => {
    let $q, $location, $scope, $controller, $rootScope, dialogService,
      crmApiMock;

    beforeEach(module('civiawards', function ($provide) {
      crmApiMock = jasmine.createSpy();

      $provide.value('crmApi', crmApiMock);
      $provide.value('ts', jasmine.createSpy());
    }));

    beforeEach(inject((_$q_, _$controller_, _$rootScope_, _$location_, _dialogService_) => {
      $q = _$q_;
      $controller = _$controller_;
      $location = _$location_;
      $rootScope = _$rootScope_;
      dialogService = _dialogService_;

      crmApiMock.and.returnValue($q.resolve());

      initController();
    }));

    describe('button visibility', () => {
      var isButtonVisible;

      describe('when viewing the awards dashboard', () => {
        beforeEach(() => {
          $location.search('case_type_category', 'awards');

          isButtonVisible = $scope.isVisible();
        });

        it('displays the more filter button', () => {
          expect(isButtonVisible).toBe(true);
        });
      });

      describe('when viewing any other dashboard', () => {
        beforeEach(() => {
          $location.search('case_type_category', 'cases');

          isButtonVisible = $scope.isVisible();
        });

        it('does not display the more filter button', () => {
          expect(isButtonVisible).toBe(false);
        });
      });
    });

    describe('when clicking the action button', () => {
      beforeEach(() => {
        dialogService.open = jasmine.createSpy();
        $scope.clickHandler();
      });

      it('opens a popup to select filters', () => {
        expect(dialogService.open).toHaveBeenCalledWith('MoreFilters', '~/civiawards/dashboard/directives/more-filters-popup.html', jasmine.any(Object), {
          autoOpen: false,
          height: 'auto',
          width: '350px',
          title: 'More Filters'
        });
      });
    });

    /**
     * Initializes the more filters controller.
     */
    function initController () {
      $scope = $rootScope.$new();

      $controller('MoreFiltersDashboardActionButtonController', { $scope: $scope });
    }
  });
})(CRM._);
