<?php

/**
 * Class for interacting with the FinanceManagement Setting.
 */
class CRM_CiviAwards_Service_FinanceManagementSetting {

  /**
   * Finance management name.
   */
  const FINANCE_MANAGEMENT_NAME = 'case_category_finance_management';

  /**
   * Saves the financial management value for the case category.
   *
   * @param int $caseCategoryId
   *   Case category Id.
   * @param bool $financeManagementValue
   *   Finance Management Value.
   */
  public function saveForCaseCategory($caseCategoryId, $financeManagementValue) {
    $financeManagementValues = $this->get();
    $financeManagementValues[$caseCategoryId] = $financeManagementValue;
    Civi::settings()->set(self::FINANCE_MANAGEMENT_NAME, $financeManagementValues);
  }

  /**
   * Deletes the finance management value for the case category.
   *
   * @param int $caseCategoryId
   *   Case category Id.
   */
  public function deleteForCaseCategory($caseCategoryId) {
    $financeManagementValues = $this->get();
    if (empty($financeManagementValues) || !isset($financeManagementValues[$caseCategoryId])) {
      return;
    }

    unset($financeManagementValues[$caseCategoryId]);
    Civi::settings()->set(self::FINANCE_MANAGEMENT_NAME, $financeManagementValues);
  }

  /**
   * Gets the financial management settings.
   *
   * @param int|null $caseCategoryId
   *   Case category Id.
   *
   * @return array|null
   *   Financial management settings value.
   */
  public function get($caseCategoryId = NULL) {
    $financeManagementValues = Civi::settings()->get(self::FINANCE_MANAGEMENT_NAME);

    if (!$caseCategoryId) {
      return !empty($financeManagementValues) ? $financeManagementValues : [];
    }

    return !empty($financeManagementValues[$caseCategoryId]) ? $financeManagementValues[$caseCategoryId] : NULL;
  }

}
