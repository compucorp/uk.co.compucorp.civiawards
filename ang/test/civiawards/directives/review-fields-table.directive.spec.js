/* eslint-env jasmine */
(function (_) {
  describe('civiawardReviewFieldsTable', () => {
    var $rootScope, $controller, $scope, $q, crmApi, ReviewFieldsMockData, AwardAdditionalDetailsMockData;

    beforeEach(module('civiawards.templates', 'civiawards', 'civicase.data', 'civiawards.data', function ($provide) {
      $provide.value('crmApi', jasmine.createSpy('crmApi'));
    }));

    beforeEach(inject((_$q_, _$controller_, _$rootScope_, _crmApi_, _ReviewFieldsMockData_, _AwardAdditionalDetailsMockData_) => {
      $q = _$q_;
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      crmApi = _crmApi_;
      ReviewFieldsMockData = _ReviewFieldsMockData_;
      AwardAdditionalDetailsMockData = _AwardAdditionalDetailsMockData_;
      $scope = $rootScope.$new();

      crmApi.and.returnValue($q.resolve([
        { values: ReviewFieldsMockData }
      ]));

      $scope.$digest();
    }));

    describe('when the controller initializes', () => {
      beforeEach(() => {
        createController();
        $scope.$digest();
      });

      it('fetches all review fields', () => {
        expect(crmApi).toHaveBeenCalledWith([['CustomField', 'get', {
          sequential: true, custom_group_id: 'Applicant_Review'
        }]]);
      });

      it('displays all review fields', () => {
        expect($scope.reviewFields).toEqual(ReviewFieldsMockData);
      });
    });

    describe('when an review field checkbox is clicked', () => {
      beforeEach(() => {
        createController();
        $scope.additionalDetails = { selectedReviewFields: { 1: true } };

        $scope.toggleReviewField({ id: '1' });
      });

      it('toggles the clicked review field', () => {
        expect($scope.additionalDetails.selectedReviewFields[1]).toEqual(false);
      });
    });

    describe('when an existing awards details are fetched', () => {
      beforeEach(() => {
        createController();
        $scope.additionalDetails = {};

        $scope.$emit('civiawards::edit-award::details-fetched', {
          additionalDetails: AwardAdditionalDetailsMockData
        });
      });

      it('displays the already saved review fields', () => {
        expect($scope.additionalDetails.selectedReviewFields).toEqual({ 19: true, 20: true });
      });
    });

    /**
     * Create Controller
     */
    function createController () {
      $controller('CiviawardReviewFieldsTableController', {
        $scope: $scope
      });
    }
  });
}(CRM._));
