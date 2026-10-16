
-- --------------------------------------------------------
-- This is the script for migrate up
-- from version '0' to version '1'
-- --------------------------------------------------------


ALTER TABLE users
ADD COLUMN createdate_new DATE NULL;

update users
set createdate_new = TO_DATE(createdate, 'YYYYMMDD');

ALTER TABLE users
  DROP COLUMN createdate;

ALTER TABLE users
  RENAME COLUMN createdate_new TO  createdate;

