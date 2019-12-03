/* eslint-env jasmine */
(function (_) {
  describe('civiaward', () => {
    var $q, $controller, $rootScope, $scope, $window, $location, crmApi,
      CaseStatus, AwardMockData, AwardAdditionalDetailsMockData;

    beforeEach(module('civicase-base', 'civiawards.templates', 'civiawards', 'civicase.data', 'civiawards.data', ($provide) => {
      $provide.value('crmApi', jasmine.createSpy('crmApi'));
      $provide.value('$window', { location: {} });
      $provide.value('$location', jasmine.createSpyObj('$location', ['path']));
    }));

    beforeEach(inject((_$q_, _$controller_, _$window_, _$location_, _$rootScope_, _crmApi_,
      _CaseStatus_, _AwardMockData_, _AwardAdditionalDetailsMockData_) => {
      $q = _$q_;
      $window = _$window_;
      $location = _$location_;
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      crmApi = _crmApi_;
      CaseStatus = _CaseStatus_;
      AwardMockData = _AwardMockData_;
      AwardAdditionalDetailsMockData = _AwardAdditionalDetailsMockData_;
      $scope = $rootScope.$new();

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
              awardManagers: []
            });
          });
        });
      });

      describe('when editing existing award', function () {
        beforeEach(() => {
          crmApi.and.returnValue($q.resolve({
            caseType: AwardMockData,
            additionalDetails: AwardAdditionalDetailsMockData
          }));

          spyOn($scope, '$emit');
          createController({ ifNewAward: false });
          $scope.$digest();
        });

        it('fires an event with the award details', () => {
          expect($scope.$emit).toHaveBeenCalledWith('civiawards::edit-award::details-fetched', {
            caseType: {
              id: '10',
              name: 'new_award',
              title: 'New Award',
              description: 'Description',
              is_active: '1',
              weight: '1',
              definition: {
                statuses: ['Open', 'Closed', 'Urgent']
              },
              case_type_category: '4',
              is_forkable: '1',
              is_forked: ''
            },
            additionalDetails: {
              id: '1',
              case_type_id: '10',
              award_type: '1',
              start_date: '2019-10-29',
              end_date: '2019-11-29',
              award_manager: ['2', '1']
            }
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
          setAwardDetails();

          $scope.saveAward();
        });

        it('momentarily disables the save button', () => {
          expect($scope.submitInProgress).toBe(true);
        });

        describe('saving the details', () => {
          beforeEach(() => {
            $scope.$digest();
          });

          it('saves the basic award details', () => {
            expect(crmApi).toHaveBeenCalledWith('CaseType', 'create', {
              sequential: true,
              title: 'title',
              description: 'description',
              is_active: true,
              case_type_category: '3',
              name: 'title'
            });
          });

          it('saves the additional award details', () => {
            expect(crmApi).toHaveBeenCalledWith('AwardDetail', 'create', {
              sequential: true,
              case_type_id: '1',
              start_date: AwardAdditionalDetailsMockData.start_date,
              end_date: AwardAdditionalDetailsMockData.end_date,
              award_type: AwardAdditionalDetailsMockData.award_type,
              award_manager: ['2', '1']
            });
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
          $scope.saveAward();
          $scope.$digest();
        });

        it('saves the basic award details', () => {
          expect(crmApi).toHaveBeenCalledWith('CaseType', 'create', {
            sequential: true,
            title: 'title',
            description: 'description',
            is_active: true,
            case_type_category: '3',
            name: 'title',
            definition: { statuses: ['Open'] },
            id: '10'
          });
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
        expect($location.path).toHaveBeenCalledWith('/awards/10');
      });
    });

    describe('when SAVE AND DONE is clicked', () => {
      beforeEach(() => {
        createController({ ifNewAward: true });
        setAwardDetails();

        $scope.saveAndNavigateToDashboard();
        $scope.$digest();
      });

      it('redirects to award dashboard page', () => {
        expect($window.location.href).toBe('/civicrm/case/a/?case_type_category=awards#/case?case_type_category=awards');
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