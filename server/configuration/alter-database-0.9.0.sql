ALTER TABLE `transaction` ADD `isRecurring` BOOLEAN NOT NULL DEFAULT FALSE AFTER `note`;

ALTER TABLE `pattern` ADD `isRecurring` BOOLEAN NOT NULL DEFAULT FALSE AFTER `subcategory`; 

UPDATE `version` SET `version` = '0.9.0';
