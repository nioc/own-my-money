ALTER TABLE `user` ADD `language` VARCHAR(7) NOT NULL AFTER `mail`;

UPDATE `version` SET `version` = '0.7.0';
