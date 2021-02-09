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

  /**
   * Initialises the payment filters controller.
   */
  function initController () {
    $scope = $rootScope.$new();

    $controller('civiawardsPaymentFiltersController', {
      $scope: $scope
    });
  }
});
