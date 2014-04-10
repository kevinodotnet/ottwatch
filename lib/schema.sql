-- Setup your database with something like this:
-- 
-- mysqladmin create ottwatch
-- echo " grant all on ottwatch.* to 'ottwatch'@'localhost' identified by 'CHANGEME'; " | mysql 
-- echo " flush privileges; " | mysql
-- mysql ottwatch < schema.sql
-- 
-- Then edit config-sample.php with appropriate values, and save it as config.php

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;


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
  twitter varchar(32),
  facebookid bigint unsigned,
  /* This requires mysql 5.6 or greater since setting a non-constant timestamp on datetime isn't supported lower */
  created datetime default CURRENT_TIMESTAMP,
  lastlogin datetime,
	emailverified boolean default false,
	admin boolean default false,
	author boolean default false,
  primary key (id)
) engine = innodb;
create unique index people_in1 on people (email);

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
  itemid mediumint(11),
  primary key (id),
  constraint `places_ibfk_1` foreign key (personid) references people (id) on delete cascade on update cascade,
  constraint `places_ibfk_2` foreign key (roadid) references roadways (OGR_FID) on delete cascade on update cascade,
  constraint `places_ibfk_3` foreign key (`itemid`) references `item` (`id`) on delete cascade on update cascade
) engine = innodb;
create unique index places_in1 on places (roadid,rd_num,personid);

DROP TABLE IF EXISTS `roadways`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roadways` (
  `OGR_FID` int(11) NOT NULL AUTO_INCREMENT,
  `SHAPE` geometry NOT NULL,
  `rd_name` varchar(25) DEFAULT NULL,
  `rd_suffix` varchar(5) DEFAULT NULL,
  `rd_directi` varchar(2) DEFAULT NULL,
  `left_from` varchar(5) DEFAULT NULL,
  `left_to` varchar(5) DEFAULT NULL,
  `right_from` varchar(5) DEFAULT NULL,
  `right_to` varchar(5) DEFAULT NULL,
  UNIQUE KEY `OGR_FID` (`OGR_FID`)
) ENGINE=InnoDB AUTO_INCREMENT=24726 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


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
  lobbiednorm varchar(200),
  created datetime,
  primary key (id),
  constraint foreign key (lobbyfileid) references lobbyfile (id) on delete cascade on update cascade,
  constraint foreign key (electedofficialid) references electedofficials (id) on delete cascade on update cascade
) engine = innodb;

-- lobbying must be declared within 15 business days. But with weekends and worst case, that means
-- most things under 23 are legit. So only put 24 days late and up in the report
create or replace view latelobbying as
  select 
	  id,
	  lobbydate,
	  created,
	  datediff(created,lobbydate) diff
  from lobbying 
  where 
    datediff(created,lobbydate) >= 24;

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
  youtubestate varchar(20),
  youtubestart smallint unsigned,
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
drop table if exists itemvote;
create table itemvote (
  id mediumint not null auto_increment,
  itemid mediumint not null,
  motion varchar(1024), -- text of the motion
  primary key (id),
  constraint foreign key (itemid) references item (id) on delete cascade on update cascade
) engine = innodb;

-- voting on items, one item may have many related motions
drop table if exists itemvotecast;
create table itemvotecast (
  id mediumint not null auto_increment,
  itemvoteid mediumint not null,
  vote varchar(1), -- Y for yes, N for no, A for absent
  name varchar(200), -- name of person who voted
  primary key (id),
  constraint foreign key (itemvoteid) references itemvote (id) on delete cascade on update cascade
) engine = innodb;

-- vote passed and count of Y/N votes
create view itemvotetab as 
select 
  itemvoteid,
  case when sum(case when vote = 'y' then 1 else 0 end) > sum(case when vote = 'n' then 1 else 0 end) then 1 else 0 end passed,
  sum(case when vote = 'y' then 1 else 0 end) y,
  sum(case when vote = 'n' then 1 else 0 end) n 
from itemvotecast 
group by itemvoteid;

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

drop table if exists ifileword;
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

drop table if exists category;
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
insert into category values ('COR','Committee of Revision');

drop table if exists electedofficials;
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

drop table if exists permit;
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

drop table if exists consultation;
create table consultation (
  id mediumint not null auto_increment,
  category varchar(300),
  title varchar(300),
  url varchar(300),
  md5 varchar(50),
  created datetime default CURRENT_TIMESTAMP,
  updated datetime default CURRENT_TIMESTAMP,
  primary key (id)
) engine = innodb;

drop table if exists consultationdoc;
create table consultationdoc (
  id mediumint not null auto_increment,
  consultationid mediumint not null,
  title varchar(300),
  url varchar(300),
  md5 varchar(50),
  created datetime default CURRENT_TIMESTAMP,
  updated datetime default CURRENT_TIMESTAMP,
  primary key (id),
  constraint foreign key (consultationid) references consultation (id) on delete cascade on update cascade
) engine = innodb;

drop table if exists mfippa;
create table mfippa (
  id mediumint not null auto_increment,
  tag varchar(12), -- A-2013-000001
  closed datetime,
  source varchar(12), -- the ottwatch mfippa req this mfippa was found in
  page smallint unsigned not null,
  x smallint unsigned not null,
  y smallint unsigned not null,
  summary varchar(2000),
  published boolean default false,
  created datetime default CURRENT_TIMESTAMP,
  primary key (id)
) engine = innodb;
create unique index mfippa_in1 on mfippa (tag);

drop table if exists rssitem;
create table rssitem (
  id mediumint not null auto_increment,
  title varchar(300),
  link varchar(300),
  guid varchar(300),
  created datetime default CURRENT_TIMESTAMP,
  primary key (id)
) engine = innodb;
create unique index rssitem_in1 on rssitem (guid);

drop table if exists candidate;
create table candidate (
  id mediumint not null auto_increment,
  year smallint,
  ward tinyint, -- 0 for mayor
  first varchar(50),
  middle varchar(50),
  last varchar(50),
  url varchar(300),
  email varchar(50),
  twitter varchar(50),
  facebook varchar(100),
  nominated datetime,
	incumbent boolean default false,
  personid mediumint,
  primary key (id),
  constraint foreign key (personid) references people (id)
) engine = innodb;

-- note, a candidate can have multiple returns (main, plus supplementary) or no return (left join only)
drop table if exists candidate_return;
create table candidate_return (
  id mediumint not null auto_increment,
  candidateid mediumint not null,
	filename varchar(50), -- ie: con057869.pdf from ottawa.ca
  primary key (id),
  constraint foreign key (candidateid) references candidate (id) on delete cascade on update cascade
) engine = innodb;

drop table if exists candidate_donation;
create table candidate_donation (
  id mediumint not null auto_increment,
  returnid mediumint not null,
	`type` tinyint, -- 0=individual, 1=corp_or_union
	name varchar(100), 
	address varchar(100),
	city varchar(100),
	prov varchar(100),
	postal varchar(15),
	amount decimal(10,2),
  page smallint unsigned, -- page in candidate_return.filename
  x smallint unsigned, -- top-left corner on page
  y smallint unsigned,
  primary key (id),
  constraint foreign key (returnid) references candidate_return (id) on delete cascade on update cascade
) engine = innodb;

drop table if exists feed;
create table feed (
  id mediumint not null auto_increment,
  message varchar(300), -- short message; ie: tweet text excluding URL
  path varchar(300), -- local '/' path reference to ottwatch.ca. ie: /meeting/City Council/xxxx
  url varchar(300), -- full url to ottwatch.ca resource, likely as bitly, so that it doesnt need to be computed
  created datetime default CURRENT_TIMESTAMP,
  primary key (id)
) engine = innodb;

drop table if exists opendata;
create table opendata (
  id mediumint not null auto_increment,
  guid varchar(100) not null,
  name varchar(300),
  title varchar(300),
  url varchar(300),
  created datetime default CURRENT_TIMESTAMP,
  updated datetime default CURRENT_TIMESTAMP, -- 'metadata-modified in json'
  primary key (id)
) engine = innodb;

drop table if exists opendatafile;
create table opendatafile (
  id mediumint not null auto_increment,
  dataid mediumint not null,
  guid varchar(100) not null,
  `size` int unsigned,
  description varchar(1024),
  format varchar(10),
  name varchar(300),
  url varchar(300),
  hash varchar(100),
  created datetime default CURRENT_TIMESTAMP,
  updated datetime default CURRENT_TIMESTAMP, -- 'last_modified in json'
  primary key (id),
  constraint foreign key (dataid) references opendata (id) on delete cascade on update cascade
) engine = innodb;

drop table if exists story;
create table story (
  id mediumint not null auto_increment,
  personid mediumint not null,
  title varchar(300),
  body text,
  created datetime default CURRENT_TIMESTAMP,
  updated datetime,
	published boolean default false,
	deleted boolean default false,
  primary key (id),
  constraint foreign key (personid) references people (id)
) engine = innodb;

drop table if exists answer;
drop table if exists election_question;
drop table if exists question_vote;
drop table if exists question;

create table question (
  id mediumint not null auto_increment,
  title varchar(100) not null,
  body varchar(500),
  created datetime default CURRENT_TIMESTAMP,
  updated datetime default CURRENT_TIMESTAMP,
	published boolean default false,
  personid mediumint not null,
  primary key (id),
  constraint foreign key (personid) references people (id) on delete cascade on update cascade
) engine = innodb;

create table question_vote (
  id mediumint not null auto_increment,
  questionid mediumint not null,
  personid mediumint not null,
	vote tinyint not null,
  created datetime default CURRENT_TIMESTAMP,
  primary key (id),
  constraint foreign key (questionid) references question (id) on delete cascade on update cascade,
  constraint foreign key (personid) references people (id) on delete cascade on update cascade,
	unique index question_vote_in1 (questionid,personid)
) engine = innodb;

create table answer (
  id mediumint not null auto_increment,
  questionid mediumint not null,
  personid mediumint not null,
  body varchar(500),
  created datetime default CURRENT_TIMESTAMP,
  updated datetime default CURRENT_TIMESTAMP,
  primary key (id),
  constraint foreign key (questionid) references question (id) on delete cascade on update cascade
) engine = innodb;

create table election_question (
  id mediumint not null auto_increment,
  questionid mediumint not null,
  ward tinyint, -- -1 for city wide, 0 for mayor, elese ward number
  primary key (id),
  constraint foreign key (questionid) references question (id) on delete cascade on update cascade
) engine = innodb;


