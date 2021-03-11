((_) => {
  describe('workflow duplicate controller', () => {
    let $controller, $rootScope, $scope, $window, CaseTypesMockData,
      civicaseCrmUrl;

    beforeEach(module('civiawards-workflow', 'civicase.data', ($provide) => {
      $provide.value('$window', { location: {} });
    }));

    beforeEach(inject((_$controller_, _$rootScope_, _$window_,
      _CaseTypesMockData_, _civicaseCrmUrl_) => {
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      $window = _$window_;
      CaseTypesMockData = _CaseTypesMockData_;
      civicaseCrmUrl = _civicaseCrmUrl_;
    }));

    describe('when clicked', () => {
      var workflowObject;
      var expectedUrl = 'workflow/click/url';

      beforeEach(() => {
        workflowObject = CaseTypesMockData.getSequential()[0];

        initController();
        civicaseCrmUrl.and.returnValue(expectedUrl);

        $scope.clickHandler(workflowObject);
      });

      it('redirects to the core screen to edit the workflow', () => {
        expect(civicaseCrmUrl).toHaveBeenCalledWith('civicrm/a/#/caseType/1');
        expect($window.location.href).toBe(expectedUrl);
      });
    });

    /**
     * Initializes the workflow advanced controller.
     */
    function initController () {
      $scope = $rootScope.$new();

      $controller('WorkflowAdvancedController', { $scope: $scope });
    }
  });
})(CRM._);
