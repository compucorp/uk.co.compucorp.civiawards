/* eslint-env jasmine */
(function (_) {
  describe('civiaward', () => {
    var $q, $controller, $rootScope, $scope, $window, crmApi,
      CaseStatus, AwardData, AwardAdditionalDetailsData;

    beforeEach(module('civicase-base', 'civiawards.templates', 'civiawards', 'civicase.data', 'civiawards.data', ($provide) => {
      $provide.value('crmApi', jasmine.createSpy('crmApi'));
      $provide.value('$window', { location: {} });
    }));

    beforeEach(inject((_$q_, _$controller_, _$window_, _$rootScope_, _crmApi_,
      _CaseStatus_, _AwardData_, _AwardAdditionalDetailsData_) => {
      $q = _$q_;
      $window = _$window_;
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      crmApi = _crmApi_;
      CaseStatus = _CaseStatus_;
      AwardData = _AwardData_;
      AwardAdditionalDetailsData = _AwardAdditionalDetailsData_;
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

        it('name field is disabled', () => {
          expect($scope.isNameDisabled).toBe(true);
        });

        it('submit button is disabled', () => {
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

          it('all basic details fields are empty', () => {
            expect($scope.basicDetails).toEqual({
              title: '',
              name: '',
              description: '',
              awardType: null,
              isEnabled: true,
              startDate: null,
              endDate: null,
              awardManagers: [],
              selectedAwardStages: expectedSelectedAwardStages
            });
          });
        });

        describe('name field', () => {
          beforeEach(() => {
            $scope.basicDetails.title = 'Some custom text';
            $scope.$digest();
          });

          it('updating the title changes the name field too', () => {
            expect($scope.basicDetails.name).toBe('some_custom_text');
          });

          describe('if the name is manually changed', () => {
            beforeEach(() => {
              $scope.basic_details_form.awardName.$pristine = false;
              $scope.basicDetails.title = 'another custom text';
              $scope.$digest();
            });

            it('does not changes the name field when editing title', () => {
              expect($scope.basicDetails.name).toBe('some_custom_text');
            });
          });
        });
      });

      describe('when editing existing award', function () {
        beforeEach(() => {
          crmApi.and.returnValue($q.resolve({
            caseType: AwardData,
            additionalDetails: AwardAdditionalDetailsData
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
          expect($scope.basicDetails.title).toBe(AwardData.title);
          expect($scope.basicDetails.name).toBe(AwardData.name);
          expect($scope.basicDetails.description).toBe(AwardData.description);
          expect($scope.basicDetails.isEnabled).toBe(AwardData.is_active === '1');
        });

        it('displays the additional awards information', () => {
          expect($scope.awardDetailsID).toBe(AwardAdditionalDetailsData.id);
          expect($scope.basicDetails.startDate).toBe(AwardAdditionalDetailsData.start_date);
          expect($scope.basicDetails.endDate).toBe(AwardAdditionalDetailsData.end_date);
          expect($scope.basicDetails.awardType).toBe(AwardAdditionalDetailsData.award_type);
          expect($scope.basicDetails.awardManagers).toBe(AwardAdditionalDetailsData.award_manager.toString());
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

    describe('when saving award', () => {
      describe('when creating a new award', () => {
        beforeEach(() => {
          createController({ ifNewAward: true });
          $scope.basicDetails.title = 'title';
          $scope.basicDetails.description = 'description';
          $scope.basicDetails.isEnabled = true;
          $scope.basicDetails.selectedAwardStages = { 1: true };
          $scope.basicDetails.awardManagers = '2,1';
          $scope.basicDetails.startDate = AwardAdditionalDetailsData.start_date;
          $scope.basicDetails.endDate = AwardAdditionalDetailsData.end_date;
          $scope.basicDetails.awardType = AwardAdditionalDetailsData.award_type;

          crmApi.and.returnValue($q.resolve({
            values: [AwardAdditionalDetailsData]
          }));
          $scope.saveAward();
        });

        it('disables the save button initially', () => {
          expect($scope.submitInProgress).toBe(true);
        });

        describe('saves the details', () => {
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
              start_date: AwardAdditionalDetailsData.start_date,
              end_date: AwardAdditionalDetailsData.end_date,
              award_type: AwardAdditionalDetailsData.award_type,
              award_manager: ['2', '1']
            });
          });

          it('redirects to dashboard page', () => {
            expect($window.location.href).toBe('/civicrm/case/a/?case_type_category=awards#/case?case_type_category=awards');
          });

          it('enables the save button finally', () => {
            expect($scope.submitInProgress).toBe(false);
          });
        });
      });
    });

    describe('create new award stage', () => {
      var loadFormSpy, loadFormCallBackFn;

      beforeEach(() => {
        loadFormSpy = jasmine.createSpy();
        loadFormSpy.and.callFake(function (eventName, callBackFn) {
          loadFormCallBackFn = callBackFn;
        });

        CRM.url = jasmine.createSpy();
        CRM.loadForm = jasmine.createSpy();
        CRM.loadForm.and.returnValue({
          on: loadFormSpy
        });

        createController({ ifNewAward: true });
        $scope.createNewAwardStage();
      });

      it('opens the popup to create new award stage', () => {
        expect(CRM.url).toHaveBeenCalledWith('civicrm/admin/options/case_status',
          { action: 'add', reset: 1 });
      });

      describe('when popup is closed', () => {
        beforeEach(() => {
          loadFormCallBackFn({}, { optionValue: { value: 'someValue' } });
        });

        it('captures the form success event', () => {
          expect(loadFormSpy).toHaveBeenCalledWith('crmFormSuccess', jasmine.any(Function));
        });

        it('shows the newly created award stage', () => {
          expect($scope.awardStages.someValue).toEqual({ value: 'someValue' });
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
