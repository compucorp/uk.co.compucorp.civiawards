<?php

/**
 * @file
 * FinanceManagement Settings API file.
 */

use CRM_CiviAwards_Service_FinanceManagementSetting as FinanceManagementSettingService;

/**
 * FinanceManagement.setsettings API specification.
 *
 * @param array $spec
 *   Description of fields supported by this API call.
 */
function _civicrm_api3_finance_management_setsetting_spec(array &$spec) {
  $spec['case_type_category_id'] = [
    'title' => 'Case Type Category ID',
    'description' => 'Case Type Category ID',
  ];

  $spec['value'] = [
    'title' => 'Finance Management Value',
    'description' => 'Finance Management Value',
  ];
}

/**
 * FinanceManagement.setsettings API.
 *
 * @param array $params
 *   API parameters.
 *
 * @return array
 *   API result descriptor
 */
function civicrm_api3_finance_management_setsetting(array $params) {
  $financeManagementSettingService = new FinanceManagementSettingService();
  $financeManagementSettingService->saveForCaseCategory($params['case_type_category_id'], $params['value']);

  return [];
}
