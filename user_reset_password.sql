ALTER TABLE `users`
    ADD `password_reset_token` CHAR(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
    ADD `last_password_reset` INT(10) UNSIGNED NULL;
