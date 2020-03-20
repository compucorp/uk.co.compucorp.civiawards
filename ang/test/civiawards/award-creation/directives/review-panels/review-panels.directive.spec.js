/* eslint-env jasmine */
(function (_) {
  describe('civiawardReviewPanels', () => {
    let $rootScope, $controller, $scope, crmApi, $q, crmStatus, ts, ContactsData,
      dialogService, RelationshipTypeData, GroupData, ReviewPanelsMockData;
    const entityActionHandlers = {
      'AwardReviewPanel.create': _.noop,
      'RelationshipType.get': relationshipTypeGetHandler,
      'AwardReviewPanel.get': awardReviewPanelGetHandler,
      'Contact.get': contactGetHandler,
      'Group.get': groupGetHandler
    };

    beforeEach(module('civiawards.templates', 'civiawards', 'crmUtil', 'civicase.data', 'civiawards.data', function ($provide) {
      $provide.value('crmApi', getCrmApiMock());

      $provide.value('dialogService', jasmine.createSpyObj('dialogService', ['open', 'close']));
    }));

    beforeEach(inject((_$q_, _$controller_, _$rootScope_, _crmApi_, _crmStatus_,
      _ts_, _dialogService_, _RelationshipTypeData_, _GroupData_,
      _ReviewPanelsMockData_, _ContactsData_) => {
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

      it('resets all fields inside review panel popup', () => {
        expect($scope.currentReviewPanel).toEqual({
          groups: { include: [], exclude: [] },
          isEnabled: false,
          title: '',
          relationships: [{
            contacts: '',
            type: ''
          }]
        });
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
          $scope.currentReviewPanel.groups = { include: [1, 2], exclude: [3, 4] };
          $scope.currentReviewPanel.relationships = [{
            contacts: '10,11',
            type: '17_a_b'
          }, {
            contacts: '30,31',
            type: '18_b_a'
          }];

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
          expect($scope.currentReviewPanel.relationships).toEqual([
            { contacts: '', type: '' }, { contacts: '', type: '' }
          ]);
        });
      });

      describe('when Remove button is clicked for a specific relationship', () => {
        beforeEach(() => {
          $scope.addMoreRelations();
          $scope.currentReviewPanel.relationships[0] = { contacts: '10', type: '20' };

          $scope.removeRelation(1);
        });

        it('removes the clicked specific relationship selection ui', () => {
          expect($scope.currentReviewPanel.relationships).toEqual([
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

    describe('review panels list', () => {
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

      it('displays all existing review panels for the award', () => {
        expect($scope.existingReviewPanels).toEqual([{
          id: '46',
          title: 'New Panel',
          case_type_id: '62',
          contact_settings: {
            exclude_groups: ['1'],
            include_groups: ['2'],
            relationship: [{
              is_a_to_b: '1',
              relationship_type_id: '14',
              contact_id: ['4', '2']
            }, {
              is_a_to_b: '0',
              relationship_type_id: '14',
              contact_id: ['3', '1']
            }]
          },
          is_active: '1',
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
          }
        }]);
      });
    });

    describe('when editing an existing review panel', () => {
      beforeEach(() => {
        $scope.awardId = '10';
        createController();
        $scope.$digest();

        $scope.handleEditReviewPanel($scope.existingReviewPanels[0]);
      });

      it('open a popup to edit the selected review panel', () => {
        expect($scope.currentReviewPanel).toEqual({
          id: '46',
          groups: {
            include: ['2'],
            exclude: ['1']
          },
          isEnabled: true,
          title: 'New Panel',
          relationships: [{
            contacts: ['4', '2'],
            type: '14_a_b'
          }, {
            contacts: ['3', '1'],
            type: '14_b_a'
          }]
        });

        expect(dialogService.open).toHaveBeenCalledWith('ReviewPanels',
          '~/civiawards/award-creation/directives/review-panels/review-panel-popup.html',
          $scope,
          jasmine.any(Object)
        );
      });
    });

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
     * @returns {object} the mocked response for the AwardReviewPanel.Get
     * api action.
     */
    function awardReviewPanelGetHandler () {
      return {
        is_error: 0,
        version: 3,
        count: ReviewPanelsMockData.length,
        values: _.cloneDeep(ReviewPanelsMockData)
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
