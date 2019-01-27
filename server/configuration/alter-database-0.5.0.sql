ALTER TABLE `category` ADD `isBudgeted` BOOLEAN NOT NULL DEFAULT TRUE AFTER `icon`;

UPDATE `version` SET `version` = '0.5.0';
