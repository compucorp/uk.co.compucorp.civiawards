/* eslint-env jasmine */

((_) => {
  describe('PaymentsTable', () => {
    let $controller, $rootScope, $scope, civicaseCrmApi;
    const mockApplication = {
      id: _.uniqueId()
    };
    const mockPayments = [
      {
        id: _.uniqueId(),
        custom_11: 'Stipend/Salary',
        custom_12: 'GBP',
        custom_13: '1099',
        custom_14: 'N9TT-9G0A-B7FQ-RANC',
        activity_date_time: '2000-01-01 12:00:00',
        target_contact_name: { 10: 'Jon Snow' },
        'status_id.label': 'paid_complete'
      },
      {
        id: _.uniqueId(),
        custom_11: 'Stipend/Salary',
        custom_12: 'GBP',
        custom_13: '1050',
        custom_14: 'QK6A-JI6S-7ETR-0A6C',
        activity_date_time: '2000-05-01 12:00:00',
        target_contact_name: { 11: 'Jon Snow' },
        'status_id.label': 'paid_complete'
      },
      {
        id: _.uniqueId(),
        custom_11: 'Stipend/Salary',
        custom_12: 'GBP',
        custom_13: '1250',
        custom_14: 'SXFP-CHYK-ONI6-S89U',
        activity_date_time: '2000-07-01 12:00:00',
        target_contact_name: { 12: 'Jon Snow' },
        'status_id.label': 'failed_incomplete'
      }
    ];
    const mockFields = [
      {
        id: '11',
        name: 'Type'
      },
      {
        id: '12',
        name: 'Payment_Amount_Currency_Type'
      },
      {
        id: '13',
        name: 'Payment_Amount_Value'
      },
      {
        id: '14',
        name: 'Payee_Ref'
      }
    ];
    const apiResponses = {
      Activity: { count: mockPayments.length, values: mockPayments },
      CustomField: { count: mockFields.length, values: mockFields }
    };

    beforeEach(module('civiawards-payments-tab', ($provide) => {
      civicaseCrmApi = jasmine.createSpy('civicaseCrmApi');

      $provide.value('civicaseCrmApi', civicaseCrmApi);
    }));

    beforeEach(inject((_$controller_, _$rootScope_, $q) => {
      $controller = _$controller_;
      $rootScope = _$rootScope_;

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
    }));

    describe('controller definition', () => {
      it('defines the controller', () => {
        expect(() => initController()).not.toThrow();
      });
    });

    describe('payment activities', () => {
      beforeEach(() => {
        initController();
        $scope.$digest();
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

      describe('stored payments', () => {
        let expectedPayments;

        beforeEach(() => {
          expectedPayments = _.map(mockPayments, (mockPayment) => jasmine.objectContaining({
            id: mockPayment.id,
            'status_id.label': mockPayment['status_id.label'],
            activity_date_time: mockPayment.activity_date_time,
            target_contact_name: _.chain(mockPayment.target_contact_name)
              .toArray().first().value()
          }));
        });

        it('stores the payment activities in the scope', () => {
          expect($scope.payments).toEqual(expectedPayments);
        });
      });

      describe('payment activity custom fields', () => {
        let expectedPayments;

        beforeEach(() => {
          expectedPayments = _.map(mockPayments, (mockPayment) => jasmine.objectContaining({
            custom_Type: mockPayment.custom_11,
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
  });
})(CRM._);
