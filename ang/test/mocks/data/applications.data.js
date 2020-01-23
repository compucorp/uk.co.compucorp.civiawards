(() => {
  var module = angular.module('civiawards.data');

  module.constant('ApplicationsMockData', [
    {
      id: 50,
      case_type_id: '10',
      subject: 'Award Application',
      start_date: '2019-12-01',
      status_id: '1',
      is_deleted: '0',
      created_date: '2019-12-1 08:00:00',
      modified_date: '2019-12-1 08:00:00',
      contact_id: {
        1: '2'
      },
      client_id: {
        1: '2'
      }
    }
  ]);
})();
