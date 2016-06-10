
-- --------------------------------------------------------
-- This is the script for migrate up
-- from version '0' to version '1'
-- --------------------------------------------------------


-- Create a temp table;
CREATE table users_backup (
  id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT ,
  name varchar(50) NOT NULL,
  createdate date not NULL
);

INSERT INTO users_backup
SELECT id, name, substr(createdate, 1, 4) || '-' || substr(createdate, 5, 2) || '-' || substr(createdate, 7, 2)
FROM users;

DROP TABLE users;

ALTER TABLE users_backup RENAME TO users;

