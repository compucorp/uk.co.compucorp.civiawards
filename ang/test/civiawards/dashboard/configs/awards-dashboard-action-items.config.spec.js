/* eslint-env jasmine */

(function (_) {
  describe('Awards Dashboard Action Items', () => {
    let DashboardActionItems;
    const expectedActionItems = [{
      templateUrl: '~/civiawards/dashboard/directives/more-filters-dashboard-action-button.html',
      weight: -10
    }, {
      templateUrl: '~/civiawards/dashboard/directives/dashboard-action-separator.html',
      weight: -5
    }, {
      templateUrl: '~/civiawards/dashboard/directives/add-award-dashboard-action-button.html',
      weight: 100
    }];

    beforeEach(module('civicase-base', 'civiawards'));

    beforeEach(inject((_DashboardActionItems_) => {
      DashboardActionItems = _DashboardActionItems_;
    }));

    describe('after the awards module has been configured', () => {
      it('it adds the "Create new award" action button', () => {
        expect(DashboardActionItems)
          .toEqual(expectedActionItems);
      });
    });
  });
})(CRM._);
