/* eslint-env jasmine */

(function (_) {
  describe('Awards Dashboard Action Buttons', () => {
    let AddAwardDashboardActionButton, DashboardActionButtons;
    const expectedActionButton = {
      buttonClass: 'btn btn-primary civicase__dashboard__action-btn civicase__dashboard__action-btn--white',
      iconClass: 'add_circle',
      identifier: 'AddAward',
      label: 'Create new award',
      weight: 100
    };

    beforeEach(module('civicase-base', 'civiawards'));

    beforeEach(inject((_AddAwardDashboardActionButton_, _DashboardActionButtons_) => {
      AddAwardDashboardActionButton = _AddAwardDashboardActionButton_;
      DashboardActionButtons = _DashboardActionButtons_;
    }));

    describe('after the awards module has been configured', () => {
      it('it adds the "Create new award" action button', () => {
        expect(DashboardActionButtons)
          .toContain(jasmine.objectContaining(expectedActionButton));
      });

      it('sets the "AddAward" action button service as the handler', () => {
        expect(DashboardActionButtons)
          .toContain(_.extend({}, expectedActionButton, {
            service: AddAwardDashboardActionButton
          }));
      });
    });
  });
})(CRM._);
