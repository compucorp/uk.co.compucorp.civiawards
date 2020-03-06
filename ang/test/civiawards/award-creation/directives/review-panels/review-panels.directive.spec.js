/* eslint-env jasmine */
(function (_) {
  describe('civiawardReviewPanels', () => {
    let $rootScope, $controller, $scope, crmApi, $q, crmStatus, ts,
      dialogService, RelationshipTypeData;
    const entityActionHandlers = {
      'AwardReviewPanel.create': _.noop,
      'RelationshipType.get': relationshipTypeGetHandler
    };

    beforeEach(module('civiawards.templates', 'civiawards', 'crmUtil', 'civicase.data', 'civiawards.data', function ($provide) {
      $provide.value('crmApi', getCrmApiMock());

      $provide.value('dialogService', jasmine.createSpyObj('dialogService', ['open']));
    }));

    beforeEach(inject((_$q_, _$controller_, _$rootScope_, _crmApi_, _crmStatus_, _ts_, _dialogService_, _RelationshipTypeData_) => {
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      dialogService = _dialogService_;
      crmApi = _crmApi_;
      crmStatus = _crmStatus_;
      $q = _$q_;
      ts = _ts_;
      RelationshipTypeData = _RelationshipTypeData_;
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

      it('resets all fields inside review panel popup', () => {
        expect($scope.reviewPanel).toEqual({
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
          $scope.reviewPanel.title = 'New Review Panel';
          $scope.reviewPanel.isEnabled = true;
          $scope.reviewPanel.groups = { include: [1, 2], exclude: [3, 4] };
          $scope.reviewPanel.relationships = [{
            contacts: '10,11',
            type: '17_a_b'
          }, {
            contacts: '30,31',
            type: '18_b_a'
          }];

          saveButtonClickHandler();
        });

        it('saves the review panel', () => {
          expect(crmApi).toHaveBeenCalledWith('AwardReviewPanel', 'create', {
            title: 'New Review Panel',
            is_active: true,
            case_type_id: 1,
            exclude_groups: [3, 4],
            include_groups: [1, 2],
            relationship: [{
              is_a_to_b: true,
              relationship_type_id: '17',
              contact_id: ['10', '11']
            }, {
              is_a_to_b: false,
              relationship_type_id: '18',
              contact_id: ['30', '31']
            }]
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
          expect($scope.reviewPanel.relationships).toEqual([
            { contacts: '', type: '' }, { contacts: '', type: '' }
          ]);
        });
      });

      describe('when Remove button is clicked for a specific relationship', () => {
        beforeEach(() => {
          $scope.addMoreRelations();
          $scope.reviewPanel.relationships[0] = { contacts: '10', type: '20' };

          $scope.removeRelation(1);
        });

        it('removes the clicked specific relationship selection ui', () => {
          expect($scope.reviewPanel.relationships).toEqual([
            { contacts: '10', type: '20' }
          ]);
        });
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
