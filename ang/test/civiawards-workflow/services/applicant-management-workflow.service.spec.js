/* eslint-env jasmine */

((_) => {
  describe('applicant management workflow', () => {
    let $q, $rootScope, $window, civicaseCrmApiMock, CaseTypesMockData,
      AwardAdditionalDetailsMockData, ApplicantManagementWorkflow,
      ContactsCache, ContactsData, processMyAwardsFilterMock;

    beforeEach(module('civiawards-workflow', 'civicase.data', 'civiawards.data', ($provide) => {
      processMyAwardsFilterMock = jasmine.createSpy('processMyAwardsFilter');
      civicaseCrmApiMock = jasmine.createSpy('civicaseCrmApi');

      $provide.value('civicaseCrmApi', civicaseCrmApiMock);
      $provide.value('processMyAwardsFilter', processMyAwardsFilterMock);
      $provide.value('$window', { location: {} });
    }));

    beforeEach(inject((_$q_, _$rootScope_, _ApplicantManagementWorkflow_,
      _$window_, _CaseTypesMockData_, _AwardAdditionalDetailsMockData_,
      _ContactsCache_, _ContactsData_, _processMyAwardsFilter_) => {
      $q = _$q_;
      $window = _$window_;
      $rootScope = _$rootScope_;
      ContactsData = _ContactsData_;
      ApplicantManagementWorkflow = _ApplicantManagementWorkflow_;
      CaseTypesMockData = _CaseTypesMockData_;
      AwardAdditionalDetailsMockData = _AwardAdditionalDetailsMockData_;
      ContactsCache = _ContactsCache_;
    }));

    describe('when getting list of workflow', () => {
      var result, expectedResult, mockWorkflow, mockAwardDetail;

      beforeEach(() => {
        mockWorkflow = CaseTypesMockData.getSequential()[0];
        mockWorkflow['api.AwardDetail.get'] = {
          values: [AwardAdditionalDetailsMockData.get()]
        };

        mockAwardDetail = AwardAdditionalDetailsMockData.get();
        mockAwardDetail['api.CaseType.get'] = {
          values: [{ is_active: true }]
        };

        processMyAwardsFilterMock.and.returnValue($q.resolve([1, 2, 3]));

        civicaseCrmApiMock.and.callFake(function (entity) {
          if (entity === 'CaseType') {
            return $q.resolve({ values: [mockWorkflow] });
          } else if (entity === 'AwardDetail') {
            return $q.resolve({ values: [mockAwardDetail] });
          }
        });

        expectedResult = [_.clone(mockWorkflow)];
        expectedResult[0].awardDetails = expectedResult[0]['api.AwardDetail.get'].values[0];
        expectedResult[0].awardDetailsFormatted = {
          managers: ['Default Organization', 'Default Organization'],
          subtypeLabel: 'Medal'
        };
        delete expectedResult[0]['api.AwardDetail.get'];

        spyOn(ContactsCache, 'add');
        spyOn(ContactsCache, 'getCachedContact');
        ContactsCache.add.and.returnValue($q.resolve());
        ContactsCache.getCachedContact.and.returnValue(ContactsData.values[0]);

        ApplicantManagementWorkflow.getWorkflowsList('some_case_type_category', {
          awardFilter: 'my_awards',
          award_subtype: [4, 5],
          is_active: true,
          is_template: 1
        }).then(function (data) {
          result = data;
        });
        $rootScope.$digest();
      });

      it('fetches the workflows for the case management instance', () => {
        expect(civicaseCrmApiMock).toHaveBeenCalledWith('CaseType', 'get', {
          sequential: 1,
          case_type_category: 'some_case_type_category',
          is_active: true,
          options: { limit: 0 },
          'api.AwardDetail.get': { case_type_id: '$value.id' },
          id: { IN: ['10'] }
        });
      });

      it('displays the list of fetched workflows', () => {
        expect(result).toEqual(expectedResult);
      });

      it('filters by subtype', () => {
        expect(civicaseCrmApiMock).toHaveBeenCalledWith('AwardDetail', 'get', {
          sequential: 1,
          is_template: 1,
          case_type_id: { IN: [1, 2, 3] },
          award_subtype: { IN: [4, 5] }
        });
      });

      it('filters by manager', () => {
        expect(processMyAwardsFilterMock).toHaveBeenCalledWith('my_awards');
      });
    });

    describe('when duplicating a workflow', () => {
      var workflow;

      beforeEach(() => {
        workflow = CaseTypesMockData.getSequential()[0];
        workflow.awardDetails = AwardAdditionalDetailsMockData.get();

        civicaseCrmApiMock.and.returnValue($q.resolve(
          { values: [CaseTypesMockData.getSequential()[0]] }
        ));

        ApplicantManagementWorkflow.createDuplicate(_.clone(workflow));

        $rootScope.$digest();
        $rootScope.$digest();
      });

      it('duplicates the workflow', () => {
        expect(civicaseCrmApiMock).toHaveBeenCalledWith(
          'CaseType', 'create', _.extend({}, workflow, { id: null })
        );

        expect(civicaseCrmApiMock).toHaveBeenCalledWith([
          ['AwardDetail', 'create', _.extend(AwardAdditionalDetailsMockData.get(), {
            id: null,
            case_type_id: '1'
          })]
        ]);
      });
    });

    describe('when redirecting to the create workflow page', () => {
      beforeEach(() => {
        ApplicantManagementWorkflow.redirectToWorkflowCreationScreen({ value: '2' });
      });

      it('redirects to the case management new workflow page', () => {
        expect($window.location.href).toBe('/civicrm/award/a/#/awards/new/2');
      });
    });

    describe('when editing a workflow', () => {
      var returnValue;

      beforeEach(() => {
        var workflow = CaseTypesMockData.getSequential()[0];

        returnValue = ApplicantManagementWorkflow.getEditWorkflowURL(workflow);
      });

      it('redirects to the case type page for the clicked workflow', () => {
        expect(returnValue).toBe('civicrm/award/a/#/awards/1/1');
      });
    });
  });
})(CRM._);
