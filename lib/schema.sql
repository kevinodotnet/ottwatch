
-- Setup your database with something like this:
-- 
-- mysqladmin create ottwatch
-- echo " grant all on ottwatch.* to 'ottwatch'@'localhost' identified by '0ttwatchme'; " | mysql 
-- echo " flush privileges; " | mysql
-- mysql ottwatch < schema.sql
-- 
-- Then edit config-sample.php with appropriate values, and save it as config.php

drop table if exists variable;
create table variable (
  name varchar(64) not null,
  value longtext,
  primary key (name)
) engine = innodb;

-- Combined "registered users" and "natural persons" table.
drop table if exists people;
create table people (
  id mediumint not null auto_increment,
  name varchar(100),
  email varchar(100),
  password varchar(32),
  created datetime default CURRENT_TIMESTAMP,
  primary key (id)
) engine = innodb;

drop table if exists places;
create table places (
  id mediumint not null auto_increment,
  /* if a specific POINT, or other geometry is availalbe, add it here */
  shape geometry, 
  /* when the place refers to a street address, link to the "road" via roads table, save specific numeral address here */
  rd_num mediumint not null,
  roadid int(11), 
  /* if place is associated with a person, link here */
  personid mediumint,
  /* if place is associated with a meeting.ITEM, link here */
  itemid mediumint(9),
  primary key (id),
  constraint foreign key (personid) references people (id) on delete cascade on update cascade,
  constraint foreign key (roadid) references roadways (OGR_FID) on delete cascade on update cascade
  constraint foreign key (itemid) references item (id) on delete cascade on update cascade
) engine = innodb;
create unique index places_in1 on places (roadid,rd_num,personid);

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
  electedofficialid mediumint,
  lobbydate datetime,
  activity varchar(100),
  lobbied varchar(200),
  created datetime,
  primary key (id),
  constraint foreign key (lobbyfileid) references lobbyfile (id) on delete cascade on update cascade
  constraint foreign key (electedofficialid) references electedofficials (id) on delete cascade on update cascade
) engine = innodb;

-- enforce unique on the lobbying table; used by INSERTER to avoid dups
create unique index lobbying_in1 on lobbying (lobbyfileid,lobbydate,activity,lobbied);

drop table if exists devappfile;
drop table if exists devappstatus;
drop table if exists devapp;
create table devapp (
  id mediumint not null auto_increment,
  appid varchar(10),
  devid varchar(20),
  ward varchar(100),
  address varchar(2048), -- big because JSON encoded array of multiple addresses with lat/lon too
  apptype varchar(100),
  description varchar(2048),
  receiveddate datetime,
  created datetime,
  updated datetime,
  primary key (id)
) engine = innodb;
create table devappstatus (
  id mediumint not null auto_increment,
  devappid mediumint not null,
  statusdate datetime,
  status varchar(100),
  created datetime default CURRENT_TIMESTAMP,
  primary key (id),
  constraint foreign key (devappid) references devapp (id) on delete cascade on update cascade
) engine = innodb;
create unique index devappstatus_in1 on devappstatus (devappid,statusdate,status);
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
	minutes boolean default false,
	youtube varchar(100),
  youtubeset datetime,
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

-- voting on items, one item may have many related motions
create table itemvote (
  id mediumint not null auto_increment,
  itemid mediumint not null,
  motion varchar(1024), -- text of the motion
  primary key (id),
  constraint foreign key (itemid) references item (id) on delete cascade on update cascade
) engine = innodb;

-- voting on items, one item may have many related motions
create table itemvotecast (
  id mediumint not null auto_increment,
  itemvoteid mediumint not null,
  vote varchar(1), -- Y for yes, N for no, A for absent
  name varchar(200), -- name of person who voted
  primary key (id),
  constraint foreign key (itemvoteid) references itemvote (id) on delete cascade on update cascade
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
insert into category values ('ESAC','Environmental Stewardship Advisory Committee');
insert into category values ('BHSC','Built Heritage Sub-Committee');
insert into category values ('CSAC','Community Services Advisory Committee');
insert into category values ('ACHRAC','Arts, Culture, Heritage and Recreation Advisory Committee');
insert into category values ('AAC','Accessibility Advisory Committee');

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

drop table permit;
create table permit (
  id mediumint not null auto_increment,
  st_num varchar(20),
  st_name varchar(100),
  postal varchar(7),
  ward tinyint,
  plan_num varchar(30),
  lot_num varchar(30),
  contractor varchar(200),
  building_type varchar(200),
  description varchar(1024),
  du mediumint,
  value int,
  area mediumint,
  permit_number mediumint,
  app_type varchar(100),
  issued_date date,
  created datetime default CURRENT_TIMESTAMP,
  primary key (id)
) engine = innodb;

create unique index permit_in1 on permit (permit_number,st_num,st_name,contractor);

