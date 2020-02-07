/* eslint-env jasmine */
((_, $, getCrmUrl) => {
  describe('Review Case Tab Content', () => {
    let $controller, $q, $rootScope, $scope, AwardAdditionalDetailsMockData,
      caseItem, crmApi, ReviewActivitiesMockData, ReviewFieldsMockData,
      reviewsActivityTypeName, reviewScoringFieldsGroupName, OptionValuesMockData;
    const entityActionHandlers = {
      'Activity.get': activityGetHandler,
      'AwardDetail.getsingle': awardDetailGetSingleHandler,
      'CustomField.get': customFieldGetHandler
    };

    beforeEach(module('civiawards', 'civiawards.data', ($provide) => {
      crmApi = getCrmApiMock();

      $provide.value('crmApi', crmApi);
    }));

    beforeEach(inject((_$controller_, _$q_, _$rootScope_, _ApplicationsMockData_,
      _AwardAdditionalDetailsMockData_, _ReviewActivitiesMockData_, _ReviewFieldsMockData_,
      _reviewsActivityTypeName_, _reviewScoringFieldsGroupName_,
      _OptionValuesMockData_) => {
      $controller = _$controller_;
      $q = _$q_;
      $rootScope = _$rootScope_;
      AwardAdditionalDetailsMockData = _AwardAdditionalDetailsMockData_;
      ReviewActivitiesMockData = _ReviewActivitiesMockData_;
      ReviewFieldsMockData = _ReviewFieldsMockData_;
      OptionValuesMockData = _OptionValuesMockData_;
      reviewsActivityTypeName = _reviewsActivityTypeName_;
      reviewScoringFieldsGroupName = _reviewScoringFieldsGroupName_;
      caseItem = _.first(_ApplicationsMockData_);
      $scope = $rootScope.$new();
      $scope.caseItem = caseItem;

      initController({ $scope });
    }));

    describe('on init', () => {
      it('starts loading the reviews', () => {
        expect($scope.isLoading).toBe(true);
      });

      it('requests the award details for the application award type', () => {
        expect(crmApi).toHaveBeenCalledWith('AwardDetail', 'getsingle', {
          case_type_id: $scope.caseItem.case_type_id
        });
      });

      it('requests all review activities for the application', () => {
        expect(crmApi).toHaveBeenCalledWith('Activity', 'get', {
          activity_type_id: reviewsActivityTypeName,
          case_id: $scope.caseItem.id,
          options: { limit: 0 },
          sequential: 1
        });
      });

      it('requests all the scoring fields used to review the applicant', () => {
        expect(crmApi).toHaveBeenCalledWith('CustomField', 'get', {
          custom_group_id: reviewScoringFieldsGroupName,
          options: { limit: 0 },
          sequential: 0
        });
      });

      describe('after loading', () => {
        let sortedScoringFields;

        beforeEach(() => {
          sortedScoringFields = getSortedScoringFields();

          $rootScope.$digest();
        });

        it('stops loading', () => {
          expect($scope.isLoading).toBe(false);
        });

        it('stores all the review activities', () => {
          expect($scope.reviewActivities).toEqual(ReviewActivitiesMockData);
        });

        it('stores the custom review fields in the order defined by the award type configuration', () => {
          expect($scope.scoringFields).toEqual(sortedScoringFields);
        });

        it('fetches the original value for the fields linked with option values', () => {
          expect(crmApi).toHaveBeenCalledWith([
            ['OptionValue', 'get', {
              sequential: 1,
              return: ['label', 'value'],
              option_group_id: '118'
            }]
          ]);
        });
      });
    });

    describe('review forms', () => {
      let expectedUrl, mockedConfirmElement, mockedFormElement, selectedReview;
      const CRM_FORM_SUCCESS_EVENT = 'crmFormSuccess.crmPopup crmPopupFormSuccess.crmPopup';
      const REVIEW_FORM_URL = 'civicrm/awardreview';

      beforeEach(() => {
        mockedConfirmElement = $('<span></span>');
        mockedFormElement = $('<span></span>');
        selectedReview = _.chain(ReviewActivitiesMockData)
          .first()
          .cloneDeep()
          .value();

        CRM.confirm.and.returnValue(mockedConfirmElement);
        CRM.loadForm.and.returnValue(mockedFormElement);
      });

      describe('when clicking the add new review button', () => {
        beforeEach(() => {
          expectedUrl = getCrmUrl(REVIEW_FORM_URL, {
            action: 'add',
            case_id: $scope.caseItem.id,
            reset: 1
          });

          $scope.handleAddReviewActivity();
        });

        it('opens the form to create a new review', () => {
          expect(CRM.loadForm).toHaveBeenCalledWith(expectedUrl);
        });

        describe('after the form has been saved', () => {
          beforeEach(() => {
            mockedFormElement.trigger(CRM_FORM_SUCCESS_EVENT);
          });

          it('reloads the reviews', () => {
            expect(crmApi).toHaveBeenCalledWith('Activity', 'get', jasmine.any(Object));
          });
        });
      });

      describe('when viewing a particular review', () => {
        beforeEach(() => {
          expectedUrl = getCrmUrl(REVIEW_FORM_URL, {
            action: 'view',
            id: selectedReview.id,
            reset: 1
          });

          $scope.handleViewReviewActivity(selectedReview);
        });

        it('calls the form to view the selected review', () => {
          expect(CRM.loadForm).toHaveBeenCalledWith(expectedUrl);
        });
      });

      describe('when editing a review', () => {
        beforeEach(() => {
          expectedUrl = getCrmUrl(REVIEW_FORM_URL, {
            action: 'update',
            id: selectedReview.id,
            reset: 1
          });

          $scope.handleEditReviewActivity(selectedReview);
        });

        it('calls the form to edit the selected review', () => {
          expect(CRM.loadForm).toHaveBeenCalledWith(expectedUrl);
        });

        describe('when saving the form', () => {
          beforeEach(() => {
            mockedFormElement.trigger(CRM_FORM_SUCCESS_EVENT);
          });

          it('reloads the reviews', () => {
            expect(crmApi).toHaveBeenCalledWith('Activity', 'get', jasmine.any(Object));
          });
        });
      });

      describe('when deleting a review', () => {
        beforeEach(() => {
          $scope.handleDeleteReviewActivity(selectedReview);
        });

        it('displays a confirmation prompt', () => {
          expect(CRM.confirm).toHaveBeenCalledWith();
        });

        describe('when confirming the review deletion', () => {
          let mockedStatusResponse;

          beforeEach(() => {
            mockedStatusResponse = jasmine.createSpyObj('status', ['resolve', 'reject']);
            CRM.status.and.returnValue(mockedStatusResponse);

            mockedConfirmElement.trigger('crmConfirm:yes');
          });

          it('deletes the selected review', () => {
            expect(crmApi).toHaveBeenCalledWith('Activity', 'delete', {
              id: selectedReview.id
            });
          });

          it('shows a loading progress', () => {
            expect(CRM.status).toHaveBeenCalledWith();
          });

          describe('after the review has been deleted', () => {
            beforeEach(() => {
              $rootScope.$digest();
            });

            it('displays a success message', () => {
              expect(mockedStatusResponse.resolve).toHaveBeenCalledWith();
            });
          });
        });
      });
    });

    /**
     * @returns {object} the mocked response for the Activity.Get api action.
     */
    function activityGetHandler () {
      return {
        is_error: 0,
        version: 3,
        count: ReviewActivitiesMockData.length,
        values: _.cloneDeep(ReviewActivitiesMockData)
      };
    }

    /**
     * @returns {object} the mocked response for the OptionValue.Get api action.
     */
    function optionValueGetHandler () {
      return [{
        is_error: 0,
        version: 3,
        count: OptionValuesMockData.get().length,
        values: _.cloneDeep(OptionValuesMockData.get())
      }];
    }

    /**
     * @returns {object} the mocked response for the Award.Additionaldetails api action.
     */
    function awardDetailGetSingleHandler () {
      return AwardAdditionalDetailsMockData;
    }

    /**
     * @returns {object} the mocked response for the CustomField.Get api action.
     */
    function customFieldGetHandler () {
      return {
        is_error: 0,
        version: 3,
        count: ReviewFieldsMockData.length,
        values: _.chain(ReviewFieldsMockData)
          .cloneDeep()
          .indexBy('id')
          .value()
      };
    }

    /**
     * @returns {Function} Returns a spy function that can replace `CRM.api3`. It also returns a mocked response
     * if there's a handler defined in the `entityActionHandler` map. The name of the handler follows the structure
     * "EntityName.ActionName".
     */
    function getCrmApiMock () {
      return jasmine.createSpy('crmApi')
        .and.callFake((entityName, action, params) => {
          let response;

          if (_.isArray(entityName)) {
            response = optionValueGetHandler();
          } else {
            const entityAction = `${entityName}.${action}`;
            const entityActionHandler = entityActionHandlers[entityAction];
            response = entityActionHandler
              ? entityActionHandler()
              : { values: [] };
          }

          return $q.resolve(response);
        });
    }

    /**
     * @returns {object[]} the expected scoring fields sorted by the weight defined in the award's configuration.
     */
    function getSortedScoringFields () {
      const reviewFieldsIndexedById = _.indexBy(ReviewFieldsMockData, 'id');

      return _.sortBy(AwardAdditionalDetailsMockData.review_fields, 'weight')
        .map(function (scoringFieldSortOrder) {
          return reviewFieldsIndexedById[scoringFieldSortOrder.id];
        });
    }

    /**
     * Initialises the Review Case Tab Content controller using the given dependencies.
     *
     * @param {object} dependencies a list of dependencies to pass to the controller.
     */
    function initController (dependencies) {
      $controller('civiawardsReviewsCaseTabContentController', dependencies);
    }
  });
})(CRM._, CRM.$, CRM.url);
