/* eslint-env jasmine */

((_) => {
  describe('duplicate applicant management workflow', () => {
    let $q, $rootScope, civicaseCrmApiMock, CaseTypesMockData,
      DuplicateWorkflowApplicantmanagement, AwardAdditionalDetailsMockData;

    beforeEach(module('civiawards-workflow', 'civicase.data', 'civiawards.data', ($provide) => {
      civicaseCrmApiMock = jasmine.createSpy('civicaseCrmApi');

      $provide.value('civicaseCrmApi', civicaseCrmApiMock);
    }));

    beforeEach(inject((_$q_, _$rootScope_, _DuplicateWorkflowApplicantmanagement_,
      _CaseTypesMockData_, _AwardAdditionalDetailsMockData_) => {
      $q = _$q_;
      $rootScope = _$rootScope_;
      DuplicateWorkflowApplicantmanagement = _DuplicateWorkflowApplicantmanagement_;
      CaseTypesMockData = _CaseTypesMockData_;
      AwardAdditionalDetailsMockData = _AwardAdditionalDetailsMockData_;
    }));

    describe('when duplicating a workflow', () => {
      var workflow, awardDetail;

      beforeEach(() => {
        awardDetail = AwardAdditionalDetailsMockData.get();
        workflow = CaseTypesMockData.getSequential()[0];

        civicaseCrmApiMock.and.returnValue($q.resolve([
          { values: [awardDetail] },
          { values: [workflow] }
        ]));

        DuplicateWorkflowApplicantmanagement.create(_.clone(workflow));

        $rootScope.$digest();
        $rootScope.$digest();
      });

      it('duplicates the workflow', () => {
        expect(civicaseCrmApiMock).toHaveBeenCalledWith([
          ['AwardDetail', 'get', { sequential: 1, case_type_id: workflow.id }],
          ['CaseType', 'create', _.extend({}, workflow, { id: null })]
        ]);

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
