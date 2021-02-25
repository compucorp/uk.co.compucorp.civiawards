((_, angular) => {
  const module = angular.module('civiawards-payments-tab.mocks');

  module.constant('paymentTypes', {
    1: {
      label: 'Stipend/Salary',
      value: '1'
    },
    2: {
      label: 'Expenses',
      value: '2'
    }
  });
})(CRM._, angular);
