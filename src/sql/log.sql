CREATE TABLE `DB_PREFIX_mobbex_log` (
  `log_id` INT(11) NOT NULL PRIMARY KEY,
  `type` TEXT NOT NULL,
  `message` TEXT NOT NULL,
  `data` TEXT NOT NULL,
  `creation_date` DATETIME
);