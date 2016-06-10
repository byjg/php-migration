
-- --------------------------------------------------------
-- This is the script for migrate up
-- from version '1' to version '2'
-- --------------------------------------------------------

create table roles
(
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  rolename char(1) NOT NULL,
  userid int not null
)
