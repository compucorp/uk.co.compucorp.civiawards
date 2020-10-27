/* eslint-env jasmine */
(function (_) {
  describe('civiawardBasicDetailsForm', () => {
    var $rootScope, $controller, $scope, AwardMockData,
      AwardAdditionalDetailsMockData;

    beforeEach(module('civiawards.templates', 'civiawards', 'civicase.data', 'civiawards.data'));

    beforeEach(inject((_$controller_, _$rootScope_, _AwardMockData_, _AwardAdditionalDetailsMockData_) => {
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      AwardMockData = _AwardMockData_;
      AwardAdditionalDetailsMockData = _AwardAdditionalDetailsMockData_.get();
      $scope = $rootScope.$new();

      $scope.$digest();
    }));

    describe('when the controller initializes', () => {
      beforeEach(() => {
        createController();
      });

      it('displays all award types', () => {
        expect($scope.awardTypeSelect2Options).toEqual([
          { id: '1', text: 'Medal', name: 'Medal' },
          { id: '2', text: 'Prize', name: 'Prize' },
          { id: '3', text: 'Programme', name: 'Programme' },
          { id: '4', text: 'Scholarship', name: 'Scholarship' },
          { id: '5', text: 'Award', name: 'Award' },
          { id: '6', text: 'Grant', name: 'Grant' }
        ]);
      });
    });

    describe('when an existing awards details are fetched', () => {
      beforeEach(() => {
        createController();
        $scope.basicDetails = {};
        $scope.additionalDetails = {};

        $scope.$emit('civiawards::edit-award::details-fetched', {
          caseType: AwardMockData[0],
          additionalDetails: AwardAdditionalDetailsMockData
        });
      });

      it('sets the basic information', () => {
        expect($scope.pageTitle).toEqual('Configure Award - New Award');
        expect($scope.basicDetails.title).toEqual('New Award');
        expect($scope.basicDetails.name).toEqual('new_award');
        expect($scope.basicDetails.description).toEqual('Description');
        expect($scope.basicDetails.isEnabled).toEqual(true);
      });

      it('sets the additional information', () => {
        expect($scope.awardDetailsID).toEqual('1');
        expect($scope.additionalDetails.startDate).toEqual('2019-10-29');
        expect($scope.additionalDetails.endDate).toEqual('2019-11-29');
        expect($scope.additionalDetails.awardType).toEqual('1');
        expect($scope.additionalDetails.awardManagers).toEqual('2,1');
      });
    });

    /**
     * Create Controller
     */
    function createController () {
      $controller('CiviawardBasicDetailsFormController', {
        $scope: $scope
      });
    }
  });
}(CRM._));
