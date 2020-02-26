CREATE TABLE `account_holder` (
  `account` smallint(5) UNSIGNED NOT NULL,
  `user` smallint(3) UNSIGNED NOT NULL,
  `isReadOnly` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `pattern_user_dispatch` (
  `user` smallint(3) UNSIGNED NOT NULL,
  `id` mediumint(8) UNSIGNED NOT NULL,
  `userShare` smallint(3) UNSIGNED NOT NULL,
  `share` tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `transaction_user_dispatch` (
  `transaction` int(11) UNSIGNED NOT NULL,
  `user` smallint(3) UNSIGNED NOT NULL,
  `share` tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

ALTER TABLE `account_holder`
  ADD PRIMARY KEY (`account`,`user`),
  ADD KEY `account_holder_user` (`user`);

ALTER TABLE `pattern_user_dispatch`
  ADD PRIMARY KEY (`user`,`id`,`userShare`),
  ADD KEY `id` (`id`),
  ADD KEY `fk_dispatch_pattern_share_userid` (`userShare`);

ALTER TABLE `transaction_user_dispatch`
  ADD UNIQUE KEY `transaction` (`transaction`,`user`),
  ADD KEY `fk_ratio_user_id` (`user`);

ALTER TABLE `account_holder`
  ADD CONSTRAINT `account_holder_aid` FOREIGN KEY (`account`) REFERENCES `account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_holder_user` FOREIGN KEY (`user`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pattern_user_dispatch`
  ADD CONSTRAINT `fk_dispatch_pattern_id` FOREIGN KEY (`id`) REFERENCES `pattern` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dispatch_pattern_share_userid` FOREIGN KEY (`userShare`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dispatch_pattern_userid` FOREIGN KEY (`user`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `transaction_user_dispatch`
  ADD CONSTRAINT `fk_ratio_transaction_id` FOREIGN KEY (`transaction`) REFERENCES `transaction` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ratio_user_id` FOREIGN KEY (`user`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

REPLACE INTO `account_holder`(`account`, `user`, `isReadOnly`) SELECT `id`, `user`, 0 FROM `account`;

UPDATE `version` SET `version` = '0.11.0';
