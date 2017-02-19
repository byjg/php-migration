
-- --------------------------------------------------------
-- This is the script for migrate up
-- from version '1' to version '2'
-- --------------------------------------------------------

create table roles
(
  id SERIAL NOT NULL PRIMARY KEY,
  rolename char(1) NOT NULL,
  userid int not null
)
