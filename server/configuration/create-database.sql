DROP TABLE IF EXISTS `account`;
CREATE TABLE `account` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `user` smallint(3) UNSIGNED NOT NULL,
  `bankId` varchar(10) DEFAULT NULL,
  `branchId` varchar(22) DEFAULT NULL,
  `accountId` varchar(22) DEFAULT NULL,
  `label` varchar(50) DEFAULT NULL,
  `duration` varchar(10) NOT NULL DEFAULT 'P3M',
  `hasIcon` tinyint(1) NOT NULL DEFAULT '0',
  `balance` decimal(8,2) DEFAULT NULL,
  `lastUpdate` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `account_icon`;
CREATE TABLE `account_icon` (
  `aid` smallint(5) UNSIGNED NOT NULL,
  `mime_type` tinyint(3) UNSIGNED NOT NULL,
  `icon` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id` smallint(3) UNSIGNED NOT NULL,
  `label` varchar(45) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  `icon` text,
  `isBudgeted` tinyint(1) NOT NULL DEFAULT '1',
  `parentId` smallint(3) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `map`;
CREATE TABLE `map` (
  `code` varchar(10) NOT NULL,
  `label` varchar(200) NOT NULL,
  `dateFormat` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `map_attribute`;
CREATE TABLE `map_attribute` (
  `code` varchar(10) NOT NULL,
  `target` varchar(50) NOT NULL,
  `origin` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pattern`;
CREATE TABLE `pattern` (
  `user` smallint(3) UNSIGNED NOT NULL,
  `id` mediumint(8) UNSIGNED NOT NULL,
  `label` varchar(200) NOT NULL,
  `category` smallint(3) UNSIGNED DEFAULT NULL,
  `subcategory` smallint(3) UNSIGNED DEFAULT NULL,
  `isRecurring` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `token`;
CREATE TABLE `token` (
  `user` smallint(3) UNSIGNED NOT NULL,
  `creation` int(10) UNSIGNED NOT NULL,
  `ip` varchar(45) NOT NULL,
  `userAgent` text NOT NULL,
  `expire` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `transaction`;
CREATE TABLE `transaction` (
  `id` int(11) UNSIGNED NOT NULL,
  `aid` smallint(5) UNSIGNED NOT NULL,
  `fitid` varchar(255) DEFAULT NULL,
  `type` enum('DEBIT','CREDIT') DEFAULT NULL,
  `datePosted` int(10) UNSIGNED DEFAULT NULL,
  `dateUser` int(10) UNSIGNED DEFAULT NULL,
  `amount` float(8,2) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `memo` varchar(255) DEFAULT NULL,
  `category` smallint(3) UNSIGNED DEFAULT NULL,
  `subcategory` smallint(3) UNSIGNED DEFAULT NULL,
  `note` varchar(50) DEFAULT NULL,
  `isRecurring` tinyint(1) NOT NULL DEFAULT '0',
  `insertedTimestamp` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` smallint(3) UNSIGNED NOT NULL,
  `login` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `scope` varchar(50) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `mail` varchar(50) DEFAULT NULL,
  `language` varchar(7) NOT NULL,
  `loginAttemptFailed` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `lastLoginAttemptFailed` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `version`;
CREATE TABLE `version` (
  `version` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `account`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_id` (`user`);

ALTER TABLE `account_icon`
  ADD PRIMARY KEY (`aid`);

ALTER TABLE `category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_parent_idx` (`parentId`);

ALTER TABLE `map`
  ADD PRIMARY KEY (`code`);

ALTER TABLE `map_attribute`
  ADD PRIMARY KEY (`code`,`target`);

ALTER TABLE `pattern`
  ADD PRIMARY KEY (`user`,`id`),
  ADD KEY `id` (`id`),
  ADD KEY `fk_pattern_category_1` (`category`),
  ADD KEY `fk_pattern_subcategory_1` (`subcategory`);

ALTER TABLE `token`
  ADD KEY `fk_token_user_1` (`user`);

ALTER TABLE `transaction`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_fitid` (`aid`,`fitid`),
  ADD KEY `fk_account` (`aid`),
  ADD KEY `fk_category` (`category`),
  ADD KEY `fk_subcategory` (`subcategory`);

ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`);

ALTER TABLE `version`
  ADD PRIMARY KEY (`version`);


ALTER TABLE `account`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `category`
  MODIFY `id` smallint(3) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `pattern`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `transaction`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `user`
  MODIFY `id` smallint(3) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `account`
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `account_icon`
  ADD CONSTRAINT `fk_account id_icon` FOREIGN KEY (`aid`) REFERENCES `account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `category`
  ADD CONSTRAINT `fk_parent` FOREIGN KEY (`parentId`) REFERENCES `category` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `map_attribute`
  ADD CONSTRAINT `code` FOREIGN KEY (`code`) REFERENCES `map` (`code`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pattern`
  ADD CONSTRAINT `fk_pattern_category_1` FOREIGN KEY (`category`) REFERENCES `category` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pattern_subcategory_1` FOREIGN KEY (`subcategory`) REFERENCES `category` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pattern_user_1` FOREIGN KEY (`user`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `token`
  ADD CONSTRAINT `fk_token_user_1` FOREIGN KEY (`user`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `transaction`
  ADD CONSTRAINT `account id` FOREIGN KEY (`aid`) REFERENCES `account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `category` FOREIGN KEY (`category`) REFERENCES `category` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `subcategory` FOREIGN KEY (`subcategory`) REFERENCES `category` (`id`) ON UPDATE CASCADE;

INSERT INTO `version` (`version`) VALUES ('0.10.0');
