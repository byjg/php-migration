
-- --------------------------------------------------------
-- This is the script for migrate up
-- from version '1' to version '2'
-- --------------------------------------------------------

create table roles
(
  ID int IDENTITY(1,1) PRIMARY KEY,
  rolename char(1) NOT NULL,
  userid int not null
)
