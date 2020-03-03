/* eslint-env jasmine */

(function (_) {
  describe('More Filters Dashboard Action Button', () => {
    let $q, $location, $scope, $controller, $rootScope, dialogService,
      crmApiMock, crmApi;

    beforeEach(module('civiawards', function ($provide) {
      crmApiMock = jasmine.createSpy();

      $provide.value('crmApi', crmApiMock);
      $provide.value('ts', jasmine.createSpy());
    }));

    beforeEach(inject((_$q_, _crmApi_, _$controller_, _$rootScope_, _$location_, _dialogService_) => {
      $q = _$q_;
      $controller = _$controller_;
      $location = _$location_;
      $rootScope = _$rootScope_;
      dialogService = _dialogService_;
      crmApi = _crmApi_;

      spyOn($rootScope, '$broadcast').and.callThrough();

      dialogService.dialogs = {};
      crmApiMock.and.returnValue($q.resolve({
        values: [{
          case_type_id: 1
        }, {
          case_type_id: 2
        }]
      }));

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
        $scope.openMoreFiltersDialog();
      });

      it('opens a popup to select filters', () => {
        expect(dialogService.open).toHaveBeenCalledWith('MoreFilters', '~/civiawards/dashboard/directives/more-filters-popup.html', jasmine.any(Object), {
          autoOpen: false,
          height: 'auto',
          width: '350px',
          title: 'More Filters'
        });
      });

      describe('when the action button is clicked again before closing the button', () => {
        beforeEach(() => {
          dialogService.dialogs.MoreFilters = true;
          $scope.openMoreFiltersDialog();
        });

        it('does not reopen the more filter popup', () => {
          expect(dialogService.open.calls.count()).toBe(1);
        });
      });
    });

    describe('when filters are applied', () => {
      var dialogModel;

      beforeEach(() => {
        dialogService.close = jasmine.createSpy('');
        dialogService.open = function (__, templateName, model) {
          dialogModel = model;
        };
        $scope.openMoreFiltersDialog();
      });

      describe('when my awards filter is selected', () => {
        describe('when status filter is selected', () => {
          beforeEach(() => {
            dialogModel.selectedFilters.awardFilter = 'my_awards';
            dialogModel.selectedFilters.start_date = '10/12/2019';
            dialogModel.selectedFilters.end_date = '15/12/2019';
            dialogModel.selectedFilters.award_types = '1,2';
            dialogModel.selectedFilters.statuses = '3,4';
            dialogModel.applyFilterAndCloseDialog();
            $rootScope.$digest();
          });

          it('shows the awards where the logged in user is the manager and also applies the rest of filters', () => {
            expect(crmApi).toHaveBeenCalledWith('AwardManager', 'get', { sequential: 1, contact_id: 203 });
            expect(crmApi).toHaveBeenCalledWith('AwardDetail', 'get', {
              sequential: 1,
              start_date: '10/12/2019',
              end_date: '15/12/2019',
              case_type_id: { IN: [1, 2] },
              award_type: { IN: ['1', '2'] }
            });
            expect($rootScope.$broadcast).toHaveBeenCalledWith('civicase::dashboard-filters::updated', {
              case_type_id: { IN: [1, 2] },
              status_id: { IN: ['3', '4'] }
            });
          });
        });

        describe('when status filter is not selected', () => {
          beforeEach(() => {
            dialogModel.selectedFilters.statuses = '';
            dialogModel.applyFilterAndCloseDialog();
            $rootScope.$digest();
          });

          it('does not hide all cases and activities', () => {
            expect($rootScope.$broadcast).toHaveBeenCalledWith('civicase::dashboard-filters::updated', jasmine.objectContaining({
              status_id: { 'IS NOT NULL': 1 }
            }));
          });
        });
      });

      describe('when all awards filter is selected', () => {
        beforeEach(() => {
          dialogModel.selectedFilters.awardFilter = 'all_awards';
          dialogModel.applyFilterAndCloseDialog();
        });

        it('shows the all the awards', () => {
          expect(crmApi).toHaveBeenCalledWith('AwardManager', 'get', { sequential: 1 });
        });
      });

      describe('when filters are not changed', () => {
        beforeEach(() => {
          dialogModel.selectedFilters.awardFilter = 'my_awards';
          dialogModel.selectedFilters.statuses = '';
          dialogModel.selectedFilters.award_types = '';
          dialogModel.selectedFilters.start_date = null;
          dialogModel.selectedFilters.end_date = null;
          dialogModel.applyFilterAndCloseDialog();
        });

        it('does not show a red dot inside the more filters button', () => {
          expect($scope.isNotificationVisible()).toBe(false);
        });
      });

      describe('when any of the filters are changed', () => {
        beforeEach(() => {
          dialogModel.selectedFilters.awardFilter = 'all_awards';
          dialogModel.selectedFilters.statuses = '';
          dialogModel.selectedFilters.award_types = '';
          dialogModel.selectedFilters.start_date = null;
          dialogModel.selectedFilters.end_date = null;
          dialogModel.applyFilterAndCloseDialog();
        });

        it('shows a red dot inside the more filters button', () => {
          expect($scope.isNotificationVisible()).toBe(true);
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
