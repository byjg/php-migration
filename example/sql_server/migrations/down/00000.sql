
-- --------------------------------------------------------
-- This is the script for migrate DOWN
-- from version '1' to version '0'
--
-- This is the reverse operation of the script up/00001
-- --------------------------------------------------------

ALTER TABLE users
ADD COLUMN createdate_old VARCHAR(8) NULL ;

update users
set createdate_old = concat(DATEPART(yyyy,createdate),DATEPART(mm,createdate),DATEPART(dd,createdate));

ALTER TABLE users
  DROP COLUMN createdate;

EXEC sp_RENAME 'users.createdate_old' , 'createdate', 'COLUMN'

