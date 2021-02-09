/* eslint-env jasmine */

((_) => {
  describe('PaymentsTable', () => {
    let $controller, $rootScope, $scope, apiResponses, civicaseCrmApi,
      mockPayments, paymentTypes;
    const mockApplication = {
      id: _.uniqueId()
    };

    beforeEach(module('civiawards-payments-tab', 'civiawards-payments-tab.mocks',
      ($provide) => {
        civicaseCrmApi = jasmine.createSpy('civicaseCrmApi');

        $provide.value('civicaseCrmApi', civicaseCrmApi);
      }));

    beforeEach(injectDependenciesAndMocks);

    describe('controller definition', () => {
      it('defines the controller', () => {
        expect(() => initController()).not.toThrow();
      });
    });

    describe('payment activities', () => {
      beforeEach(() => {
        initController();
      });

      describe('when loading the payment activities', () => {
        it('sets the loading state as true', () => {
          expect($scope.isLoading).toBe(true);
        });

        it('requests the list of payment activities for the current application', () => {
          expect(_.toArray(civicaseCrmApi.calls.mostRecent().args[0]))
            .toContain(['Activity', 'get', {
              sequential: 1,
              case_id: mockApplication.id,
              activity_type_id: 'Awards Payment',
              return: ['id', 'target_contact_id', 'status_id.label',
                'activity_date_time', 'custom'],
              options: { limit: 0 }
            }]);
        });

        it('requests the list of payment activity custom fields', () => {
          expect(_.toArray(civicaseCrmApi.calls.mostRecent().args[0]))
            .toContain(['CustomField', 'get', {
              custom_group_id: 'Awards_Payment_Information'
            }]);
        });
      });

      describe('after the payment activities have been loaded', () => {
        let expectedPayments;

        beforeEach(() => {
          $scope.$digest();

          expectedPayments = _.map(mockPayments, (mockPayment) => jasmine.objectContaining({
            id: mockPayment.id,
            'status_id.label': mockPayment['status_id.label'],
            activity_date_time: mockPayment.activity_date_time,
            target_contact_name: _.chain(mockPayment.target_contact_name)
              .toArray().first().value()
          }));
        });

        it('sets the loading state as false', () => {
          expect($scope.isLoading).toBe(false);
        });

        it('stores the payment activities in the scope', () => {
          expect($scope.payments).toEqual(expectedPayments);
        });
      });

      describe('payment activity custom fields', () => {
        let expectedPayments;

        beforeEach(() => {
          $scope.$digest();

          expectedPayments = _.map(mockPayments, (mockPayment) => jasmine.objectContaining({
            paymentTypeLabel: paymentTypes[mockPayment.custom_11].label,
            custom_Payment_Amount_Currency_Type: mockPayment.custom_12,
            custom_Payment_Amount_Value: mockPayment.custom_13,
            custom_Payee_Ref: mockPayment.custom_14
          }));
        });

        it('replaces the payment activity custom fields with human readable versions', () => {
          expect($scope.payments).toEqual(expectedPayments);
        });
      });
    });

    describe('payments filtering', () => {
      let expectedPayments;

      describe('when filtering payments by their ID', () => {
        beforeEach(() => {
          initController();
          $scope.$digest();

          apiResponses.Activity.values = _.map(mockPayments, (mockPayment) => ({
            ...mockPayment,
            id: _.uniqueId()
          }));

          expectedPayments = _.map(
            apiResponses.Activity.values,
            (mockPayment) => jasmine.objectContaining({
              id: mockPayment.id
            })
          );

          $scope.filterPayments({ id: '123' });
          $scope.$digest();
        });

        it('requests the payment with the ID 123', () => {
          expect(_.toArray(civicaseCrmApi.calls.mostRecent().args[0]))
            .toContain(['Activity', 'get', jasmine.objectContaining({
              id: '123'
            })]);
        });

        it('stores the filtered payments', () => {
          expect($scope.payments).toEqual(expectedPayments);
        });
      });

      describe('when a filter is empty', () => {
        beforeEach(() => {
          initController();
          $scope.$digest();

          $scope.filterPayments({ id: '' });
          $scope.$digest();
        });

        it('does not request activities using the empty filter', () => {
          expect(_.toArray(civicaseCrmApi.calls.mostRecent().args[0]))
            .not.toContain(['Activity', 'get', jasmine.objectContaining({
              id: jasmine.any(String)
            })]);
        });
      });
    });

    /**
     * Initialises the payments table controller.
     */
    function initController () {
      $scope = $rootScope.$new();
      $scope.caseItem = mockApplication;

      $controller('civiawardsPaymentsTableController', {
        $scope
      });
    }

    /**
     * Hoists all dependencies needed by the spec file and provides mock services.
     */
    function injectDependenciesAndMocks () {
      inject((_$controller_, _$rootScope_, $q, mockCustomFields,
        _mockPayments_, _paymentTypes_) => {
        $controller = _$controller_;
        $rootScope = _$rootScope_;
        mockPayments = _mockPayments_;
        paymentTypes = _paymentTypes_;

        apiResponses = {
          Activity: { count: mockPayments.length, values: mockPayments },
          CustomField: { count: mockCustomFields.length, values: mockCustomFields }
        };

        civicaseCrmApi.and.callFake((apiCalls) => {
          if (!_.isObject(apiCalls)) {
            return $q.resolve({ values: [] });
          }

          return $q.resolve(
            _.transform(apiCalls, (requestObject, requestParameters, requestKey) => {
              const entityName = requestParameters[0];
              requestObject[requestKey] = apiResponses[entityName];

              return requestObject;
            })
          );
        });
      });
    }
  });
})(CRM._);
