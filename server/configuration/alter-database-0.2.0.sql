DROP TABLE IF EXISTS `pattern`;
CREATE TABLE `pattern` (
  `user` smallint(3) UNSIGNED NOT NULL,
  `id` mediumint(8) UNSIGNED NOT NULL,
  `label` varchar(200) NOT NULL,
  `category` smallint(3) UNSIGNED DEFAULT NULL,
  `subcategory` smallint(3) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `version`;
CREATE TABLE `version` (
  `version` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `pattern`
  ADD PRIMARY KEY (`user`,`id`),
  ADD KEY `id` (`id`),
  ADD KEY `fk_pattern_category_1` (`category`),
  ADD KEY `fk_pattern_subcategory_1` (`subcategory`);

ALTER TABLE `version`
  ADD PRIMARY KEY (`version`);

ALTER TABLE `pattern`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `pattern`
  ADD CONSTRAINT `fk_pattern_category_1` FOREIGN KEY (`category`) REFERENCES `category` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pattern_subcategory_1` FOREIGN KEY (`subcategory`) REFERENCES `category` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pattern_user_1` FOREIGN KEY (`user`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

INSERT INTO `version` (`version`) VALUES ('0.2.0');
