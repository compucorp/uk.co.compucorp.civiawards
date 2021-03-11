((_, getCrmUrl) => {
  describe('workflow duplicate controller', () => {
    let $controller, $rootScope, $scope, $window, CaseTypesMockData;

    beforeEach(module('civiawards-workflow', 'civicase.data', ($provide) => {
      $provide.value('$window', { location: {} });
    }));

    beforeEach(inject((_$controller_, _$rootScope_, _$window_,
      _CaseTypesMockData_) => {
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      $window = _$window_;
      CaseTypesMockData = _CaseTypesMockData_;
    }));

    describe('when clicked', () => {
      var workflowObject, expectedUrl;

      beforeEach(() => {
        workflowObject = CaseTypesMockData.getSequential()[0];

        initController();

        $scope.clickHandler(workflowObject);

        expectedUrl = getCrmUrl('civicrm/a/#/caseType/1');
      });

      it('redirects to the core screen to edit the workflow', () => {
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
})(CRM._, CRM.url);
