ALTER TABLE `user` ADD `language` VARCHAR(7) NOT NULL AFTER `mail`;
ALTER TABLE `account` ADD `duration` VARCHAR(10) NOT NULL DEFAULT 'P3M' AFTER `label`;

UPDATE `version` SET `version` = '0.7.0';
