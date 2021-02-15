((_, angular) => {
  const module = angular.module('civiawards-payments-tab.mocks');

  module.constant('mockCustomFields', [
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
  ]);
})(CRM._, angular);
