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

  describe('when pressing the clear filter button', () => {
    beforeEach(function () {
      initController();

      $scope.filters = {
        activity_date_time: { BETWEEN: [new Date(), new Date()] },
        custom_Payee_Ref: '2',
        custom_Type: '1',
        id: '1',
        status_id: '11',
        target_contact_id: '2'
      };

      $scope.clearFilters();
    });

    it('resets the filters', () => {
      expect($scope.filters).toEqual({});
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
