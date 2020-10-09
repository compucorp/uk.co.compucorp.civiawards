/* eslint-env jasmine */
(function (_, $) {
  describe('civiawardCustomFieldSets', () => {
    var civiawardCustomFieldSetsDirective, $compile, $rootScope, $scope;

    beforeEach(module('civiawards.templates', 'civiawards'));

    beforeEach(inject((_$compile_, _$rootScope_) => {
      $compile = _$compile_;
      $rootScope = _$rootScope_;
    }));

    describe('basic tests', () => {
      describe('when creating new award', function () {
        beforeEach(() => {
          CRM.loadForm.calls.reset();
          initDirective();
        });

        it('does not fetch the custom fields information', () => {
          expect(CRM.loadForm).not.toHaveBeenCalled();
        });
      });

      describe('when editing existing award', function () {
        beforeEach(() => {
          CRM.loadForm.calls.reset();
          initDirective(5);
        });

        it('shows the custom field set UI', () => {
          expect(CRM.loadForm).toHaveBeenCalledWith('/civicrm/award/customfield?entityId=5', {
            target: jasmine.any(Object)
          });
        });
      });
    });

    describe('when saving the award', () => {
      var originalTriggerFn, originalUnblockFn;
      var promise, customFieldSetsTabCallbackFn;

      beforeEach(() => {
        initDirective(5);

        var customFieldSetsTabObj = _.find($scope.tabs, function (tabObj) {
          return tabObj.name === 'customFieldSets';
        });

        originalTriggerFn = $.fn.trigger;
        originalUnblockFn = $.fn.unblock;
        spyOn($.fn, 'trigger').and.callThrough();
        spyOn($.fn, 'unblock').and.callThrough();

        promise = customFieldSetsTabObj.save();

        customFieldSetsTabCallbackFn = jasmine.createSpy('promise');
        promise.then(customFieldSetsTabCallbackFn);
      });

      afterEach(() => {
        $.fn.trigger = originalTriggerFn;
        $.fn.unblock = originalUnblockFn;
      });

      it('saves the custom field set values', () => {
        expect($.fn.trigger).toHaveBeenCalledWith('click');
      });

      describe('when the save is complete', () => {
        beforeEach(() => {
          civiawardCustomFieldSetsDirective.trigger('crmFormSuccess');
          $scope.$digest();
        });

        it('saves the award itself', () => {
          expect(customFieldSetsTabCallbackFn).toHaveBeenCalled();
        });

        it('hides the loading screen for custom field sets', () => {
          expect($.fn.unblock).toHaveBeenCalled();
        });
      });
    });

    /**
     * Initialise directive
     *
     * @param {string/number} awardId award id
     */
    function initDirective (awardId) {
      var html = '<civiaward-custom-field-sets></civiaward-custom-field-sets>';
      $scope = $rootScope.$new();
      $scope.awardId = awardId;
      $scope.tabs = [
        { name: 'basicDetails', label: 'Basic Details' },
        { name: 'stages', label: 'Award Stages' },
        { name: 'customFieldSets', label: 'Custom Field Sets' },
        { name: 'reviewPanels', label: 'Panels' },
        { name: 'reviewFields', label: 'Review Fields' }
      ];

      civiawardCustomFieldSetsDirective = $compile(html)($scope);

      $scope.$digest();
    }
  });
}(CRM._, CRM.$));
