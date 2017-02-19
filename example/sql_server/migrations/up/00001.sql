
-- --------------------------------------------------------
-- This is the script for migrate up
-- from version '0' to version '1'
-- --------------------------------------------------------


ALTER TABLE users ADD createdate_new DATE NULL;

update users set createdate_new = concat(substring(createdate, 1, 4), '-', substring(createdate, 5, 2), '-', substring(createdate, 7, 2));

ALTER TABLE users DROP COLUMN createdate;

EXEC SP_RENAME 'users.createdate_new' , 'createdate', 'COLUMN';


