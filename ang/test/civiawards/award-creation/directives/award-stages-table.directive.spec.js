(function (_) {
  describe('civiawardAwardStagesTable', () => {
    var $rootScope, $controller, $scope, CaseStatus, AwardMockData;

    beforeEach(module('civiawards.templates', 'civiawards', 'civicase.data', 'civiawards.data'));

    beforeEach(inject((_$controller_, _$rootScope_, _CaseStatus_, _AwardMockData_) => {
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      CaseStatus = _CaseStatus_;
      AwardMockData = _AwardMockData_;
      $scope = $rootScope.$new();

      $scope.$digest();
    }));

    describe('when the controller initializes', () => {
      beforeEach(() => {
        createController();
      });

      it('displays all award stages', () => {
        expect($scope.awardStages).toEqual(CaseStatus.getAll());
      });
    });

    describe('when an award checkbox is clicked', () => {
      beforeEach(() => {
        createController();
        $scope.basicDetails = { selectedAwardStages: { 1: true } };

        $scope.toggleAwardStage({ value: '1' });
      });

      it('toggles the clicked award stage', () => {
        expect($scope.basicDetails.selectedAwardStages[1]).toEqual(false);
      });
    });

    describe('when an existing awards details are fetched', () => {
      beforeEach(() => {
        createController();
        $scope.basicDetails = {};

        $scope.$emit('civiawards::edit-award::details-fetched', {
          caseType: AwardMockData[0]
        });
      });

      it('displays the already saved award stages', () => {
        expect($scope.basicDetails.selectedAwardStages).toEqual({ 1: true, 2: true, 3: true });
      });
    });

    /**
     * Create Controller
     */
    function createController () {
      $controller('CiviawardAwardStagesTableController', {
        $scope: $scope
      });
    }
  });
}(CRM._));
