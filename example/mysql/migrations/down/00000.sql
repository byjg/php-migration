
-- --------------------------------------------------------
-- This is the script for migrate DOWN
-- from version '1' to version '0'
--
-- This is the reverse operation of the script up/00001
-- --------------------------------------------------------

ALTER TABLE `users`
ADD COLUMN `createdate_old` VARCHAR(8) NULL AFTER `createdate`;

update users
set createdate_old = DATE_FORMAT(createdate,'%Y%m%d');

ALTER TABLE `users`
  DROP COLUMN `createdate`;

ALTER TABLE `users`
  CHANGE COLUMN `createdate_old` `createdate` VARCHAR(8) NOT NULL ;

