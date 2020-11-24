/* eslint-env jasmine */

((_) => {
  describe('applicant management workflow', () => {
    let $q, $rootScope, civicaseCrmApiMock, CaseTypesMockData,
      AwardAdditionalDetailsMockData, ApplicantmanagementWorkflow,
      ContactsCache, ContactsData;

    beforeEach(module('civiawards-workflow', 'civicase.data', 'civiawards.data', ($provide) => {
      civicaseCrmApiMock = jasmine.createSpy('civicaseCrmApi');

      $provide.value('civicaseCrmApi', civicaseCrmApiMock);
    }));

    beforeEach(inject((_$q_, _$rootScope_, _ApplicantmanagementWorkflow_,
      _CaseTypesMockData_, _AwardAdditionalDetailsMockData_,
      _ContactsCache_, _ContactsData_) => {
      $q = _$q_;
      $rootScope = _$rootScope_;
      ContactsData = _ContactsData_;
      ApplicantmanagementWorkflow = _ApplicantmanagementWorkflow_;
      CaseTypesMockData = _CaseTypesMockData_;
      AwardAdditionalDetailsMockData = _AwardAdditionalDetailsMockData_;
      ContactsCache = _ContactsCache_;
    }));

    describe('when getting list of workflow', () => {
      var result, expectedResult;

      beforeEach(() => {
        var mockWorkflow = CaseTypesMockData.getSequential()[0];
        mockWorkflow['api.AwardDetail.get'] = {
          values: [AwardAdditionalDetailsMockData.get()]
        };

        civicaseCrmApiMock.and.returnValue($q.resolve(
          { values: [mockWorkflow] }
        ));

        expectedResult = [_.clone(mockWorkflow)];
        expectedResult[0].awardDetails = expectedResult[0]['api.AwardDetail.get'].values[0];
        expectedResult[0].awardDetailsFormatted = {
          managers: ['Default Organization', 'Default Organization'],
          subtypeLabel: 'Medal'
        };
        delete expectedResult[0]['api.AwardDetail.get'];

        spyOn(ContactsCache, 'getCachedContact');
        ContactsCache.getCachedContact.and.returnValue(ContactsData.values[0]);

        ApplicantmanagementWorkflow.getWorkflowsList('some_case_type_category')
          .then(function (data) {
            result = data;
          });
        $rootScope.$digest();
      });

      it('fetches the workflows for the case management instance', () => {
        expect(civicaseCrmApiMock).toHaveBeenCalledWith('CaseType', 'get', {
          sequential: 1,
          case_type_category: 'some_case_type_category',
          options: { limit: 0 },
          'api.AwardDetail.get': { case_type_id: '$value.id' }
        });
      });

      it('displays the list of fetched workflows', () => {
        expect(result).toEqual(expectedResult);
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

        ApplicantmanagementWorkflow.createDuplicate(_.clone(workflow));

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
  });
})(CRM._);
