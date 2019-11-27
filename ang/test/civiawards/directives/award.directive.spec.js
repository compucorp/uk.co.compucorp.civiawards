/* eslint-env jasmine */
(function (_) {
  describe('civiaward', () => {
    var $q, $controller, $rootScope, $scope, $window, crmApi,
      CaseStatus, AwardMockData, AwardAdditionalDetailsMockData;

    beforeEach(module('civicase-base', 'civiawards.templates', 'civiawards', 'civicase.data', 'civiawards.data', ($provide) => {
      $provide.value('crmApi', jasmine.createSpy('crmApi'));
      $provide.value('$window', { location: {} });
    }));

    beforeEach(inject((_$q_, _$controller_, _$window_, _$rootScope_, _crmApi_,
      _CaseStatus_, _AwardMockData_, _AwardAdditionalDetailsMockData_) => {
      $q = _$q_;
      $window = _$window_;
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

        it('disables the name field', () => {
          expect($scope.isNameDisabled).toBe(true);
        });

        it('disables the submit button', () => {
          expect($scope.submitInProgress).toBe(false);
        });

        it('shows all award stages', () => {
          expect($scope.awardStages).toBe(CaseStatus.getAll());
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
              isEnabled: true,
              selectedAwardStages: expectedSelectedAwardStages
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

        describe('when the title changes', () => {
          beforeEach(() => {
            $scope.basicDetails.title = 'Some custom text';
            $scope.$digest();
          });

          it('updating the title changes the name field too', () => {
            expect($scope.basicDetails.name).toBe('some_custom_text');
          });
        });

        describe('if the name is manually changed', () => {
          beforeEach(() => {
            $scope.basicDetails.title = 'Some custom text';
            $scope.$digest();
            $scope.basic_details_form.awardName.$pristine = false;
            $scope.basicDetails.title = 'another custom text';
            $scope.$digest();
          });

          it('does not changes the name field when editing title', () => {
            expect($scope.basicDetails.name).toBe('some_custom_text');
          });
        });
      });

      describe('when editing existing award', function () {
        beforeEach(() => {
          crmApi.and.returnValue($q.resolve({
            caseType: AwardMockData,
            additionalDetails: AwardAdditionalDetailsMockData
          }));

          createController({ ifNewAward: false });
          $scope.$digest();
        });

        it('fetches the award information', () => {
          expect(crmApi).toHaveBeenCalledWith({
            caseType: ['CaseType', 'getsingle', { sequential: true, id: '10' }],
            additionalDetails: ['AwardDetail', 'getsingle', { sequential: true, case_type_id: '10' }]
          });
        });

        it('displays the basic awards information', () => {
          expect($scope.basicDetails.title).toBe(AwardMockData.title);
          expect($scope.basicDetails.name).toBe(AwardMockData.name);
          expect($scope.basicDetails.description).toBe(AwardMockData.description);
          expect($scope.basicDetails.isEnabled).toBe(AwardMockData.is_active === '1');
        });

        it('displays the additional awards information', () => {
          expect($scope.awardDetailsID).toBe(AwardAdditionalDetailsMockData.id);
          expect($scope.additionalDetails.startDate).toBe(AwardAdditionalDetailsMockData.start_date);
          expect($scope.additionalDetails.endDate).toBe(AwardAdditionalDetailsMockData.end_date);
          expect($scope.additionalDetails.awardType).toBe(AwardAdditionalDetailsMockData.award_type);
          expect($scope.additionalDetails.awardManagers).toBe(AwardAdditionalDetailsMockData.award_manager.toString());
        });

        describe('name field', () => {
          beforeEach(() => {
            $scope.basicDetails.title = 'Some custom text';
            $scope.$digest();
          });

          it('updating the title does not change the name field', () => {
            expect($scope.basicDetails.name).toBe('new_award');
          });
        });
      });
    });

    describe('when saving the award', () => {
      describe('when creating a new award', () => {
        beforeEach(() => {
          createController({ ifNewAward: true });
          $scope.basicDetails.title = 'title';
          $scope.basicDetails.description = 'description';
          $scope.basicDetails.isEnabled = true;
          $scope.basicDetails.selectedAwardStages = { 1: true };
          $scope.additionalDetails.awardManagers = '2,1';
          $scope.additionalDetails.startDate = AwardAdditionalDetailsMockData.start_date;
          $scope.additionalDetails.endDate = AwardAdditionalDetailsMockData.end_date;
          $scope.additionalDetails.awardType = AwardAdditionalDetailsMockData.award_type;

          crmApi.and.returnValue($q.resolve({
            values: [AwardAdditionalDetailsMockData]
          }));
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
              name: '',
              definition: { statuses: ['Open'] }
            });
          });

          it('saves the additional award details', () => {
            expect(crmApi).toHaveBeenCalledWith('AwardDetail', 'create', {
              case_type_id: '1',
              start_date: AwardAdditionalDetailsMockData.start_date,
              end_date: AwardAdditionalDetailsMockData.end_date,
              award_type: AwardAdditionalDetailsMockData.award_type,
              award_manager: ['2', '1']
            });
          });

          it('redirects to dashboard page', () => {
            expect($window.location.href).toBe('/civicrm/case/a/?case_type_category=awards#/case?case_type_category=awards');
          });

          it('enables the save button after api has responded', () => {
            expect($scope.submitInProgress).toBe(false);
          });
        });
      });
    });

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
