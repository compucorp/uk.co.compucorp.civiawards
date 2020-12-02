/* eslint-env jasmine */
(function (_) {
  describe('civiawardReviewFieldsTable', () => {
    var $rootScope, $controller, $scope, AwardSubtypeMockData, Select2Utils;

    beforeEach(module('civiawards-workflow', 'civiawards.data'));

    beforeEach(inject((_$controller_, _$rootScope_, _Select2Utils_,
      _AwardSubtypeMockData_) => {
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      AwardSubtypeMockData = _AwardSubtypeMockData_;
      Select2Utils = _Select2Utils_;
      $scope = $rootScope.$new();
      $scope.caseTypeCategory = 'case type category';

      $scope.$digest();
    }));

    describe('when the controller initializes', () => {
      beforeEach(() => {
        createController();
        $scope.$digest();
      });

      it('shows all sub types as filters', () => {
        expect($scope.allSubtypes).toEqual(_.map(AwardSubtypeMockData, Select2Utils.mapSelectOptions));
      });

      it('shows a filter to select where I am the manager', () => {
        expect($scope.awardOptions).toEqual([
          { text: 'My case type category', id: 'my_awards' },
          { text: 'All case type category', id: 'all_awards' }
        ]);
      });
    });

    /**
     * Create Controller
     */
    function createController () {
      $controller('CiviAwardWorkflowFilterController', {
        $scope: $scope
      });
    }
  });
}(CRM._));
