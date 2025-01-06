<?php

use CRM_Civicase_Helper_OptionValues as OptionValuesHelper;
use CRM_CiviAwards_Service_FinanceManagementSetting as FinanceManagementSettingService;

/**
 * Get a list of settings for angular pages.
 */
class CRM_CiviAwards_Settings {

  /**
   * Get a list of settings for angular pages.
   */
  public static function getBaseSettings(): array {
    $options = ['awardSubtypes' => 'civiawards_award_subtype'];

    OptionValuesHelper::setToJsVariables($options);

    return $options;
  }

  /**
   * Get a list of settings for payment tab.
   */
  public static function getPaymentTabSettings(): array {
    $options = ['payment_types' => 'budget_line_type'];

    OptionValuesHelper::setToJsVariables($options);

    $financeManagementSettingService = new FinanceManagementSettingService();
    $options['instances_finance_support'] = $financeManagementSettingService->get();

    return $options;
  }

}
