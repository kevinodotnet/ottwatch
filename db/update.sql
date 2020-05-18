DROP TABLE IF EXISTS `campaign`;
CREATE TABLE `campaign` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `title` varchar(128) DEFAULT NULL,
  `sub_title` varchar(128) DEFAULT NULL,
  `preamble` varchar(1024) DEFAULT NULL,
  `item_id` mediumint(9) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

insert into campaign (title, sub_title, item_id,preamble) values ('More space for walking','Ottawa Public Health: Apr20',2274515,
'
<p>This is just a preamble message that introduces the campaign. I could use help coming up with something here.</p>

<p>Nullam vel odio nisl. Nulla ornare dolor non gravida suscipit. Donec tincidunt luctus lorem nec aliquet. Nulla viverra nisi vitae nisl eleifend, in maximus neque venenatis. Proin lobortis leo metus, id placerat metus placerat at. Maecenas ac ipsum erat. Praesent dictum nulla vel justo pellentesque posuere. Aliquam sit amet dolor ut diam interdum lacinia in vel eros. Phasellus vitae eros vitae urna scelerisque vestibulum non eget risus. Vivamus sagittis orci turpis, vitae tempor neque imperdiet eget. Vestibulum bibendum orci sit amet sem interdum, vel sodales ante imperdiet. Vivamus condimentum diam sed purus efficitur, vel euismod tellus porttitor. Aenean quis auctor nisi. Proin mattis leo turpis, in malesuada augue porta luctus. Mauris pretium pellentesque nisl, vulputate rhoncus sapien viverra vitae.</p>
'
);

DROP TABLE if exists `campaign_recipient`;
CREATE TABLE `campaign_recipient` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `campaign_id` mediumint(9) NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  `email` varchar(128) DEFAULT NULL,
  `role` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

insert into campaign_recipient (campaign_id, name, email, role) values ( (select max(id) from campaign), 'Kevin O''Donnell', 'kevinodotnet@gmail.com', 'Coordinator');
insert into campaign_recipient (campaign_id, name, email, role) values ( (select max(id) from campaign), 'Another Person', 'kevinodotnet+another@gmail.com', 'Committee Member');

DROP TABLE if exists `campaign_question`;
CREATE TABLE `campaign_question` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `campaign_id` mediumint(9) NOT NULL,
  `title` varchar(128) DEFAULT NULL,
  `text` varchar(1024) DEFAULT NULL,
  `type` varchar(32) DEFAULT 'text',
  `required` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

insert into campaign_question (campaign_id, title, text, required) values ( (select max(id) from campaign), 'Full Name', '', 1);
insert into campaign_question (campaign_id, title, text, required) values ( (select max(id) from campaign), 'Address', 'Just street number, street name and unit will do.', 1);
insert into campaign_question (campaign_id, title, text, required) values ( (select max(id) from campaign), 'Postal Code', '', 1);
insert into campaign_question (campaign_id, title, text, required) values ( (select max(id) from campaign), 'Twitter handle', '(optional)', 1);

insert into campaign_question (campaign_id, title, text, required, type) values ( (select max(id) from campaign), 
'What would you close?', 
'Think about where you need to walk: for essentials, and just for mental health exercise. What streets, or parts of streets, would you close to vehicles if you could?',
1,'textarea');

insert into campaign_question (campaign_id, title, text, required, type) values ( (select max(id) from campaign), 
'Do drivers have an alternative?', 
'If the city restricted access in those places, do drivers ... help copy editors! ',
1,'textarea');

insert into campaign_question (campaign_id, title, text, required, type) values ( (select max(id) from campaign), 
'What is another question?', 
'The system allows any number of questions to be posed, to nudge users to give small answers and unblock writer''s fear.',
1,'textarea');
insert into campaign_question (campaign_id, title, text, required, type) values ( (select max(id) from campaign), 
'Got another question?', 
'We don''t want to ask too many, but maybe three is the sweet spot?',
1,'textarea');

DROP TABLE if exists `campaign_submission`;
CREATE TABLE `campaign_submission` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `campaign_id` mediumint(9) NOT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

DROP TABLE if exists `campaign_submission_value`;
CREATE TABLE `campaign_submission_value` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `submission_id` mediumint(9) NOT NULL,
  `question_id` mediumint(9) DEFAULT NULL,
  `name` varchar(128) DEFAULT NULL,
  `value` varchar(2048) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

