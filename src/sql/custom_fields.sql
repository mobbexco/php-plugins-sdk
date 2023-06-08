CREATE TABLE IF NOT EXISTS `DB_PREFIX_mobbex_custom_fields` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `row_id` INT(11) NOT NULL,
    `object` TEXT NOT NULL,
    `field_name` TEXT NOT NULL,
    `data` TEXT NOT NULL
);