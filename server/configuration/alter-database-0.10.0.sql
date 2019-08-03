ALTER TABLE `account` CHANGE `lastUpdate` `lastUpdate` INT(10) UNSIGNED NULL DEFAULT NULL;

ALTER TABLE `token` CHANGE `creation` `creation` INT(10) UNSIGNED NOT NULL, CHANGE `expire` `expire` INT(10) UNSIGNED NOT NULL; 

ALTER TABLE `transaction` CHANGE `id` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `transaction` CHANGE `datePosted` `datePosted` INT(10) UNSIGNED NULL DEFAULT NULL, CHANGE `dateUser` `dateUser` INT(10) UNSIGNED NULL DEFAULT NULL; 

ALTER TABLE `user` CHANGE `lastLoginAttemptFailed` `lastLoginAttemptFailed` INT(10) UNSIGNED NULL DEFAULT NULL; 

ALTER TABLE `transaction` ADD `insertedTimestamp` INT(10) UNSIGNED NOT NULL AFTER `isRecurring`;

UPDATE `transaction` SET `insertedTimestamp` = `datePosted` WHERE `insertedTimestamp` = '';

ALTER TABLE `account_icon` DROP FOREIGN KEY `fk_account id_icon`;
ALTER TABLE `transaction` DROP FOREIGN KEY `account id`;
ALTER TABLE `account` CHANGE `id` `id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `transaction` CHANGE `aid` `aid` SMALLINT UNSIGNED NOT NULL;
ALTER TABLE `account_icon` CHANGE `aid` `aid` SMALLINT UNSIGNED NOT NULL;
ALTER TABLE `account_icon`
  ADD CONSTRAINT `fk_account id_icon` FOREIGN KEY (`aid`) REFERENCES `account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `transaction`
  ADD CONSTRAINT `account id` FOREIGN KEY (`aid`) REFERENCES `account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
  
UPDATE `version` SET `version` = '0.10.0';
