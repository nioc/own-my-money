ALTER TABLE `account` ADD `isActive` BOOLEAN NOT NULL DEFAULT TRUE AFTER `balance`;

UPDATE `version` SET `version` = '0.13.0';
