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
      AwardAdditionalDetailsMockData = _AwardAdditionalDetailsMockData_;
      ReviewFieldsMockData = _ReviewFieldsMockData_;
      $scope = $rootScope.$new();

      spyOn($scope, '$emit');
      spyOn(CRM, 'alert').and.callThrough();

      crmApi.and.returnValue($q.resolve({}));
      $scope.$digest();
      $scope.basic_details_form = { awardName: { $pristine: true } };
    }));

    describe('basic tests', () => {
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
              awardType: null,
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
              case_type_id: '1',
              start_date: AwardAdditionalDetailsMockData.start_date,
              end_date: AwardAdditionalDetailsMockData.end_date,
              award_type: AwardAdditionalDetailsMockData.award_type,
              award_manager: ['2', '1'],
              review_fields: [{ id: '19', required: '0', weight: 1 }]
            });
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
          setAwardDetails();

          crmApi.and.returnValue($q.resolve({
            values: [AwardAdditionalDetailsMockData]
          }));
          $scope.saveAwardInBG();
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
        expect($location.path).toHaveBeenCalledWith('/awards/10/stages');
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
      $scope.additionalDetails.awardType = AwardAdditionalDetailsMockData.award_type;
      $scope.additionalDetails.selectedReviewFields = [{
        id: ReviewFieldsMockData[0].id,
        required: false,
        weight: 1
      }];

      crmApi.and.returnValue($q.resolve({
        values: [AwardAdditionalDetailsMockData]
      }));
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
      $controller('CiviAwardCreateEditAwardController', {
        $scope: $scope
      });
    }
  });
}(CRM._));
