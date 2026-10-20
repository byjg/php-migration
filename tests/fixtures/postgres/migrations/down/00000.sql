
-- --------------------------------------------------------
-- This is the script for migrate DOWN
-- from version '1' to version '0'
--
-- This is the reverse operation of the script up/00001
-- --------------------------------------------------------

ALTER TABLE users
ADD COLUMN createdate_old VARCHAR(8) NULL;

update users
  set createdate_old = TO_CHAR(createdate,'YYYYMMDD');

ALTER TABLE users
  DROP COLUMN createdate;

ALTER TABLE users
  RENAME COLUMN createdate_old TO createdate;

