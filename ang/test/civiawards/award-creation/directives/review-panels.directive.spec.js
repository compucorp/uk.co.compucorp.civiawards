/* eslint-env jasmine */
(function (_) {
  describe('civiawardReviewPanels', () => {
    var $rootScope, $controller, $scope, dialogServiceMock, crmApi, $q, crmStatus, ts;

    beforeEach(module('civiawards.templates', 'civiawards', 'crmUtil', 'civicase.data', 'civiawards.data', function ($provide) {
      dialogServiceMock = jasmine.createSpyObj('dialogService', ['open']);

      $provide.value('crmApi', jasmine.createSpy('crmApi'));
      $provide.value('dialogService', dialogServiceMock);
    }));

    beforeEach(inject((_$q_, _$controller_, _$rootScope_, _crmApi_, _crmStatus_, _ts_) => {
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      crmApi = _crmApi_;
      crmStatus = _crmStatus_;
      $q = _$q_;
      ts = _ts_;
      $scope = $rootScope.$new();

      crmApi.and.returnValue($q.resolve());

      $scope.$digest();
    }));

    describe('when the controller initializes', () => {
      beforeEach(() => {
        createController();
      });

      it('disables the save button inside the review panel popup', () => {
        expect($scope.submitInProgress).toBe(false);
      });

      it('resets all fields inside review panel popup', () => {
        expect($scope.reviewPanel).toEqual({});
      });
    });

    describe('when "Add Review Panel" button is clicked', () => {
      beforeEach(() => {
        createController();
        $scope.openCreateReviewPanelPopup();
      });

      it('opens the review panel popup', () => {
        expect(dialogServiceMock.open).toHaveBeenCalledWith('ReviewPanels',
          '~/civiawards/award-creation/directives/review-panel-popup.html',
          $scope,
          jasmine.any(Object)
        );
      });
    });

    describe('when "Add Review Panel" button is clicked', () => {
      var saveButtonClickHandler;

      beforeEach(() => {
        createController();
        $scope.review_panel_form = {};

        dialogServiceMock.open.and.callFake(function (__, ___, ____, options) {
          saveButtonClickHandler = options.buttons[0].click;
        });

        $scope.openCreateReviewPanelPopup();
      });

      describe('button disablity', () => {
        describe('when the form is not valid', () => {
          beforeEach(() => {
            $scope.review_panel_form.$valid = false;
            saveButtonClickHandler();
          });

          it('does not save the review panel', () => {
            expect(crmApi).not.toHaveBeenCalled();
          });
        });

        describe('when the form is valid but save is in progress', () => {
          beforeEach(() => {
            $scope.review_panel_form.$valid = true;
            $scope.submitInProgress = true;
            saveButtonClickHandler();
          });

          it('does not save the review panel', () => {
            expect(crmApi).not.toHaveBeenCalled();
          });
        });
      });

      describe('click event', () => {
        beforeEach(() => {
          $scope.awardId = 1;
          $scope.review_panel_form.$valid = true;
          $scope.submitInProgress = false;
          $scope.reviewPanel.title = 'New Review Panel';
          $scope.reviewPanel.isEnabled = true;

          saveButtonClickHandler();
        });

        it('saves the review panel', () => {
          expect(crmApi).toHaveBeenCalledWith('AwardReviewPanel', 'create', {
            title: 'New Review Panel',
            is_active: true,
            case_type_id: 1
          });
        });

        it('shows a notification when save is in progress', () => {
          expect(crmStatus).toHaveBeenCalledWith({
            start: ts('Saving Review Panel...'),
            success: ts('Review Panel Saved')
          }, jasmine.any(Object));
        });
      });
    });

    /**
     * Create Controller
     */
    function createController () {
      $controller('CiviawardReviewPanelsController', {
        $scope: $scope
      });
    }
  });
}(CRM._));
