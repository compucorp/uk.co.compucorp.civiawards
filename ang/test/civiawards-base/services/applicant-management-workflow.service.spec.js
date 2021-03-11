((_) => {
  describe('applicant management workflow', () => {
    let $q, $rootScope, $window, civicaseCrmApiMock, CaseTypesMockData,
      AwardAdditionalDetailsMockData, ApplicantManagementWorkflow,
      ContactsCache, ContactsData;

    beforeEach(module('civiawards-workflow', 'civicase.data', 'civiawards.data', ($provide) => {
      civicaseCrmApiMock = jasmine.createSpy('civicaseCrmApi');

      $provide.value('civicaseCrmApi', civicaseCrmApiMock);
      $provide.value('$window', { location: {} });
    }));

    beforeEach(inject((_$q_, _$rootScope_, _ApplicantManagementWorkflow_,
      _$window_, _CaseTypesMockData_, _AwardAdditionalDetailsMockData_,
      _ContactsCache_, _ContactsData_) => {
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
      var result, expectedResult, mockWorkflow;

      describe('for manage workflow page', () => {
        beforeEach(() => {
          mockWorkflow = CaseTypesMockData.getSequential()[0];
          mockWorkflow['api.AwardDetail.get'] = {
            values: [AwardAdditionalDetailsMockData.get()]
          };

          civicaseCrmApiMock.and.returnValue($q.resolve([
            { values: [mockWorkflow] },
            1
          ]));

          expectedResult = {
            values: [_.clone(mockWorkflow)],
            count: 1
          };
          expectedResult.values[0].awardDetails = expectedResult.values[0]['api.AwardDetail.get'].values[0];
          expectedResult.values[0].awardDetailsFormatted = {
            managers: ['Default Organization', 'Default Organization'],
            subtypeLabel: 'Medal'
          };
          delete expectedResult.values[0]['api.AwardDetail.get'];

          spyOn(ContactsCache, 'add');
          spyOn(ContactsCache, 'getCachedContact');
          ContactsCache.add.and.returnValue($q.resolve());
          ContactsCache.getCachedContact.and.returnValue(ContactsData.values[0]);
        });

        describe('when page in page 1', () => {
          beforeEach(() => {
            ApplicantManagementWorkflow.getWorkflowsListForManageWorkflow({
              case_type_category: 'some_case_type_category',
              managed_by: 203,
              award_detail_params: {
                award_subtype: [4, 5],
                is_template: 1
              },
              is_active: true

            }, {
              size: 25,
              num: 1
            }, true).then(function (data) {
              result = data;
            });
            $rootScope.$digest();
          });

          it('fetches the first 25 workflows for the case management instance', () => {
            expect(civicaseCrmApiMock).toHaveBeenCalledWith([[
              'Award',
              'get',
              {
                managed_by: 203,
                award_detail_params: {
                  is_template: 1,
                  award_subtype: { IN: [4, 5] }
                },
                case_type_params: {
                  sequential: 1,
                  case_type_category: 'some_case_type_category',
                  is_active: true,
                  options: { limit: 25, offset: 0 },
                  'api.AwardDetail.get': { case_type_id: '$value.id' }
                }
              }
            ],
            [
              'Award',
              'getcount',
              {
                managed_by: 203,
                award_detail_params: {
                  is_template: 1,
                  award_subtype: { IN: [4, 5] }
                },
                case_type_params: {
                  sequential: 1,
                  case_type_category: 'some_case_type_category',
                  is_active: true,
                  'api.AwardDetail.get': { case_type_id: '$value.id' }
                }
              }
            ]]);
          });

          it('displays the results in a list view', () => {
            expect(result).toEqual(expectedResult);
          });
        });

        describe('when page in page 2', () => {
          beforeEach(() => {
            ApplicantManagementWorkflow.getWorkflowsListForManageWorkflow({
              case_type_category: 'some_case_type_category',
              managed_by: 203,
              award_detail_params: {
                award_subtype: [4, 5],
                is_template: 1
              },
              is_active: true
            }, {
              size: 25,
              num: 2
            }, true).then(function (data) {
              result = data;
            });
            $rootScope.$digest();
          });

          it('fetches the 26th to 50th workflows for the case management instance', () => {
            expect(civicaseCrmApiMock).toHaveBeenCalledWith([[
              'Award',
              'get',
              {
                managed_by: 203,
                award_detail_params: {
                  is_template: 1,
                  award_subtype: { IN: [4, 5] }
                },
                case_type_params: {
                  sequential: 1,
                  case_type_category: 'some_case_type_category',
                  is_active: true,
                  options: { limit: 25, offset: 25 },
                  'api.AwardDetail.get': { case_type_id: '$value.id' }
                }
              }
            ],
            [
              'Award',
              'getcount',
              {
                managed_by: 203,
                award_detail_params: {
                  is_template: 1,
                  award_subtype: { IN: [4, 5] }
                },
                case_type_params: {
                  sequential: 1,
                  case_type_category: 'some_case_type_category',
                  is_active: true,
                  'api.AwardDetail.get': { case_type_id: '$value.id' }
                }
              }
            ]]);
          });

          it('displays the results in a list view', () => {
            expect(result).toEqual(expectedResult);
          });
        });
      });

      describe('for case overview page', () => {
        beforeEach(() => {
          mockWorkflow = CaseTypesMockData.getSequential()[0];

          civicaseCrmApiMock.and.returnValue($q.resolve([
            { values: [mockWorkflow] },
            1
          ]));

          expectedResult = {
            values: [_.clone(mockWorkflow)],
            count: 1
          };

          spyOn(ContactsCache, 'add');
          spyOn(ContactsCache, 'getCachedContact');
          ContactsCache.add.and.returnValue($q.resolve());
          ContactsCache.getCachedContact.and.returnValue(ContactsData.values[0]);
        });

        describe('when page in page 1', () => {
          beforeEach(() => {
            ApplicantManagementWorkflow.getWorkflowsListForCaseOverview({
              case_type_category: 'some_case_type_category',
              managed_by: 203,
              award_detail_params: {
                award_subtype: [4, 5],
                is_template: 1
              },
              is_active: true

            }, {
              size: 25,
              num: 1
            }, true).then(function (data) {
              result = data;
            });
            $rootScope.$digest();
          });

          it('fetches the first 25 workflows for the case management instance', () => {
            expect(civicaseCrmApiMock).toHaveBeenCalledWith([[
              'Award',
              'get',
              {
                managed_by: 203,
                award_detail_params: {
                  is_template: 1,
                  award_subtype: { IN: [4, 5] }
                },
                case_type_params: {
                  sequential: 1,
                  case_type_category: 'some_case_type_category',
                  is_active: true,
                  options: { limit: 25, offset: 0 }
                }
              }
            ],
            [
              'Award',
              'getcount',
              {
                managed_by: 203,
                award_detail_params: {
                  is_template: 1,
                  award_subtype: { IN: [4, 5] }
                },
                case_type_params: {
                  sequential: 1,
                  case_type_category: 'some_case_type_category',
                  is_active: true
                }
              }
            ]]);
          });

          it('displays the results in a list view', () => {
            expect(result).toEqual(expectedResult);
          });
        });

        describe('when page in page 2', () => {
          beforeEach(() => {
            ApplicantManagementWorkflow.getWorkflowsListForCaseOverview({
              case_type_category: 'some_case_type_category',
              managed_by: 203,
              award_detail_params: {
                award_subtype: [4, 5],
                is_template: 1
              },
              is_active: true
            }, {
              size: 25,
              num: 2
            }, true).then(function (data) {
              result = data;
            });
            $rootScope.$digest();
          });

          it('fetches the 26th to 50th workflows for the case management instance', () => {
            expect(civicaseCrmApiMock).toHaveBeenCalledWith([[
              'Award',
              'get',
              {
                managed_by: 203,
                award_detail_params: {
                  is_template: 1,
                  award_subtype: { IN: [4, 5] }
                },
                case_type_params: {
                  sequential: 1,
                  case_type_category: 'some_case_type_category',
                  is_active: true,
                  options: { limit: 25, offset: 25 }
                }
              }
            ],
            [
              'Award',
              'getcount',
              {
                managed_by: 203,
                award_detail_params: {
                  is_template: 1,
                  award_subtype: { IN: [4, 5] }
                },
                case_type_params: {
                  sequential: 1,
                  case_type_category: 'some_case_type_category',
                  is_active: true
                }
              }
            ]]);
          });

          it('displays the results in a list view', () => {
            expect(result).toEqual(expectedResult);
          });
        });
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
        expect($window.location.href).toBe('/civicrm/award/a/#/awards/new/2/workflow');
      });
    });

    describe('when editing a workflow', () => {
      var returnValue;

      beforeEach(() => {
        var workflow = CaseTypesMockData.getSequential()[0];

        returnValue = ApplicantManagementWorkflow.getEditWorkflowURL(workflow);
      });

      it('redirects to the case type page for the clicked workflow', () => {
        expect(returnValue).toBe('civicrm/award/a/#/awards/1/1/workflow');
      });
    });

    describe('when loading dashboard', () => {
      var returnValue;

      beforeEach(() => {
        returnValue = ApplicantManagementWorkflow.getActivityFilters();
      });

      it('shows the cases from active case types, non deleted contacts and managed by current user', () => {
        expect(returnValue).toEqual({
          case_filter: {
            'case_type_id.is_active': 1,
            contact_is_deleted: 0,
            'case_type_id.managed_by': 203
          }
        });
      });
    });
  });
})(CRM._);
