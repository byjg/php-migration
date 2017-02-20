
-- --------------------------------------------------------
-- This is the script for migrate DOWN
-- from version '1' to version '0'
--
-- This is the reverse operation of the script up/00001
-- --------------------------------------------------------

ALTER TABLE users
ADD createdate_old VARCHAR(8) NULL ;

update users
set createdate_old = concat(DATEPART(yyyy,createdate),
  RIGHT('00' + CONVERT(NVARCHAR(2), DATEPART(MONTH, createdate)), 2),
  RIGHT('00' + CONVERT(NVARCHAR(2), DATEPART(DAY, createdate)), 2)
);

ALTER TABLE users
  DROP COLUMN createdate;

EXEC sp_RENAME 'users.createdate_old' , 'createdate', 'COLUMN'

