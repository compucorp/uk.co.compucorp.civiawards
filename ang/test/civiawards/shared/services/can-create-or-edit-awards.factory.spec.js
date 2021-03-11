(function () {
  describe('canCreateOrEditAwards', function () {
    let canCreateOrEditAwards, permissions;

    beforeEach(module('civiawards'));

    beforeEach(() => {
      permissions = {
        'administer CiviCase': false,
        'create/edit awards': false
      };

      CRM.checkPerm.and.callFake(checkPermMock);
    });

    describe('when the user has the administer CiviCase permission', () => {
      beforeEach(() => {
        permissions['administer CiviCase'] = true;

        injectDependencies();
      });

      it('returns true', () => {
        expect(canCreateOrEditAwards()).toBe(true);
      });
    });

    describe('when the user has the create/edit awards permission', () => {
      beforeEach(() => {
        permissions['create/edit awards'] = true;

        injectDependencies();
      });

      it('returns true', () => {
        expect(canCreateOrEditAwards()).toBe(true);
      });
    });

    describe('when the user does not have any permissions', () => {
      beforeEach(() => {
        injectDependencies();
      });

      it('returns false', () => {
        expect(canCreateOrEditAwards()).toBe(false);
      });
    });

    /**
     * Mock function to determines if the user has permission
     * using the permissions global object.
     *
     * @param {string} permissionName the name of the permission.
     * @returns {boolean} true if the user has the given permission.
     */
    function checkPermMock (permissionName) {
      return permissions[permissionName];
    }

    /**
     * Injects and hoists the dependencies needed by this spec.
     */
    function injectDependencies () {
      inject((_canCreateOrEditAwards_) => {
        canCreateOrEditAwards = _canCreateOrEditAwards_;
      });
    }
  });
})();
