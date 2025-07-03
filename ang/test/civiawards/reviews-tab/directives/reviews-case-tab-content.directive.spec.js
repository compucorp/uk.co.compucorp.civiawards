((_, $) => {
  describe('Review Case Tab Content', () => {
    let $controller, $q, $rootScope, $scope, AwardAdditionalDetailsMockData,
      caseItem, civicaseCrmApi, ReviewActivitiesMockData, ReviewFieldsMockData,
      crmStatus, civicaseCrmUrl, civicaseCrmLoadForm, crmApi4;
    const entityActionHandlers = {
      'Activity.get': activityGetHandler,
      'AwardDetail.getsingle': awardDetailGetSingleHandler,
      'CustomField.get': customFieldGetHandler
    };

    beforeEach(module('civiawards', 'civiawards.data', ($provide) => {
      civicaseCrmApi = getCrmApiMock();
      $provide.value('civicaseCrmApi', civicaseCrmApi);

      $provide.value('crmApi4', jasmine.createSpy('crmApi4'));
    }));

    beforeEach(inject((_$controller_, _$q_, _$rootScope_, _ApplicationsMockData_,
      _AwardAdditionalDetailsMockData_, _ReviewActivitiesMockData_, _ReviewFieldsMockData_,
      _crmStatus_,
      _civicaseCrmUrl_, _civicaseCrmLoadForm_, _crmApi4_) => {
      $controller = _$controller_;
      $q = _$q_;
      $rootScope = _$rootScope_;
      crmStatus = _crmStatus_;
      AwardAdditionalDetailsMockData = _AwardAdditionalDetailsMockData_;
      ReviewActivitiesMockData = _ReviewActivitiesMockData_;
      ReviewFieldsMockData = _ReviewFieldsMockData_;
      civicaseCrmUrl = _civicaseCrmUrl_;
      crmApi4 = _crmApi4_;
      civicaseCrmLoadForm = _civicaseCrmLoadForm_;

      caseItem = _.first(_ApplicationsMockData_);
      $scope = $rootScope.$new();
      $scope.caseItem = caseItem;
      crmApi4.and.returnValue($q.resolve([
        { values: [{ name: 'Applicant_Review' }] }
      ]));

      initController({ $scope });
    }));

    describe('on init', () => {
      it('starts loading the reviews', () => {
        expect($scope.isLoading).toBe(true);
      });

      it('requests the award details for the application award type', () => {
        expect(civicaseCrmApi).toHaveBeenCalledWith('AwardDetail', 'getsingle', {
          case_type_id: $scope.caseItem.case_type_id
        });
      });

      it('fetches all review fields', () => {
        expect(crmApi4).toHaveBeenCalledWith('ApplicantReviewField', 'get', {});
      });

      describe('after loading', () => {
        beforeEach(function () {
          $scope.$digest();
        });

        it('stops loading', () => {
          expect($scope.isLoading).toBe(false);
        });

        describe('review activities', () => {
          let finalReviewFields, reviewMockData;

          beforeEach(() => {
            finalReviewFields = _.each(angular.copy($scope.reviewActivities), function (activity) {
              delete activity.reviewFields;
            });
            reviewMockData = _.each(angular.copy(ReviewActivitiesMockData), function (activity) {
              activity.status_label = activity['api.OptionValue.getsingle'].label;
              delete activity['api.OptionValue.getsingle'];
              delete activity['api.CustomValue.gettreevalues'];
            });
          });

          it('stores all the review activities', () => {
            expect(finalReviewFields).toEqual(reviewMockData);
          });
        });

        describe('scoring fields', () => {
          let reviewFieldsIds;

          beforeEach(() => {
            reviewFieldsIds = _.map($scope.reviewActivities[0].reviewFields, function (activity) {
              return activity.id;
            });
          });

          it('sorts all scoring fields by weight', () => {
            expect(reviewFieldsIds).toEqual(getSortedScoringFieldIDs());
          });
        });
      });
    });

    describe('on case updated', () => {
      beforeEach(() => {
        civicaseCrmApi.calls.reset();
        $scope.$emit('updateCaseData');
      });

      it('reloads the list of reviews', () => {
        expect(crmApi4).toHaveBeenCalledWith('CustomGroup', 'get', jasmine.any(Object));
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
        civicaseCrmLoadForm.and.returnValue(mockedFormElement);
      });

      describe('when clicking the add new review button', () => {
        beforeEach(() => {
          expectedUrl = 'new/review/button/url';
          civicaseCrmUrl.and.returnValue(expectedUrl);
          $scope.handleAddReviewActivity();
        });

        it('opens the form to create a new review', () => {
          expect(civicaseCrmUrl).toHaveBeenCalledWith(REVIEW_FORM_URL, {
            action: 'add',
            case_id: $scope.caseItem.id,
            reset: 1
          });
          expect(civicaseCrmLoadForm).toHaveBeenCalledWith(expectedUrl);
        });

        describe('after the form has been saved', () => {
          beforeEach(() => {
            mockedFormElement.trigger(CRM_FORM_SUCCESS_EVENT);
          });

          it('reloads the reviews', () => {
            expect(crmApi4).toHaveBeenCalledWith('CustomGroup', 'get', jasmine.any(Object));
          });
        });
      });

      describe('when viewing a particular review', () => {
        beforeEach(() => {
          expectedUrl = 'view/review/button/url';
          civicaseCrmUrl.and.returnValue(expectedUrl);
          $scope.handleViewReviewActivity(selectedReview);
        });

        it('calls the form to view the selected review', () => {
          expect(civicaseCrmUrl).toHaveBeenCalledWith(REVIEW_FORM_URL, {
            action: 'view',
            id: selectedReview.id,
            reset: 1
          });
          expect(civicaseCrmLoadForm).toHaveBeenCalledWith(expectedUrl);
        });
      });

      describe('when editing a review', () => {
        beforeEach(() => {
          expectedUrl = 'update/review/button/url';
          civicaseCrmUrl.and.returnValue(expectedUrl);
          $scope.handleEditReviewActivity(selectedReview);
        });

        it('calls the form to edit the selected review', () => {
          expect(civicaseCrmUrl).toHaveBeenCalledWith(REVIEW_FORM_URL, {
            action: 'update',
            id: selectedReview.id,
            reset: 1
          });
          expect(civicaseCrmLoadForm).toHaveBeenCalledWith(expectedUrl);
        });

        describe('when saving the form', () => {
          beforeEach(() => {
            mockedFormElement.trigger(CRM_FORM_SUCCESS_EVENT);
          });

          it('reloads the reviews', () => {
            expect(crmApi4).toHaveBeenCalledWith('CustomGroup', 'get', jasmine.any(Object));
          });
        });
      });

      describe('when deleting a review', () => {
        beforeEach(() => {
          $scope.handleDeleteReviewActivity(selectedReview);
        });

        it('displays a confirmation prompt', () => {
          expect(CRM.confirm).toHaveBeenCalledWith({ title: 'Delete Review' });
        });

        describe('when confirming the review deletion', () => {
          beforeEach(() => {
            mockedConfirmElement.trigger('crmConfirm:yes');
          });

          it('deletes the selected review', () => {
            expect(civicaseCrmApi).toHaveBeenCalledWith('Activity', 'delete', {
              id: selectedReview.id
            });
          });

          it('shows a loading progress', () => {
            expect(crmStatus).toHaveBeenCalledWith({
              start: 'Deleting...',
              success: 'Deleted'
            }, jasmine.any(Object));
          });
        });
      });
    });

    describe('check popup title processing when viewing a review', () => {
      let mockedFormElement, selectedReview;
      const CRM_FORM_LOAD_EVENT = 'crmFormLoad';

      beforeEach(() => {
        mockedFormElement = $('<div class="ui-dialog">\n' +
          '  <div class="ui-dialog-titlebar">\n' +
          '    <span class="ui-dialog-title">View Review - Demo - Award &amp;nbsp; &lt;span class="crm-tag-item" style="background-color: #652881; color: #ffffff"&gt;Tag&lt;/span&gt;</span>\n' +
          '  </div>\n' +
          '  <div class="ui-dialog-content">\n' +
          '  </div>\n' +
          '</div>');
        selectedReview = _.chain(ReviewActivitiesMockData)
          .first()
          .cloneDeep()
          .value();

        civicaseCrmLoadForm.and.returnValue(mockedFormElement);
      });

      describe('when clicking the View review menu link', () => {
        beforeEach(() => {
          $scope.handleViewReviewActivity(selectedReview);
        });

        describe('after the form has been loaded', () => {
          beforeEach(() => {
            mockedFormElement.trigger(CRM_FORM_LOAD_EVENT);
          });

          it('displays the tags with background and text color', () => {
            expect(mockedFormElement.find('.crm-tag-item').length).toBe(1);
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
      return jasmine.createSpy('civicaseCrmApi')
        .and.callFake((entityName, action, params) => {
          const entityAction = `${entityName}.${action}`;
          const entityActionHandler = entityActionHandlers[entityAction];
          const response = entityActionHandler
            ? entityActionHandler()
            : { values: [] };

          return $q.resolve(response);
        });
    }

    /**
     * @returns {object[]} the expected scoring fields IDs sorted by the weight defined in the award's configuration.
     */
    function getSortedScoringFieldIDs () {
      const reviewFieldsIndexedById = _.indexBy(ReviewFieldsMockData, 'id');

      return _.sortBy(AwardAdditionalDetailsMockData.review_fields, 'weight')
        .map(function (scoringFieldSortOrder) {
          return reviewFieldsIndexedById[scoringFieldSortOrder.id].id;
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
})(CRM._, CRM.$);
