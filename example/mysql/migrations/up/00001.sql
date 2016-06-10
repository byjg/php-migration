
-- --------------------------------------------------------
-- This is the script for migrate up
-- from version '0' to version '1'
-- --------------------------------------------------------


ALTER TABLE `users`
ADD COLUMN `createdate_new` DATE NULL AFTER `createdate`;

update users
set createdate_new = concat(substr(createdate, 1, 4), substr(createdate, 5, 2), substr(createdate, 7, 2));

ALTER TABLE `users`
  DROP COLUMN `createdate`;

ALTER TABLE `users`
  CHANGE COLUMN `createdate_new` `createdate` DATE NOT NULL ;

