-- /*******************************************************
-- *
-- * Clean up the exisiting tables
-- *
-- *******************************************************/

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `civicrm_civiawards_award_manager`;
DROP TABLE IF EXISTS `civicrm_civiawards_award_detail`;

SET FOREIGN_KEY_CHECKS=1;
