ALTER TABLE `{{public_shell_prefix}}_sessions`
  MODIFY `sesskey` VARCHAR(256) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  MODIFY `value` MEDIUMTEXT NOT NULL;
