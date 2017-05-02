
-- --------------------------------------------------------
-- THIS IS THE BASE FILE . The version '0'
-- --------------------------------------------------------

-- Create the demo table USERS and populate it

create table users (

  ID int IDENTITY(1,1) PRIMARY KEY,
  name varchar(50) NOT NULL,
  createdate VARCHAR(8)

);

insert into users (name, createdate) values ('John Doe', '20160110');
insert into users (name, createdate) values ('Jane Doe', '20151230');

