
-- Setup your database with something like this:
-- 
-- mysqladmin create ottwatch
-- echo " grant all on ottwatch.* to 'ottwatch'@'localhost' identified by '0ttwatchme'; " | mysql 
-- echo " flush privileges; " | mysql
-- mysql ottwatch < schema.sql
-- 
-- Then edit config-sample.php with appropriate values, and save it as config.php

-- Combined "registered users" and "natural persons" table.
drop table if exists people;
create table people (
  id mediumint not null auto_increment,
  name varchar(100),
  email varchar(100),
  primary key (id)
) engine = innodb;

drop table if exists ifile;
drop table if exists item;
drop table if exists meeting;

create table meeting (
  id mediumint not null auto_increment,
  rssguid varchar(200),
  meetid mediumint,
  starttime datetime,
  title varchar(100),
  category varchar(100),
  created datetime default CURRENT_TIMESTAMP,
  updated datetime default CURRENT_TIMESTAMP,
  primary key (id)
) engine = innodb;

create table item (
  id mediumint not null auto_increment,
  meetingid mediumint not null,
  itemid mediumint,
  title varchar(300),
  created datetime default CURRENT_TIMESTAMP,
  updated datetime default CURRENT_TIMESTAMP,
  primary key (id),
  constraint foreign key (meetingid) references meeting (id) on delete cascade on update cascade
) engine = innodb;

-- "ifile" means "item file" but 'file' is a keyword
create table ifile (
  id mediumint not null auto_increment,
  itemid mediumint not null,
  fileid mediumint,
  created datetime default CURRENT_TIMESTAMP,
  updated datetime default CURRENT_TIMESTAMP,
  primary key (id),
  constraint foreign key (itemid) references item (id) on delete cascade on update cascade
) engine = innodb;

