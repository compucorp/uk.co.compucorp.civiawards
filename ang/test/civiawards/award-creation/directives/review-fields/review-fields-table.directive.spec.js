(function (_) {
  describe('civiawardReviewFieldsTable', () => {
    var $rootScope, $controller, $scope, $q, civicaseCrmApi, ReviewFieldsMockData,
      AwardAdditionalDetailsMockData, dialogService;

    beforeEach(module('civiawards.templates', 'civiawards', 'civicase.data', 'civiawards.data', function ($provide) {
      $provide.value('civicaseCrmApi', jasmine.createSpy('civicaseCrmApi'));
      $provide.value('dialogService', jasmine.createSpyObj('dialogService', ['open', 'close']));
    }));

    beforeEach(inject((_$q_, _$controller_, _$rootScope_, _civicaseCrmApi_,
      _ReviewFieldsMockData_, _AwardAdditionalDetailsMockData_, _dialogService_) => {
      $q = _$q_;
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      dialogService = _dialogService_;
      civicaseCrmApi = _civicaseCrmApi_;
      ReviewFieldsMockData = _ReviewFieldsMockData_;
      AwardAdditionalDetailsMockData = _AwardAdditionalDetailsMockData_.get();
      $scope = $rootScope.$new();

      dialogService.dialogs = {};
      civicaseCrmApi.and.returnValue($q.resolve([
        { values: ReviewFieldsMockData }
      ]));

      $scope.$digest();
    }));

    describe('when the controller initializes', () => {
      beforeEach(() => {
        createController();
        $scope.$digest();
      });

      it('fetches all review fields', () => {
        expect(civicaseCrmApi).toHaveBeenCalledWith([['CustomField', 'get', {
          sequential: true,
          custom_group_id: 'Applicant_Review',
          options: { limit: 0 }
        }]]);
      });

      it('displays all review fields', () => {
        expect($scope.reviewFields).toEqual(ReviewFieldsMockData);
      });
    });

    describe('when an review field is clicked from the popup', () => {
      beforeEach(() => {
        createController();
        $scope.$digest();
      });

      describe('and the review was not selected before', () => {
        beforeEach(() => {
          $scope.additionalDetails = { selectedReviewFields: [] };

          $scope.toggleReviewField(ReviewFieldsMockData[0]);
        });

        it('adds the clicked review field as selected', () => {
          expect($scope.additionalDetails.selectedReviewFields[0]).toEqual({
            id: ReviewFieldsMockData[0].id,
            required: false,
            weight: 2
          });
        });

        it('shows the checkbox as checked beside the added field', () => {
          expect(!!$scope.findReviewFieldByID(ReviewFieldsMockData[0].id)).toEqual(true);
        });
      });

      describe('and the review was selected before', () => {
        beforeEach(() => {
          $scope.additionalDetails = {
            selectedReviewFields: [{
              id: ReviewFieldsMockData[0].id,
              required: false,
              weight: 1
            }]
          };

          $scope.toggleReviewField(ReviewFieldsMockData[0]);
        });

        it('removes the clicked review field from selected', () => {
          expect($scope.additionalDetails.selectedReviewFields).toEqual([]);
        });
      });
    });

    describe('when the REMOVE button is clicked from the review field list', () => {
      beforeEach(() => {
        createController();
        $scope.$digest();

        $scope.additionalDetails = {
          selectedReviewFields: [{
            id: ReviewFieldsMockData[0].id,
            required: false,
            weight: 1
          }]
        };

        $scope.removeReviewFieldFromSelection(ReviewFieldsMockData[0]);
      });

      it('removes the clicked review field from selected', () => {
        expect($scope.additionalDetails.selectedReviewFields).toEqual([]);
      });
    });

    describe('when an existing awards details are fetched', () => {
      beforeEach(() => {
        createController();
        $scope.additionalDetails = {};

        $scope.$emit('civiawards::edit-award::details-fetched', {
          additionalDetails: AwardAdditionalDetailsMockData
        });
      });

      it('displays the already saved review fields', () => {
        expect($scope.additionalDetails.selectedReviewFields).toEqual([{
          id: '19',
          required: true,
          weight: 1
        }, {
          id: '20',
          required: false,
          weight: 2
        }, {
          id: '45',
          required: false,
          weight: 3
        }]);
      });
    });

    describe('when "Add Review Fields" button is clicked', () => {
      beforeEach(() => {
        createController();
        $scope.openReviewFieldSelectionPopup();
      });

      it('displays the already saved review fields', () => {
        expect(dialogService.open).toHaveBeenCalledWith('ReviewFields', '~/civiawards/award-creation/directives/review-fields/review-field-selection.html', $scope, {
          autoOpen: false,
          height: 'auto',
          width: '600px',
          title: 'Select Review Fields',
          buttons: [{
            text: 'Done',
            icons: { primary: 'fa-check' },
            click: jasmine.any(Function)
          }]
        });
      });

      describe('when the DONE button is clicked', () => {
        beforeEach(() => {
          var doneButtonClickFunction =
            dialogService.open.calls.mostRecent().args[3].buttons[0].click;

          doneButtonClickFunction();
        });

        it('closes the review fields popup', () => {
          expect(dialogService.close).toHaveBeenCalledWith('ReviewFields');
        });
      });
    });

    describe('when Required checkbox is clicked', () => {
      var reviewField;

      beforeEach(() => {
        reviewField = {
          id: ReviewFieldsMockData[0].id,
          required: false,
          weight: 1
        };

        createController();
        $scope.$digest();

        $scope.additionalDetails = {
          selectedReviewFields: [reviewField]
        };

        $scope.toggleRequiredState(reviewField);
      });

      it('toggles the checkbox state', () => {
        expect(reviewField.required).toBe(true);
      });
    });

    describe('when displaying the selected review fields', () => {
      var reviewField;

      beforeEach(() => {
        reviewField = {
          id: ReviewFieldsMockData[0].id,
          required: false,
          weight: 1
        };

        createController();
        $scope.$digest();

        $scope.additionalDetails = {
          selectedReviewFields: [reviewField]
        };
      });

      it('shows details of the all the fields', () => {
        expect($scope.getReviewFieldData(reviewField.id, 'data_type')).toBe('String');
        expect($scope.getReviewFieldData(reviewField.id, 'html_type')).toBe('Text');
      });
    });

    describe('when trying to reorder the selected review fields', () => {
      var reviewFields;

      beforeEach(() => {
        reviewFields = [{
          id: ReviewFieldsMockData[0].id,
          required: false,
          weight: 1
        }, {
          id: ReviewFieldsMockData[1].id,
          required: false,
          weight: 2
        }, {
          id: ReviewFieldsMockData[2].id,
          required: false,
          weight: 3
        }];

        createController();
        $scope.$digest();

        $scope.additionalDetails = {
          selectedReviewFields: reviewFields
        };
      });

      describe('when clicking on move up button', () => {
        beforeEach(() => {
          $scope.moveUp(reviewFields[1]);
        });

        it('moves the field up the order by one', () => {
          expect($scope.additionalDetails.selectedReviewFields[0].weight).toBe(2);
          expect($scope.additionalDetails.selectedReviewFields[1].weight).toBe(1);
          expect($scope.additionalDetails.selectedReviewFields[2].weight).toBe(3);
        });
      });

      describe('when clicking on move down button', () => {
        beforeEach(() => {
          $scope.moveDown(reviewFields[1]);
        });

        it('moves the field down the order by one', () => {
          expect($scope.additionalDetails.selectedReviewFields[0].weight).toBe(1);
          expect($scope.additionalDetails.selectedReviewFields[1].weight).toBe(3);
          expect($scope.additionalDetails.selectedReviewFields[2].weight).toBe(2);
        });
      });

      describe('when clicking on move to top button', () => {
        beforeEach(() => {
          $scope.moveToTop(reviewFields[2]);
        });

        it('moves the field to the bottom of the list', () => {
          expect($scope.additionalDetails.selectedReviewFields[0].weight).toBe(2);
          expect($scope.additionalDetails.selectedReviewFields[1].weight).toBe(3);
          expect($scope.additionalDetails.selectedReviewFields[2].weight).toBe(1);
        });
      });

      describe('when clicking on move to bottom button', () => {
        beforeEach(() => {
          $scope.moveToBottom(reviewFields[0]);
        });

        it('moves the field to the top of the list', () => {
          expect($scope.additionalDetails.selectedReviewFields[0].weight).toBe(3);
          expect($scope.additionalDetails.selectedReviewFields[1].weight).toBe(1);
          expect($scope.additionalDetails.selectedReviewFields[2].weight).toBe(2);
        });
      });
    });

    /**
     * Create Controller
     */
    function createController () {
      $controller('CiviawardReviewFieldsTableController', {
        $scope: $scope
      });
    }
  });
}(CRM._));
