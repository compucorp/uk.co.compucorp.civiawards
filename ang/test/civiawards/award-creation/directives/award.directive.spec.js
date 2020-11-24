/* eslint-env jasmine */
(function (_) {
  describe('civiaward', () => {
    var $q, $controller, $rootScope, $scope, $window, $location, crmApi, crmStatus,
      CaseStatus, AwardMockData, AwardAdditionalDetailsMockData, ReviewFieldsMockData;

    beforeEach(module('civicase-base', 'civiawards.templates',
      'crmUtil', 'civiawards', 'civicase.data', 'civiawards.data', ($provide) => {
        $provide.value('crmApi', jasmine.createSpy('crmApi'));
        $provide.value('$window', { location: {} });
        $provide.value('$location', jasmine.createSpyObj('$location', ['path']));
      }));

    beforeEach(inject((_$q_, _$controller_, _$window_, _$location_, _$rootScope_, _crmApi_,
      _CaseStatus_, _AwardMockData_, _AwardAdditionalDetailsMockData_, _ReviewFieldsMockData_,
      _crmStatus_) => {
      $q = _$q_;
      $window = _$window_;
      $location = _$location_;
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      crmStatus = _crmStatus_;
      crmApi = _crmApi_;
      CaseStatus = _CaseStatus_;
      AwardMockData = _AwardMockData_;
      AwardAdditionalDetailsMockData = _AwardAdditionalDetailsMockData_.get();
      ReviewFieldsMockData = _ReviewFieldsMockData_;
      $scope = $rootScope.$new();

      spyOn($scope, '$emit');
      spyOn(CRM, 'alert').and.callThrough();

      crmApi.and.callFake(mockCrmApiService);
      $scope.$digest();
      $scope.basic_details_form = { awardName: { $pristine: true } };
    }));

    describe('basic tests', () => {
      describe('tabs', function () {
        beforeEach(() => {
          createController({ ifNewAward: true });
        });

        it('shows all the tabs', () => {
          expect($scope.tabs).toEqual([
            { name: 'basicDetails', label: 'Basic Details' },
            { name: 'stages', label: 'Award Stages' },
            { name: 'customFieldSets', label: 'Custom Field Sets' },
            { name: 'reviewPanels', label: 'Panels' },
            { name: 'reviewFields', label: 'Review Fields' }
          ]);
        });
      });

      describe('when creating new award', function () {
        beforeEach(() => {
          createController({ ifNewAward: true });
        });

        it('disables the submit button', () => {
          expect($scope.submitInProgress).toBe(false);
        });

        describe('all basic fields', () => {
          var expectedSelectedAwardStages = {};

          beforeEach(function () {
            _.each(CaseStatus.getAll(), (awardStage) => {
              expectedSelectedAwardStages[awardStage.value] = true;
            });
          });

          it('defines all basic details fields as empty', () => {
            expect($scope.basicDetails).toEqual({
              title: '',
              name: '',
              description: '',
              isEnabled: true
            });
          });

          it('defines all additional details fields as empty', () => {
            expect($scope.additionalDetails).toEqual({
              awardSubtype: null,
              startDate: null,
              endDate: null,
              awardManagers: [],
              selectedReviewFields: []
            });
          });
        });
      });

      describe('when editing existing award', function () {
        beforeEach(() => {
          crmApi.and.returnValue($q.resolve({
            caseType: AwardMockData[0],
            additionalDetails: AwardAdditionalDetailsMockData
          }));

          createController({ ifNewAward: false });
          $scope.$digest();
        });

        it('fires an event with the award details', () => {
          expect($scope.$emit).toHaveBeenCalledWith('civiawards::edit-award::details-fetched', {
            caseType: AwardMockData[0],
            additionalDetails: AwardAdditionalDetailsMockData
          });
        });
      });
    });

    describe('status options', () => {
      describe('when the controller initialises', () => {
        beforeEach(() => {
          createController({});
        });

        it('defines the list of status options as empty', () => {
          expect($scope.applicationStatusOptions).toEqual([]);
        });
      });

      describe('when the award has some status names stored', () => {
        beforeEach(() => {
          const award = _.chain(AwardMockData).first().cloneDeep().value();
          award.definition.statuses = ['Open', 'Closed'];

          crmApi.and.returnValue($q.resolve({
            caseType: award
          }));

          createController({});
          $scope.$digest();
        });

        it('stores the status data as select2 options', () => {
          expect($scope.applicationStatusOptions).toEqual([
            { id: '1', text: 'Ongoing', name: 'Open' },
            { id: '2', text: 'Resolved', name: 'Closed' }
          ]);
        });
      });

      describe('when the award has no status names stored', () => {
        beforeEach(() => {
          const award = _.chain(AwardMockData).first().cloneDeep().value();
          delete award.definition.statuses;

          crmApi.and.returnValue($q.resolve({
            caseType: award
          }));

          createController({});
          $scope.$digest();
        });

        it('stores all the statuses data as select2 options', () => {
          expect($scope.applicationStatusOptions).toEqual([
            { id: '1', text: 'Ongoing', name: 'Open' },
            { id: '2', text: 'Resolved', name: 'Closed' },
            { id: '3', text: 'Urgent', name: 'Urgent' }
          ]);
        });
      });
    });

    describe('save button disability', () => {
      var returnValue;

      beforeEach(() => {
        createController({ ifNewAward: true });
      });

      describe('when the basic details form is valid and submit is in progress', () => {
        beforeEach(() => {
          $scope.basic_details_form.$valid = true;
          $scope.submitInProgress = true;
          returnValue = $scope.ifSaveButtonDisabled();
        });

        it('disables the save button', () => {
          expect(returnValue).toBe(true);
        });
      });

      describe('when the basic details form is valid and submit is not in progress', () => {
        beforeEach(() => {
          $scope.basic_details_form.$valid = true;
          $scope.submitInProgress = false;
          returnValue = $scope.ifSaveButtonDisabled();
        });

        it('enables the save button', () => {
          expect(returnValue).toBe(false);
        });
      });

      describe('when the basic details form is invalid and submit is in progress', () => {
        beforeEach(() => {
          $scope.basic_details_form.$valid = false;
          $scope.submitInProgress = true;
          returnValue = $scope.ifSaveButtonDisabled();
        });

        it('disables the save button', () => {
          expect(returnValue).toBe(true);
        });
      });

      describe('when the basic details form is invalid and submit is not in progress', () => {
        beforeEach(() => {
          $scope.basic_details_form.$valid = false;
          $scope.submitInProgress = false;
          returnValue = $scope.ifSaveButtonDisabled();
        });

        it('disables the save button', () => {
          expect(returnValue).toBe(true);
        });
      });
    });

    describe('when selecting a tab', () => {
      beforeEach(() => {
        createController({ ifNewAward: true });

        $scope.selectTab('basicDetails');
      });

      it('selects the clicked tab', () => {
        expect($scope.activeTab).toBe('basicDetails');
      });
    });

    describe('when saving the award', () => {
      describe('when creating a new award', () => {
        beforeEach(() => {
          createController({ ifNewAward: true });
          $scope.reviewFields = ReviewFieldsMockData;
          setAwardDetails();

          $scope.saveAwardInBG();
        });

        it('momentarily disables the save button', () => {
          expect($scope.submitInProgress).toBe(true);
        });

        describe('saving the details', () => {
          beforeEach(() => {
            $scope.$digest();
          });

          it('shows saving notification while save is in progress', () => {
            expect(crmStatus).toHaveBeenCalledWith({
              start: $scope.ts('Saving Award...'),
              success: $scope.ts('Saved')
            }, jasmine.any(Object));
          });

          it('saves the basic award details', () => {
            expect(crmApi).toHaveBeenCalledWith('CaseType', 'create', {
              sequential: true,
              title: 'title',
              description: 'description',
              is_active: true,
              case_type_category: '3',
              name: 'title',
              definition: jasmine.objectContaining({
                caseRoles: [{
                  name: 'Application Manager',
                  manager: 1
                }]
              })
            });
          });

          it('saves default activity types for the award', () => {
            expect(crmApi).toHaveBeenCalledWith('CaseType', 'create', jasmine.objectContaining({
              definition: jasmine.objectContaining({
                activityTypes: [
                  { name: 'Applicant Review' },
                  { name: 'Email' },
                  { name: 'Follow up' },
                  { name: 'Meeting' },
                  { name: 'Phone Call' }
                ]
              })
            }));
          });

          it('saves the additional award details', () => {
            expect(crmApi).toHaveBeenCalledWith('AwardDetail', 'create', {
              sequential: true,
              case_type_id: _.first(AwardMockData).id,
              start_date: AwardAdditionalDetailsMockData.start_date,
              end_date: AwardAdditionalDetailsMockData.end_date,
              award_subtype: AwardAdditionalDetailsMockData.award_subtype,
              award_manager: ['2', '1'],
              review_fields: [{ id: '19', required: '0', weight: 1 }]
            });
          });

          it('updates the list of application status options', () => {
            expect($scope.applicationStatusOptions).toEqual([
              {
                id: '1',
                text: 'Ongoing',
                name: 'Open'
              },
              {
                id: '2',
                text: 'Resolved',
                name: 'Closed'
              },
              {
                id: '3',
                text: 'Urgent',
                name: 'Urgent'
              }
            ]);
          });

          it('shows a notification after save is successfull', () => {
            expect(CRM.alert).toHaveBeenCalledWith('Award Successfully Saved.', 'Saved', 'success');
          });

          it('enables the save button after api has responded', () => {
            expect($scope.submitInProgress).toBe(false);
          });
        });
      });

      describe('when editing an award', () => {
        beforeEach(() => {
          createController({ ifNewAward: false });
          $scope.awardStages = CaseStatus.getAll();
          $scope.basicDetails.selectedAwardStages = { 1: true };

          _.each($scope.tabs, function (tabObj) {
            tabObj.save = jasmine.createSpy('tabSaveFn');
          });

          setAwardDetails();
          $scope.saveAwardInBG();
          $scope.$digest();
        });

        it('runs individual tabs save logic', () => {
          _.each($scope.tabs, function (tabObj) {
            expect(tabObj.save).toHaveBeenCalled();
          });
        });

        it('shows saving notification while save is in progress', () => {
          expect(crmStatus).toHaveBeenCalledWith({
            start: $scope.ts('Saving Award...'),
            success: $scope.ts('Saved')
          }, jasmine.any(Object));
        });

        it('saves the basic award details', () => {
          expect(crmApi).toHaveBeenCalledWith('CaseType', 'create', {
            sequential: true,
            title: 'title',
            description: 'description',
            is_active: true,
            case_type_category: '3',
            name: 'title',
            definition: jasmine.objectContaining({
              caseRoles: [{
                name: 'Application Manager',
                manager: 1
              }],
              statuses: ['Open']
            }),
            id: '10'
          });
        });

        it('creates the award with default activity types', () => {
          expect(crmApi).toHaveBeenCalledWith('CaseType', 'create', jasmine.objectContaining({
            definition: jasmine.objectContaining({
              activityTypes: [
                { name: 'Applicant Review' },
                { name: 'Email' },
                { name: 'Follow up' },
                { name: 'Meeting' },
                { name: 'Phone Call' }
              ]
            })
          }));
        });
      });
    });

    describe('when saving a new award', () => {
      beforeEach(() => {
        createController({ ifNewAward: true });
        setAwardDetails();

        $scope.saveNewAward();
        $scope.$digest();
      });

      it('redirects to edit the award', () => {
        expect($location.path).toHaveBeenCalledWith('/awards/3/10/stages');
      });

      it('shows a notification after save is successfull', () => {
        expect(CRM.alert).toHaveBeenCalledWith('Award Successfully Saved.', 'Saved', 'success');
      });
    });

    describe('when SAVE AND DONE is clicked', () => {
      beforeEach(() => {
        createController({ ifNewAward: true });
        setAwardDetails();

        $scope.saveAndNavigateToDashboard();
        $scope.$digest();
      });

      it('shows saving notification while save is in progress', () => {
        expect(crmStatus).toHaveBeenCalledWith({
          start: $scope.ts('Saving Award...'),
          success: $scope.ts('Saved')
        }, jasmine.any(Object));
      });

      it('redirects to award dashboard page', () => {
        expect($window.location.href).toBe('/civicrm/case/a/?case_type_category=awards#/case?case_type_category=awards');
      });

      it('shows a notification after save is successfull', () => {
        expect(CRM.alert).toHaveBeenCalledWith('Award Successfully Saved.', 'Saved', 'success');
      });
    });

    /**
     * Set Award Details
     */
    function setAwardDetails () {
      $scope.basicDetails.title = 'title';
      $scope.basicDetails.description = 'description';
      $scope.basicDetails.isEnabled = true;
      $scope.additionalDetails.awardManagers = '2,1';
      $scope.additionalDetails.startDate = AwardAdditionalDetailsMockData.start_date;
      $scope.additionalDetails.endDate = AwardAdditionalDetailsMockData.end_date;
      $scope.additionalDetails.awardSubtype = AwardAdditionalDetailsMockData.award_subtype;
      $scope.additionalDetails.selectedReviewFields = [{
        id: ReviewFieldsMockData[0].id,
        required: false,
        weight: 1
      }];

      crmApi.and.callFake(mockCrmApiService);
    }

    /**
     * Create Controller
     *
     * @param {object} params parameters
     */
    function createController (params) {
      if (!params.ifNewAward) {
        $scope.awardId = '10';
      }

      $scope.caseTypeCategoryId = '3';

      $controller('CiviAwardCreateEditAwardController', {
        $scope: $scope
      });
    }

    /**
     * Mocks API calls and responses through the CRM API Service.
     *
     * @param {string|object} entity The Entity name or an object with multiple requests.
     * @param {string} action The Action name
     * @param {object} params Extra parameters to send to the endpoint.
     *
     * @returns {Promise} The mocked API response.
     */
    function mockCrmApiService (entity, action, params) {
      const mockAwardType = _.extend({}, _.first(AwardMockData), {
        definition: {}
      });
      const entityResponses = {
        AwardDetail: { values: [AwardAdditionalDetailsMockData] },
        CaseType: { values: [mockAwardType] }
      };

      if (_.isObject(entity)) {
        return $q.resolve({
          caseType: mockAwardType,
          additionalDetails: AwardAdditionalDetailsMockData
        });
      }

      return $q.resolve(entityResponses[entity]);
    }
  });
}(CRM._));
