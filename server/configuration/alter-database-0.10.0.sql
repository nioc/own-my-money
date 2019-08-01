ALTER TABLE `account` CHANGE `lastUpdate` `lastUpdate` INT(10) UNSIGNED NULL DEFAULT NULL;

ALTER TABLE `token` CHANGE `creation` `creation` INT(10) UNSIGNED NOT NULL, CHANGE `expire` `expire` INT(10) UNSIGNED NOT NULL; 

ALTER TABLE `transaction` CHANGE `id` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `transaction` CHANGE `datePosted` `datePosted` INT(10) UNSIGNED NULL DEFAULT NULL, CHANGE `dateUser` `dateUser` INT(10) UNSIGNED NULL DEFAULT NULL; 

ALTER TABLE `user` CHANGE `lastLoginAttemptFailed` `lastLoginAttemptFailed` INT(10) UNSIGNED NULL DEFAULT NULL; 

ALTER TABLE `transaction` ADD `insertedTimestamp` INT(10) UNSIGNED NOT NULL AFTER `isRecurring`;

UPDATE `transaction` SET `insertedTimestamp` = `datePosted` WHERE `insertedTimestamp` = '';

UPDATE `version` SET `version` = '0.10.0';
