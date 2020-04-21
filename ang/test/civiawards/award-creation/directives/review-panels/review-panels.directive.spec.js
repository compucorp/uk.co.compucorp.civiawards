/* eslint-env jasmine */
(function (_) {
  describe('civiawardReviewPanels', () => {
    let $rootScope, $controller, $scope, crmApi, $q, crmStatus, ts, ContactsData,
      dialogService, RelationshipTypeData, GroupData, ReviewPanelsMockData,
      entityActionHandlers, TagsMockData;

    beforeEach(module('civiawards.templates', 'civiawards', 'crmUtil', 'civicase.data', 'civiawards.data', function ($provide) {
      $provide.value('crmApi', getCrmApiMock());

      $provide.value('dialogService', jasmine.createSpyObj('dialogService', ['open', 'close']));
    }));

    beforeEach(inject((_$q_, _$controller_, _$rootScope_, _crmApi_, _crmStatus_,
      _ts_, _dialogService_, _RelationshipTypeData_, _GroupData_,
      _ReviewPanelsMockData_, _ContactsData_, _TagsMockData_) => {
      setApiActionHandlers();
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      dialogService = _dialogService_;
      crmApi = _crmApi_;
      crmStatus = _crmStatus_;
      $q = _$q_;
      ts = _ts_;
      RelationshipTypeData = _RelationshipTypeData_;
      ReviewPanelsMockData = _ReviewPanelsMockData_;
      ContactsData = _ContactsData_;
      TagsMockData = _TagsMockData_;
      GroupData = _GroupData_;
      $scope = $rootScope.$new();

      dialogService.dialogs = {};

      $scope.$digest();
    }));

    describe('when the controller initializes', () => {
      beforeEach(() => {
        createController();
      });

      it('disables the save button inside the review panel popup', () => {
        expect($scope.submitInProgress).toBe(false);
      });

      it('starts fetching required data', () => {
        expect($scope.isLoading).toBe(true);
      });

      it('resets the review panel list', () => {
        expect($scope.existingReviewPanels).toEqual([]);
      });

      it('shows all the tabs inside the add/edit review panel popup', () => {
        expect($scope.tabs).toEqual($scope.tabs = [
          { name: 'people', label: ts('People') },
          { name: 'applications', label: ts('Applications') },
          { name: 'permissions', label: ts('Permissions') }
        ]);
      });

      it('defaults to the poeple tab inside review panel popup', () => {
        expect($scope.activeTab).toBe('people');
      });

      it('resets all fields inside review panel popup', () => {
        expect($scope.currentReviewPanel).toEqual({
          title: '',
          isEnabled: false,
          visibilitySettings: {
            selectedApplicantStatus: '',
            anonymizeApplication: true,
            tags: [],
            isApplicationStatusRestricted: false,
            restrictedApplicationStatus: []
          },
          contactSettings: {
            groups: { include: [], exclude: [] },
            relationships: [{
              contacts: '',
              type: ''
            }]
          }
        });
      });

      it('shows list of application statuses inside the applications tab of review panel popup', () => {
        expect($scope.applicantStatusSelect2Options).toEqual([
          { id: '1', text: 'Ongoing', name: 'Open' },
          { id: '2', text: 'Resolved', name: 'Closed' },
          { id: '3', text: 'Urgent', name: 'Urgent' }
        ]);
      });
    });

    describe('when "Add Review Panel" button is clicked', () => {
      var saveButtonClickHandler;

      beforeEach(() => {
        createController();
        $scope.review_panel_form = {};

        dialogService.open.and.callFake(function (__, ___, ____, options) {
          saveButtonClickHandler = options.buttons[0].click;
        });

        $scope.openCreateReviewPanelPopup();
      });

      it('opens the review panel popup', () => {
        expect(dialogService.open).toHaveBeenCalledWith('ReviewPanels',
          '~/civiawards/award-creation/directives/review-panels/review-panel-popup.html',
          $scope,
          jasmine.any(Object)
        );
      });

      describe('button disablity', () => {
        describe('when the form is not valid', () => {
          beforeEach(() => {
            $scope.review_panel_form.$valid = false;
            saveButtonClickHandler();
          });

          it('does not save the review panel', () => {
            expect(crmApi).not.toHaveBeenCalledWith('AwardReviewPanel', 'create', jasmine.any(Object));
          });
        });

        describe('when the form is valid but save is in progress', () => {
          beforeEach(() => {
            $scope.review_panel_form.$valid = true;
            $scope.submitInProgress = true;
            saveButtonClickHandler();
          });

          it('does not save the review panel', () => {
            expect(crmApi).not.toHaveBeenCalledWith('AwardReviewPanel', 'create', jasmine.any(Object));
          });
        });
      });

      describe('click event', () => {
        beforeEach(() => {
          $scope.awardId = 1;
          $scope.review_panel_form.$valid = true;
          $scope.submitInProgress = false;
          $scope.currentReviewPanel.title = 'New Review Panel';
          $scope.currentReviewPanel.isEnabled = true;
          $scope.currentReviewPanel.contactSettings.groups = { include: [1, 2], exclude: [3, 4] };
          $scope.currentReviewPanel.contactSettings.relationships = [{
            contacts: '10,11',
            type: '17_a_b'
          }, {
            contacts: '30,31',
            type: '18_b_a'
          }];
          $scope.currentReviewPanel.visibilitySettings.selectedApplicantStatus = '1,2';
          $scope.currentReviewPanel.visibilitySettings.tags = ['1', '2', '12', '13'];
          $scope.currentReviewPanel.visibilitySettings.isApplicationStatusRestricted = true;
          $scope.currentReviewPanel.visibilitySettings.restrictedApplicationStatus = '3,4';

          $scope.$digest();
          saveButtonClickHandler();
        });

        it('saves a new review panel', () => {
          expect(crmApi).toHaveBeenCalledWith('AwardReviewPanel', 'create', {
            id: null,
            title: 'New Review Panel',
            is_active: '1',
            case_type_id: 1,
            contact_settings: {
              exclude_groups: [3, 4],
              include_groups: [1, 2],
              relationship: [{
                is_a_to_b: '1',
                relationship_type_id: '17',
                contact_id: ['10', '11']
              }, {
                is_a_to_b: '0',
                relationship_type_id: '18',
                contact_id: ['30', '31']
              }]
            },
            visibility_settings: {
              application_status: ['1', '2'],
              anonymize_application: '1',
              application_tags: ['1', '2', '12', '13'],
              is_application_status_restricted: '1',
              restricted_application_status: ['3', '4']
            }
          });
        });

        it('shows a notification when save is in progress', () => {
          expect(crmStatus).toHaveBeenCalledWith({
            start: ts('Saving Review Panel...'),
            success: ts('Review Panel Saved')
          }, jasmine.any(Object));
        });
      });

      describe('delete button', () => {
        describe('when creating new review panel', () => {
          it('displays only the save button inside the popup', () => {
            expect(dialogService.open).toHaveBeenCalledWith('ReviewPanels',
              '~/civiawards/award-creation/directives/review-panels/review-panel-popup.html',
              $scope,
              jasmine.objectContaining({
                buttons: [jasmine.objectContaining({
                  text: ts('Save'),
                  icons: { primary: 'fa-check' }
                })]
              })
            );
          });
        });

        describe('when editing existing review panel', () => {
          beforeEach(() => {
            createController();
            $scope.review_panel_form = {};

            dialogService.open.and.callFake(function (__, ___, ____, options) {
              saveButtonClickHandler = options.buttons[0].click;
            });

            $scope.currentReviewPanel.id = '1';

            $scope.openCreateReviewPanelPopup();
          });

          it('displays both the save button and delete button inside the popup', () => {
            expect(dialogService.open).toHaveBeenCalledWith('ReviewPanels',
              '~/civiawards/award-creation/directives/review-panels/review-panel-popup.html',
              $scope,
              jasmine.objectContaining({
                buttons: [jasmine.objectContaining({
                  text: ts('Save'),
                  icons: { primary: 'fa-check' }
                }), jasmine.objectContaining({
                  text: ts('Delete'),
                  icons: { primary: 'fa-times' }
                })]
              })
            );
          });
        });
      });
    });

    describe('relationship types', () => {
      beforeEach(() => {
        createController();
        $scope.$digest();
      });

      it('fetches all relationship types', () => {
        expect(crmApi).toHaveBeenCalledWith('RelationshipType', 'get', {
          sequential: 1,
          is_active: 1,
          options: { sort: 'label_a_b', limit: 0 }
        });
      });

      it('displays all relationship type on the UI', () => {
        expect($scope.relationshipTypes).toEqual([{
          text: 'Application Manager is',
          id: '17_a_b'
        }, {
          text: 'Application Manager',
          id: '17_b_a'
        }, {
          text: 'Benefits Specialist is',
          id: '14_a_b'
        }, {
          text: 'Benefits Specialist',
          id: '14_b_a'
        }]);
      });

      describe('when ADD MORE button is clicked for a specific relationship', () => {
        beforeEach(() => {
          $scope.addMoreRelations();
        });

        it('adds one more specific relationship selection ui', () => {
          expect($scope.currentReviewPanel.contactSettings.relationships).toEqual([
            { contacts: '', type: '' }, { contacts: '', type: '' }
          ]);
        });
      });

      describe('when Remove button is clicked for a specific relationship', () => {
        beforeEach(() => {
          $scope.addMoreRelations();
          $scope.currentReviewPanel.contactSettings.relationships[0] = { contacts: '10', type: '20' };

          $scope.removeRelation(1);
        });

        it('removes the clicked specific relationship selection ui', () => {
          expect($scope.currentReviewPanel.contactSettings.relationships).toEqual([
            { contacts: '10', type: '20' }
          ]);
        });
      });
    });

    describe('groups', () => {
      beforeEach(() => {
        createController();
        $scope.$digest();
      });

      it('fetches all groups', () => {
        expect(crmApi).toHaveBeenCalledWith('Group', 'get', {
          sequential: 1,
          return: ['id', 'title'],
          options: { limit: 0 }
        });
      });
    });

    describe('tags', () => {
      beforeEach(() => {
        createController();
        $scope.$digest();
      });

      it('fetches all tags for cases', () => {
        expect(crmApi).toHaveBeenCalledWith('Tag', 'get', {
          sequential: 1,
          used_for: 'Cases',
          options: { limit: 0 }
        });
      });
    });

    describe('review panels list', () => {
      describe('when there is atleast one relationship', () => {
        beforeEach(() => {
          $scope.awardId = '10';
          createController();
          $scope.$digest();
        });

        it('fetches all exisiting review panels for the award', () => {
          expect(crmApi).toHaveBeenCalledWith('AwardReviewPanel', 'get', {
            sequential: 1,
            case_type_id: '10',
            options: { limit: 0 }
          });
        });

        describe('existing review panels', () => {
          let expectedReviewPanelsValue;

          beforeEach(() => {
            const reviewPanel1 = _.extend(ReviewPanelsMockData[0], {
              formattedContactSettings: {
                include: ['Group 2'],
                exclude: ['Group 1'],
                relation: [{
                  relationshipLabel: 'Benefits Specialist is',
                  contacts: ['Shauna Barkley', 'Shauna Wattson']
                }, {
                  relationshipLabel: 'Benefits Specialist',
                  contacts: ['Kiara Jones', 'Default Organization']
                }]
              },
              formattedVisibilitySettings: {
                applicationStatus: ['Ongoing'],
                tags: ['Non-profit', 'Strenuous', 'awq']
              }
            });

            const reviewPanel2 = _.extend(ReviewPanelsMockData[1], {
              formattedContactSettings: {
                include: [],
                exclude: [],
                relation: []
              },
              formattedVisibilitySettings: {
                applicationStatus: [],
                tags: []
              }
            });

            expectedReviewPanelsValue = [reviewPanel1, reviewPanel2];
          });

          it('displays all existing review panels for the award', () => {
            expect($scope.existingReviewPanels).toEqual(expectedReviewPanelsValue);
          });
        });

        it('shows the contacts name in the contact list', () => {
          expect(crmApi).toHaveBeenCalledWith('Contact', 'get', {
            sequential: 1,
            return: ['display_name'],
            id: { IN: ['4', '2', '3', '1'] },
            options: { limit: 0 }
          });
        });
      });

      describe('when there no relationships in all review panels', () => {
        beforeEach(() => {
          setApiActionHandlers({ noRelationPresentInAllReviewPanels: true });
          $scope.awardId = '10';
          createController();
          $scope.$digest();
        });

        it('fetches all exisiting review panels for the award', () => {
          expect(crmApi).toHaveBeenCalledWith('AwardReviewPanel', 'get', {
            sequential: 1,
            case_type_id: '10',
            options: { limit: 0 }
          });
        });

        describe('existing review panels', () => {
          let expectedReviewPanelsValue;

          beforeEach(() => {
            const reviewPanel = _.extend(ReviewPanelsMockData[1], {
              formattedContactSettings: {
                include: [],
                exclude: [],
                relation: []
              },
              formattedVisibilitySettings: {
                applicationStatus: [],
                tags: []
              }
            });

            expectedReviewPanelsValue = [reviewPanel];
          });

          it('displays all existing review panels for the award', () => {
            expect($scope.existingReviewPanels).toEqual(expectedReviewPanelsValue);
          });
        });

        it('does not fetch any contact information', () => {
          expect(crmApi).not.toHaveBeenCalledWith('Contact', 'get', jasmine.any(Object));
        });
      });
    });

    describe('when editing an existing review panel', () => {
      let expectedReviewPanel;

      beforeEach(() => {
        $scope.awardId = '10';
        createController();
        $scope.$digest();

        $scope.handleEditReviewPanel($scope.existingReviewPanels[0]);

        expectedReviewPanel = {
          id: '46',
          isEnabled: true,
          title: 'New Panel',
          contactSettings: {
            groups: {
              include: ['2'],
              exclude: ['1']
            },
            relationships: [{
              contacts: ['4', '2'],
              type: '14_a_b'
            }, {
              contacts: ['3', '1'],
              type: '14_b_a'
            }]
          },
          visibilitySettings: {
            selectedApplicantStatus: ['1'],
            anonymizeApplication: true,
            tags: ['1', '12', '15'],
            isApplicationStatusRestricted: false,
            restrictedApplicationStatus: ['1', '2']
          }
        };
      });

      it('open a popup to edit the selected review panel', () => {
        expect($scope.currentReviewPanel).toEqual(expectedReviewPanel);

        expect(dialogService.open).toHaveBeenCalledWith('ReviewPanels',
          '~/civiawards/award-creation/directives/review-panels/review-panel-popup.html',
          $scope,
          jasmine.any(Object)
        );
      });
    });

    describe('when deleting an existing review panel', () => {
      let crmConfirmOnFn, crmConfirmCallbackFn;

      beforeEach(() => {
        crmConfirmOnFn = jasmine.createSpy('crmConfirmOnFn').and.callFake(function (evtName, crmConfirmCallbackFntn) {
          crmConfirmCallbackFn = crmConfirmCallbackFntn;
        });

        CRM.confirm.and.returnValue({ on: crmConfirmOnFn });
      });

      beforeEach(() => {
        $scope.awardId = '10';
        createController();
        $scope.$digest();

        $scope.handleDeleteReviewPanel($scope.existingReviewPanels[0]);
      });

      it('confirms before deleting the review panel', () => {
        expect(CRM.confirm).toHaveBeenCalledWith({
          title: 'Delete New Panel?',
          message: 'Are you sure you want to delete this?'
        });
      });

      describe('when yes is pressed on confirmation prompt', () => {
        beforeEach(() => {
          crmConfirmCallbackFn();
          $scope.$digest();
        });

        it('deletes the review panel', () => {
          expect(crmConfirmOnFn).toHaveBeenCalledWith('crmConfirm:yes', jasmine.any(Function));
          expect(crmApi).toHaveBeenCalledWith('AwardReviewPanel', 'delete', {
            id: '46'
          });
        });

        it('refreshes the review panel list', () => {
          expect(crmApi).toHaveBeenCalledWith('AwardReviewPanel', 'get', {
            sequential: 1,
            case_type_id: '10',
            options: { limit: 0 }
          });
        });
      });
    });

    describe('when enabling an existing review panel', () => {
      let crmConfirmOnFn, crmConfirmCallbackFn;

      beforeEach(() => {
        crmConfirmOnFn = jasmine.createSpy('crmConfirmOnFn').and.callFake(function (evtName, crmConfirmCallbackFntn) {
          crmConfirmCallbackFn = crmConfirmCallbackFntn;
        });

        CRM.confirm.and.returnValue({ on: crmConfirmOnFn });
      });

      beforeEach(() => {
        $scope.awardId = '10';
        createController();
        $scope.$digest();

        const reviewPanel = $scope.existingReviewPanels[0];
        reviewPanel.is_active = '0';

        $scope.handleActiveStateReviewPanel(reviewPanel);
      });

      it('confirms before enabling the review panel', () => {
        expect(CRM.confirm).toHaveBeenCalledWith({
          title: 'Enable New Panel?',
          message: 'Are you sure you want to enable this?'
        });
      });

      describe('when yes is pressed on confirmation prompt', () => {
        beforeEach(() => {
          crmConfirmCallbackFn();
          $scope.$digest();
        });

        it('enables the review panel', () => {
          expect(crmConfirmOnFn).toHaveBeenCalledWith('crmConfirm:yes', jasmine.any(Function));
          expect(crmApi).toHaveBeenCalledWith('AwardReviewPanel', 'create', {
            id: '46',
            is_active: '1'
          });
        });

        it('refreshes the review panel list', () => {
          expect(crmApi).toHaveBeenCalledWith('AwardReviewPanel', 'get', {
            sequential: 1,
            case_type_id: '10',
            options: { limit: 0 }
          });
        });
      });
    });

    describe('when disabling an existing review panel', () => {
      let crmConfirmOnFn, crmConfirmCallbackFn;

      beforeEach(() => {
        crmConfirmOnFn = jasmine.createSpy('crmConfirmOnFn').and.callFake(function (evtName, crmConfirmCallbackFntn) {
          crmConfirmCallbackFn = crmConfirmCallbackFntn;
        });

        CRM.confirm.and.returnValue({ on: crmConfirmOnFn });
      });

      beforeEach(() => {
        $scope.awardId = '10';
        createController();
        $scope.$digest();

        const reviewPanel = $scope.existingReviewPanels[0];
        reviewPanel.is_active = '1';

        $scope.handleActiveStateReviewPanel(reviewPanel);
      });

      it('confirms before disabling the review panel', () => {
        expect(CRM.confirm).toHaveBeenCalledWith({
          title: 'Disable New Panel?',
          message: 'Are you sure you want to disable this?'
        });
      });

      describe('when yes is pressed on confirmation prompt', () => {
        beforeEach(() => {
          crmConfirmCallbackFn();
          $scope.$digest();
        });

        it('disables the review panel', () => {
          expect(crmConfirmOnFn).toHaveBeenCalledWith('crmConfirm:yes', jasmine.any(Function));
          expect(crmApi).toHaveBeenCalledWith('AwardReviewPanel', 'create', {
            id: '46',
            is_active: '0'
          });
        });

        it('refreshes the review panel list', () => {
          expect(crmApi).toHaveBeenCalledWith('AwardReviewPanel', 'get', {
            sequential: 1,
            case_type_id: '10',
            options: { limit: 0 }
          });
        });
      });
    });

    /**
     * Set Api Action Handlers
     *
     * @param {object} options configurations
     * @param {string} options.noRelationPresentInAllReviewPanels if no relationships are present in all fetched review panels
     */
    function setApiActionHandlers (options) {
      options = options || {};

      entityActionHandlers = {
        'AwardReviewPanel.create': _.noop,
        'RelationshipType.get': relationshipTypeGetHandler,
        'AwardReviewPanel.get': function () {
          return awardReviewPanelGetHandler(options);
        },
        'Contact.get': contactGetHandler,
        'Group.get': groupGetHandler,
        'Tag.get': tagGetHandler
      };
    }
    /**
     * Create Controller
     */
    function createController () {
      $controller('CiviawardReviewPanelsController', {
        $scope: $scope
      });
    }

    /**
     * @returns {object} the mocked response for the RelationshipType.Get api action.
     */
    function relationshipTypeGetHandler () {
      return {
        is_error: 0,
        version: 3,
        count: RelationshipTypeData.values.length,
        values: _.cloneDeep(RelationshipTypeData.values)
      };
    }

    /**
     * @returns {object} the mocked response for the Groups.Get api action.
     */
    function groupGetHandler () {
      return {
        is_error: 0,
        version: 3,
        count: GroupData.values.length,
        values: _.cloneDeep(GroupData.values)
      };
    }

    /**
     * @returns {object} the mocked response for the Tag.Get api action.
     */
    function tagGetHandler () {
      return {
        is_error: 0,
        version: 3,
        count: TagsMockData.get().length,
        values: _.cloneDeep(TagsMockData.get())
      };
    }

    /**
     * @param {object} options configurations
     * @param {string} options.noRelationPresentInAllReviewPanels if no relationships are present in all fetched review panels
     * @returns {object} the mocked response for the AwardReviewPanel.Get
     * api action.
     */
    function awardReviewPanelGetHandler (options) {
      var mockData = options.noRelationPresentInAllReviewPanels
        ? [ReviewPanelsMockData[1]]
        : ReviewPanelsMockData;

      return {
        is_error: 0,
        version: 3,
        count: mockData.length,
        values: _.cloneDeep(mockData)
      };
    }

    /**
     * @returns {object} the mocked response for the Contact.Get
     * api action.
     */
    function contactGetHandler () {
      return {
        is_error: 0,
        version: 3,
        count: ContactsData.values.length,
        values: _.cloneDeep(ContactsData.values)
      };
    }

    /**
     * @returns {Function} Returns a spy function that can replace `CRM.api3`. It also returns a mocked response
     * if there's a handler defined in the `entityActionHandler` map. The name of the handler follows the structure
     * "EntityName.ActionName".
     */
    function getCrmApiMock () {
      return jasmine.createSpy('crmApi')
        .and.callFake((entityName, action, params) => {
          const entityAction = `${entityName}.${action}`;
          const entityActionHandler = entityActionHandlers[entityAction];
          const response = entityActionHandler
            ? entityActionHandler()
            : { values: [] };

          return $q.resolve(response);
        });
    }
  });
}(CRM._));
