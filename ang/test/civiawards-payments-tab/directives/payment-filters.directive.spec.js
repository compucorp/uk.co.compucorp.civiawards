/* eslint-env jasmine */

describe('Payment Filters', () => {
  let $controller, $rootScope, $scope;

  beforeEach(module('civiawards-payments-tab', 'civiawards-payments-tab.mocks'));

  beforeEach(inject((_$controller_, _$rootScope_) => {
    $controller = _$controller_;
    $rootScope = _$rootScope_;
  }));

  describe('when initialising the controller', () => {
    it('does not throw an error', () => {
      expect(() => initController()).not.toThrow();
    });
  });

  describe('payment type options', () => {
    beforeEach(initController);

    it('stores the list of payment types as select2 options', () => {
      expect($scope.paymentTypeOptions).toEqual([
        jasmine.objectContaining({ id: '1', text: 'Stipend/Salary' }),
        jasmine.objectContaining({ id: '2', text: 'Expenses' })
      ]);
    });
  });

  describe('payment status options', () => {
    beforeEach(initController);

    it('stores the list of payment statuses as select2 options', () => {
      expect($scope.paymentStatusOptions).toEqual([
        jasmine.objectContaining({ id: '11', text: 'Applied for (incomplete)' }),
        jasmine.objectContaining({ id: '12', text: 'Approved (complete)' }),
        jasmine.objectContaining({ id: '13', text: 'Exported (complete)' }),
        jasmine.objectContaining({ id: '14', text: 'Paid (complete)' }),
        jasmine.objectContaining({ id: '15', text: 'Cancelled (cancelled)' }),
        jasmine.objectContaining({ id: '16', text: 'Failed (incomplete)' })
      ]);
    });
  });

  describe('when content needs to be refreshed', () => {
    beforeEach(() => {
      initController();
      $rootScope.$broadcast('civiawards::paymentstable::refresh');
      $scope.$digest();
    });

    it('refreshes the content with filters', () => {
      expect($scope.onFilter).toHaveBeenCalledWith({ $filters: $scope.filters });
    });
  });

  /**
   * Initialises the payment filters controller.
   */
  function initController () {
    $scope = $rootScope.$new();
    $scope.onFilter = jasmine.createSpy('onFilter');

    $controller('civiawardsPaymentFiltersController', {
      $scope: $scope
    });
  }
});
