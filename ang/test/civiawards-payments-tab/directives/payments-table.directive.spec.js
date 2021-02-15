/* eslint-env jasmine */

((_) => {
  describe('PaymentsTable', () => {
    let $controller, $rootScope, $scope, apiResponses, civicaseCrmApi,
      expectedPayments, mockPayments, paymentTypes;
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
                'activity_date_time', 'custom', 'status_id.name'],
              options: { offset: 0, limit: 25 }
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
      describe('when filtering payments by their ID', () => {
        beforeEach(() => {
          initController();
          $scope.$digest();

          apiResponses.Activity.get.values = generateNewMockPayments();
          expectedPayments = getExpectedPaymentIds(apiResponses.Activity.get.values);

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

      describe('when filtering by custom fields', () => {
        beforeEach(() => {
          initController();
          $scope.$digest();

          $scope.filterPayments({ custom_Payee_Ref: '123' });
          $scope.$digest();
        });

        it('filters the payment using the real custom field name', () => {
          expect(_.toArray(civicaseCrmApi.calls.mostRecent().args[0]))
            .toContain(['Activity', 'get', jasmine.objectContaining({
              custom_14: '123'
            })]);
        });
      });
    });

    describe('payments refreshing', () => {
      beforeEach(() => {
        initController();
        $scope.$digest();
      });

      describe('when no payments have been filtered', () => {
        beforeEach(() => {
          apiResponses.Activity.get.values = generateNewMockPayments();
          expectedPayments = getExpectedPaymentIds(apiResponses.Activity.get.values);

          $rootScope.$broadcast('civiawards::paymentstable::refresh');
          $scope.$digest();
        });

        it('requests all payments', () => {
          expect(_.toArray(civicaseCrmApi.calls.mostRecent().args[0]))
            .toContain(['Activity', 'get', {
              sequential: 1,
              case_id: mockApplication.id,
              activity_type_id: 'Awards Payment',
              return: jasmine.any(Array),
              options: { offset: 0, limit: 25 }
            }]);
        });

        it('stores the refreshed payments', () => {
          expect($scope.payments).toEqual(expectedPayments);
        });
      });

      describe('when the payments have been filtered', () => {
        beforeEach(() => {
          $scope.filterPayments({ id: '123' });
          $scope.$digest();

          apiResponses.Activity.get.values = generateNewMockPayments();
          expectedPayments = getExpectedPaymentIds(apiResponses.Activity.get.values);

          civicaseCrmApi.calls.reset();
          $rootScope.$broadcast('civiawards::paymentstable::refresh');
          $scope.$digest();
        });

        it('requests the filtered payments', () => {
          expect(_.toArray(civicaseCrmApi.calls.mostRecent().args[0]))
            .toContain(['Activity', 'get', jasmine.objectContaining({
              id: '123'
            })]);
        });

        it('stores the refreshed payments', () => {
          expect($scope.payments).toEqual(expectedPayments);
        });
      });
    });

    describe('payments paging', () => {
      describe('on init', () => {
        beforeEach(() => {
          initController();
        });

        it('defines an paging object with no records', () => {
          expect($scope.paging).toEqual({
            page: 1,
            pageSize: 25,
            total: 0,
            isDisabled: true
          });
        });
      });

      describe('when loading the payments first page', () => {
        beforeEach(() => {
          apiResponses.Activity.getcount = 100;

          initController();
          $scope.$digest();
        });

        it('requests the total number of payments for the given filter', () => {
          expect(_.toArray(civicaseCrmApi.calls.mostRecent().args[0]))
            .toContain(['Activity', 'getcount', {
              case_id: mockApplication.id,
              activity_type_id: 'Awards Payment'
            }]);
        });

        it('stores the total number of payments', () => {
          expect($scope.paging.total).toBe(100);
        });
      });

      describe('when going to a specific filtered page', () => {
        beforeEach(() => {
          initController();
          $scope.$digest();

          apiResponses.Activity.getcount = 100;
          apiResponses.Activity.get.values = generateNewMockPayments();
          expectedPayments = getExpectedPaymentIds(apiResponses.Activity.get.values);

          $scope.filterPayments({ id: '123' });
          $scope.$digest();
          civicaseCrmApi.calls.reset();
          $scope.goToPage(3);
        });

        it('changes the page', () => {
          expect($scope.paging.page).toBe(3);
        });

        it('disables the paging while the records are loading', () => {
          expect($scope.paging.isDisabled).toBe(true);
        });

        it('fetches the records belonging to the given page', () => {
          expect(_.toArray(civicaseCrmApi.calls.mostRecent().args[0]))
            .toContain(['Activity', 'get', jasmine.objectContaining({
              options: {
                offset: 50,
                limit: 25
              }
            })]);
        });

        it('requests the total count for the filtered payments', () => {
          expect(_.toArray(civicaseCrmApi.calls.mostRecent().args[0]))
            .toContain(['Activity', 'getcount', {
              id: '123',
              case_id: mockApplication.id,
              activity_type_id: 'Awards Payment'
            }]);
        });

        describe('after loading the payments page', () => {
          beforeEach(() => {
            $scope.$digest();
          });

          it('stores the total number of payments', () => {
            expect($scope.paging.total).toBe(100);
          });

          it('stores the payments for the given page', () => {
            expect($scope.payments).toEqual(expectedPayments);
          });

          it('enables paging', () => {
            expect($scope.paging.isDisabled).toBe(false);
          });
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
     * Generates new mock payment objects based on the current list of mock payments.
     * The only field that changes is the ID.
     *
     * @returns {object[]} a list of payment objects.
     */
    function generateNewMockPayments () {
      return _.map(mockPayments, (mockPayment) => ({
        ...mockPayment,
        id: _.uniqueId()
      }));
    }

    /**
     * @param {object[]} mockPayments a list of payment objects.
     * @returns {object[]} A list of expected payment objects. The only field
     * that is used for matching is the ID since the rest of the fields might
     * have changed.
     */
    function getExpectedPaymentIds (mockPayments) {
      return _.map(
        mockPayments,
        (mockPayment) => jasmine.objectContaining({
          id: mockPayment.id
        })
      );
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
          Activity: {
            get: { count: mockPayments.length, values: mockPayments },
            getcount: mockPayments.length
          },
          CustomField: {
            get: { count: mockCustomFields.length, values: mockCustomFields }
          }
        };

        civicaseCrmApi.and.callFake((apiCalls) => {
          if (!_.isObject(apiCalls)) {
            return $q.resolve({ values: [] });
          }

          return $q.resolve(
            _.transform(apiCalls, (requestObject, requestParameters, requestKey) => {
              const entityName = requestParameters[0];
              const actionName = requestParameters[1];
              requestObject[requestKey] = apiResponses[entityName][actionName];

              return requestObject;
            })
          );
        });
      });
    }
  });
})(CRM._);
