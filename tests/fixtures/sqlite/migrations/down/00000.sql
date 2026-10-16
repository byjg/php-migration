
-- --------------------------------------------------------
-- This is the script for migrate DOWN
-- from version '1' to version '0'
--
-- This is the reverse operation of the script up/00001
-- --------------------------------------------------------

CREATE table users_backup (
  id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT ,
  name varchar(50) NOT NULL,
  createdate varchar(8) not NULL
);

INSERT INTO users_backup
SELECT id, name, strftime('%Y%m%d', createdate)
FROM users;

DROP TABLE users;

ALTER TABLE users_backup RENAME TO users;

