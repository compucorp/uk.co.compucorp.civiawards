/* eslint-env jasmine */

(function (_) {
  describe('Awards Dashboard Action Buttons', () => {
    let AddAwardDashboardActionButton, DashboardActionButtons;
    const expectedActionButton = {
      buttonClass: 'btn btn-primary civicase__dashboard__action-btn civicase__dashboard__action-btn--light',
      iconClass: 'add_circle',
      identifier: 'AddAward',
      label: 'Create new award',
      weight: 100
    };

    beforeEach(module('civicase-base', 'civiawards'));

    describe('when the user can create awards', () => {
      beforeEach(() => {
        CRM.checkPerm.and.returnValue(true);

        injectDependencies();
      });

      describe('after the awards module has been configured', () => {
        it('checks if the user can create awards', () => {
          expect(CRM.checkPerm).toHaveBeenCalledWith('create/edit awards');
        });

        it('adds the "Create new award" action button', () => {
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

    describe('when the user cannot create awards', () => {
      beforeEach(() => {
        CRM.checkPerm.and.returnValue(false);

        injectDependencies();
      });

      describe('after the awards module has been configured', () => {
        it('checks if the user can create awards', () => {
          expect(CRM.checkPerm).toHaveBeenCalledWith('create/edit awards');
        });

        it('does not add the "Create new award" action button', () => {
          expect(DashboardActionButtons)
            .not.toContain(jasmine.objectContaining(expectedActionButton));
        });
      });
    });

    /**
     * Injects and hoists the dependencies needed for the test.
     */
    function injectDependencies () {
      inject((_AddAwardDashboardActionButton_, _DashboardActionButtons_) => {
        AddAwardDashboardActionButton = _AddAwardDashboardActionButton_;
        DashboardActionButtons = _DashboardActionButtons_;
      });
    }
  });
})(CRM._);
