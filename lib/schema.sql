
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

drop table if exists lobbying;
drop table if exists lobbyfile;
create table lobbyfile (
  id mediumint not null auto_increment,
  lobbyist varchar(100),
  client varchar(100),
  issue varchar(200),
  primary key (id)
) engine = innodb;

create table lobbying (
  id mediumint not null auto_increment,
  lobbyfileid mediumint not null,
  lobbydate datetime,
  activity varchar(100),
  lobbied varchar(200),
  created datetime,
  primary key (id),
  constraint foreign key (lobbyfileid) references lobbyfile (id) on delete cascade on update cascade
) engine = innodb;

-- enforce unique on the lobbying table; used by INSERTER to avoid dups
create unique index lobbying_in1 on lobbying (lobbyfileid,lobbydate,activity,lobbied);

drop table if exists devappfile;
drop table if exists devapp;
create table devapp (
  id mediumint not null auto_increment,
  appid varchar(10),
  devid varchar(20),
  ward varchar(100),
  address varchar(2048), -- big because JSON encoded array of multiple addresses with lat/lon too
  apptype varchar(100),
  status varchar(100),
  description varchar(2048),
  statusdate datetime,
  receiveddate datetime,
  created datetime,
  updated datetime,
  primary key (id)
) engine = innodb;
create table devappfile (
  id mediumint not null auto_increment,
  devappid mediumint not null,
  href varchar(300),
  title varchar(300),
  created datetime,
  updated datetime,
  primary key (id),
  constraint foreign key (devappid) references devapp (id) on delete cascade on update cascade
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
  contactName varchar(100),
  contactEmail varchar(100),
  contactPhone varchar(30),
  members varchar(300),
  created datetime,
  updated datetime,
  primary key (id)
) engine = innodb;

create table item (
  id mediumint not null auto_increment,
  meetingid mediumint not null,
  itemid mediumint,
  title varchar(300),
  created datetime,
  updated datetime,
  primary key (id),
  constraint foreign key (meetingid) references meeting (id) on delete cascade on update cascade
) engine = innodb;

-- "ifile" means "item file" but 'file' is a keyword
create table ifile (
  id mediumint not null auto_increment,
  itemid mediumint not null,
  fileid mediumint,
  title varchar(300),
  md5 varchar(50),
  txt mediumtext, -- after pdftotext
  created datetime,
  updated datetime,
  primary key (id),
  constraint foreign key (itemid) references item (id) on delete cascade on update cascade
) engine = innodb;

create table ifileword (
  id mediumint not null auto_increment,
  fileid mediumint not null,
  word varchar(50),
  line int,
  offset int,
  docoffset int,
  primary key (id),
  constraint foreign key (fileid) references ifile (id) on delete cascade on update cascade
) engine = innodb;

create table category (
  category varchar(100) not null,
  title varchar(100),
  primary key (category)
) engine = innodb;

insert into category values ('ARAC','Agriculture and Rural Affairs Committee');
insert into category values ('ASC','Audit Sub-Committee');
insert into category values ('City Council','City Council');
insert into category values ('CPSC','Community and Protective Services Committee');
insert into category values ('CUR','COURT OF REVISION');
insert into category values ('DC','Debenture Committee');
insert into category values ('EC','Environment Committee');
insert into category values ('FEDC','Finance and Economic Development Committee');
insert into category values ('ITSC','Information Technology Sub-Committee');
insert into category values ('OBHAC','Ottawa Built Heritage Advisory Committee');
insert into category values ('OTC','Transit Commission');
insert into category values ('PLC','Planning Committee');
insert into category values ('TRC','Transportation Committee');

create table electedofficials (
  id mediumint not null auto_increment,
  ward varchar(100),
  wardnum varchar(5),
  office varchar(25),
  first varchar(50),
  last varchar(50),
  email varchar(100),
  url varchar(300),
  photourl varchar(300),
  phone varchar(12),
  primary key (id)
) engine = innodb;

