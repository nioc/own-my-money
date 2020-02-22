CREATE TABLE `transaction_user_dispatch` (
  `transaction` int(11) UNSIGNED NOT NULL,
  `user` smallint(3) UNSIGNED NOT NULL,
  `share` tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

ALTER TABLE `transaction_user_dispatch`
  ADD UNIQUE KEY `transaction` (`transaction`,`user`),
  ADD KEY `fk_ratio_user_id` (`user`);

ALTER TABLE `transaction_user_dispatch`
  ADD CONSTRAINT `fk_ratio_transaction_id` FOREIGN KEY (`transaction`) REFERENCES `transaction` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ratio_user_id` FOREIGN KEY (`user`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

UPDATE `version` SET `version` = '0.11.0';
