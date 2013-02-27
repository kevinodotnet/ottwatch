
-- Setup your database with something like this:
-- 
-- mysqladmin create ottwatch
-- echo " grant all on ottwatch.* to 'ottwatch'@'localhost' identified by '0ttwatchme'; " | mysql 
-- echo " flush privileges; " | mysql
-- mysql ottwatch < schema.sql
-- 
-- Then edit config-sample.php with appropriate values, and save it as config.php

-- Combined "registered users" and "natural persons" table.
create table people (
  id mediumint not null auto_increment,
  name varchar(100),
  email varchar(100),
  primary key (id)
) engine = innodb;

