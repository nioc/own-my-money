ALTER TABLE `user` ADD `language` VARCHAR(7) NOT NULL AFTER `mail`;

ALTER TABLE `account` ADD `duration` VARCHAR(10) NOT NULL DEFAULT 'P3M' AFTER `label`;

ALTER TABLE `account` ADD `hasIcon` BOOLEAN NOT NULL DEFAULT FALSE AFTER `duration`;

CREATE TABLE `account_icon` (
  `aid` int(11) NOT NULL,
  `mime_type` tinyint(3) UNSIGNED NOT NULL,
  `icon` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `account_icon`
  ADD PRIMARY KEY (`aid`);

ALTER TABLE `account_icon`
  ADD CONSTRAINT `fk_account id_icon` FOREIGN KEY (`aid`) REFERENCES `account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

UPDATE `version` SET `version` = '0.7.0';
