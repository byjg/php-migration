
-- --------------------------------------------------------
-- This is the script for migrate up
-- from version '1' to version '2'
-- --------------------------------------------------------

create table roles
(
  id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  rolename char(1) NOT NULL,
  userid int not null
)
