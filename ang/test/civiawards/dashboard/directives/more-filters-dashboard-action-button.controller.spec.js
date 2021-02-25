/* eslint-env jasmine */

(function (_) {
  describe('More Filters Dashboard Action Button', () => {
    let $q, $location, $scope, $controller, $rootScope, dialogService,
      civicaseCrmApiMock;

    beforeEach(module('civiawards', function ($provide) {
      civicaseCrmApiMock = jasmine.createSpy();

      $provide.value('civicaseCrmApi', civicaseCrmApiMock);
      $provide.value('ts', jasmine.createSpy());
    }));

    beforeEach(inject((_$q_, _$controller_, _$rootScope_, _$location_, _dialogService_) => {
      $q = _$q_;
      $controller = _$controller_;
      $location = _$location_;
      $rootScope = _$rootScope_;
      dialogService = _dialogService_;

      spyOn($rootScope, '$broadcast').and.callThrough();

      dialogService.dialogs = {};
      civicaseCrmApiMock.and.returnValue($q.resolve({
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
          initController();
          $rootScope.$digest();

          isButtonVisible = $scope.isVisible();
        });

        it('displays the more filter button', () => {
          expect(isButtonVisible).toBe(true);
        });
      });

      describe('when viewing any other dashboard', () => {
        beforeEach(() => {
          $location.search('case_type_category', 'cases');
          initController();
          $rootScope.$digest();

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
        $scope.activityFilters = { case_filter: {} };
        dialogService.close = jasmine.createSpy('');
        dialogService.open = function (__, templateName, model) {
          dialogModel = model;
        };
        $scope.openMoreFiltersDialog();
      });

      describe('when my awards filter is selected', () => {
        describe('when status filter is selected', () => {
          beforeEach(() => {
            dialogModel.selectedFilters.managed_by = '2';
            dialogModel.selectedFilters.start_date = '10/12/2019';
            dialogModel.selectedFilters.end_date = '15/12/2019';
            dialogModel.selectedFilters.award_subtypes = '1,2';
            dialogModel.selectedFilters.statuses = '2,3';
            dialogModel.selectedFilters.onlyShowDisabledAwards = false;
            dialogModel.applyFilterAndCloseDialog();
            $rootScope.$digest();
          });

          it('shows the awards where the logged in user is the manager and also applies the rest of filters', () => {
            expect($scope.activityFilters.case_filter).toEqual({
              'case_type_id.is_active': '1',
              status_id: { IN: ['2', '3'] },
              'status_id.grouping': { IN: ['Closed', 'Opened'] },
              'case_type_id.managed_by': '2',
              'case_type_id.award_detail_params': {
                award_subtype: '1,2',
                start_date: '10/12/2019',
                end_date: '15/12/2019'
              }
            });
            expect($rootScope.$broadcast).toHaveBeenCalledWith('civicase::dashboard-filters::updated');
          });
        });

        describe('disabled awards', () => {
          describe('when showing disabled awards', () => {
            beforeEach(() => {
              dialogModel.selectedFilters.onlyShowDisabledAwards = true;
              dialogModel.applyFilterAndCloseDialog();
              $rootScope.$digest();
            });

            it('filters applications by disabled awards', () => {
              expect($scope.activityFilters.case_filter).toEqual({
                'case_type_id.is_active': '0',
                status_id: { 'IS NOT NULL': 1 },
                'case_type_id.award_detail_params': {},
                'case_type_id.managed_by': 203
              });
            });
          });

          describe('when hiding disabled awards', () => {
            beforeEach(() => {
              dialogModel.selectedFilters.onlyShowDisabledAwards = false;
              dialogModel.applyFilterAndCloseDialog();
              $rootScope.$digest();
            });

            it('filters applications by enabled awards', () => {
              expect($scope.activityFilters.case_filter).toEqual({
                'case_type_id.is_active': '1',
                status_id: { 'IS NOT NULL': 1 },
                'case_type_id.award_detail_params': {},
                'case_type_id.managed_by': 203
              });
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
            expect($scope.activityFilters.case_filter).toEqual({
              'case_type_id.is_active': '1',
              status_id: { 'IS NOT NULL': 1 },
              'case_type_id.award_detail_params': {},
              'case_type_id.managed_by': 203
            });
          });
        });
      });

      describe('when all awards filter is selected', () => {
        beforeEach(() => {
          dialogModel.selectedFilters.managed_by = 'all_awards';
          dialogModel.applyFilterAndCloseDialog();
        });

        it('shows the all the awards', () => {
          expect($scope.activityFilters.case_filter).toEqual({
            'case_type_id.is_active': '1',
            status_id: { 'IS NOT NULL': 1 },
            'case_type_id.award_detail_params': {}
          });
        });
      });

      describe('when filters are not changed', () => {
        beforeEach(() => {
          dialogModel.selectedFilters.managed_by = 203;
          dialogModel.selectedFilters.statuses = '';
          dialogModel.selectedFilters.award_subtypes = '';
          dialogModel.selectedFilters.start_date = null;
          dialogModel.selectedFilters.end_date = null;
          dialogModel.selectedFilters.onlyShowDisabledAwards = false;
          dialogModel.applyFilterAndCloseDialog();
        });

        it('does not show a red dot inside the more filters button', () => {
          expect($scope.isNotificationVisible()).toBe(false);
        });
      });

      describe('when any of the filters are changed', () => {
        beforeEach(() => {
          dialogModel.selectedFilters.managed_by = 'all_awards';
          dialogModel.selectedFilters.statuses = '';
          dialogModel.selectedFilters.award_subtypes = '';
          dialogModel.selectedFilters.start_date = null;
          dialogModel.selectedFilters.end_date = null;
          dialogModel.selectedFilters.onlyShowDisabledAwards = false;
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
