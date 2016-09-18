-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL COMMENT 'id of a user; matches to nationbuilder',
  `fullname` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'full name of the user; combined first + last name from nationbuilder',
  `creationdt` datetime DEFAULT NULL,
  `adminflg` tinyint(1) DEFAULT NULL COMMENT 'Determines if the user has admin access',
  `profileimage` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'profile image url',
  `email` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'email address',
  `mobile` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'mobile phone number',
  `latitude` double DEFAULT NULL COMMENT 'latitude of the user''s primary address',
  `longitude` double DEFAULT NULL COMMENT 'longitude of the user''s primary address',
  `tags` varchar(4000) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'tags associated with the user',
  `secretkey` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Stores a generated secret key to secure access',
  `pointpersonid` int(11) DEFAULT NULL COMMENT 'The Id of this users point person',
  `ispointperson` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Whether or not this person can be auto assigned as a point person.',
  PRIMARY KEY (`id`),
  KEY `pointpersonid` (`pointpersonid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='User table synchronized from NationBuilder for easier queryi';

--
-- Table structure for table `usersview`
--

DROP VIEW IF EXISTS `usersview`;
CREATE VIEW `usersview` AS select `users`.`id` AS `id`,`users`.`fullname` AS `fullname`,`users`.`profileimage` AS `profileimage`,`users`.`email` AS `email`,`users`.`mobile` AS `mobile`,`users`.`latitude` AS `latitude`,`users`.`longitude` AS `longitude`,`users`.`tags` AS `tags` from `users` where (`users`.`id` <> 1);


-- --------------------------------------------------------

--
-- Table structure for table `objectcategory`
--

DROP TABLE IF EXISTS `objectcategory`;
CREATE TABLE `objectcategory` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'primary key for object category',
    `name` varchar(500) COLLATE utf8_unicode_ci NOT NULL COMMENT 'name of the object category',
    PRIMARY KEY (`id`)
)  ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE = utf8_unicode_ci COMMENT='Stores the Object Categories for an Object' AUTO_INCREMENT=38;

--
-- Dumping data for table `objectcategory`
--

INSERT INTO `objectcategory` (`id`, `name`) VALUES(1, 'Antiques');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(2, 'Appliances');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(3, 'Arts + Crafts');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(4, 'ATVs / UTVs / Snow');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(5, 'Auto Parts');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(6, 'Baby + Kids');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(7, 'Beauty + Health');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(8, 'Bike Parts');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(9, 'Bikes');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(10, 'Boats');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(11, 'Books');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(12, 'Commercial Equipment');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(13, 'Cars + Trucks');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(14, 'CDs / DVD / VHS');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(15, 'Cell Phones');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(16, 'Clothes + Accessories');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(17, 'Collectibles');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(18, 'Computer Parts');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(19, 'Computers');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(20, 'Electronics');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(21, 'Farm + Garden');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(22, 'Furniture');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(23, 'General');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(24, 'Heavy Equipment');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(25, 'Household');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(26, 'Jewelry');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(27, 'Materials');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(28, 'Motorcycle Parts');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(29, 'Motorcycles');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(30, 'Music Instruments');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(31, 'Photo + Video');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(32, 'RVs');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(33, 'Sporting');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(34, 'Tickets');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(35, 'Tools');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(36, 'Toys + Games');
INSERT INTO `objectcategory` (`id`, `name`) VALUES(37, 'Video Gaming');

-- --------------------------------------------------------

--
-- Table structure for table `objectpermanence`
--

DROP TABLE IF EXISTS `objectpermanence`;
CREATE TABLE `objectpermanence` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'primary key',
    `name` varchar(500) COLLATE utf8_unicode_ci NOT NULL COMMENT 'name',
    PRIMARY KEY (`id`)
)  ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE = utf8_unicode_ci COMMENT='Holds the object permenance types for an object' AUTO_INCREMENT=3;

--
-- Dumping data for table `objectpermanence`
--

INSERT INTO `objectpermanence` (`id`, `name`) VALUES(1, 'Borrow');
INSERT INTO `objectpermanence` (`id`, `name`) VALUES(2, 'Keep');

-- --------------------------------------------------------

--
-- Table structure for table `phasesteps`
--

DROP TABLE IF EXISTS `phasesteps`;
CREATE TABLE `phasesteps` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `planphaseid` int(11) NOT NULL COMMENT 'Foreign Key to planphase',
  `name` varchar(2000) COLLATE utf8_unicode_ci NOT NULL COMMENT 'name of the plan step',
  `number` int(11) NOT NULL COMMENT 'Stores the step number.',
  `pointpersontask` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Whether or not the step needs to be completed by the user''s point person',
  PRIMARY KEY (`id`),
  KEY `planphasesid` (`planphaseid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Stores all of the steps needed to complete a phase.' AUTO_INCREMENT=64 ;

--
-- Dumping data for table `phasesteps`
--


-- --------------------------------------------------------

--
-- Table structure for table `planphases`
--

DROP TABLE IF EXISTS `planphases`;
CREATE TABLE `planphases` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
    `name` varchar(500) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name of the plan phase',
    `number` int(11) NOT NULL COMMENT 'Stores the number of the phase.',
    PRIMARY KEY (`id`)
)  ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE = utf8_unicode_ci COMMENT='Holds all of the phases for the plan' AUTO_INCREMENT=7;

--
-- Dumping data for table `planphases`
--

INSERT INTO `planphases` (`id`, `name`, `number`) VALUES(1, 'Phase 0 - "Seed Phase"', 0);
INSERT INTO `planphases` (`id`, `name`, `number`) VALUES(2, 'Phase I - "Water Phase" (Finding Community)', 1);
INSERT INTO `planphases` (`id`, `name`, `number`) VALUES(3, 'Phase II - "Germination Phase" (Building Community)', 2);
INSERT INTO `planphases` (`id`, `name`, `number`) VALUES(4, 'Phase III - "Weeding" (Community)', 3);
INSERT INTO `planphases` (`id`, `name`, `number`) VALUES(5, 'Phase IV - "Harvesting" (Community)', 4);
INSERT INTO `planphases` (`id`, `name`, `number`) VALUES(6, 'Phase V - "Pollination" (Community)', 5);

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

DROP TABLE IF EXISTS `skills`;
CREATE TABLE `skills` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id of the skill',
    `name` varchar(500) COLLATE utf8_unicode_ci NOT NULL COMMENT 'name of the skill',
    PRIMARY KEY (`id`)
)  ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE = utf8_unicode_ci COMMENT='Table of skills' AUTO_INCREMENT=33;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`id`, `name`) VALUES(1, 'Gardener');
INSERT INTO `skills` (`id`, `name`) VALUES(2, 'Carpenter');
INSERT INTO `skills` (`id`, `name`) VALUES(3, 'Elder Care');
INSERT INTO `skills` (`id`, `name`) VALUES(4, 'Nutritionist');
INSERT INTO `skills` (`id`, `name`) VALUES(5, 'Social Media Administrator');
INSERT INTO `skills` (`id`, `name`) VALUES(6, 'Advertising');
INSERT INTO `skills` (`id`, `name`) VALUES(7, 'Website Design');
INSERT INTO `skills` (`id`, `name`) VALUES(8, 'Alternative Home Building');
INSERT INTO `skills` (`id`, `name`) VALUES(9, 'Plumber');
INSERT INTO `skills` (`id`, `name`) VALUES(10, 'Cooking');
INSERT INTO `skills` (`id`, `name`) VALUES(11, 'Business Administration');
INSERT INTO `skills` (`id`, `name`) VALUES(12, 'Herbalist');
INSERT INTO `skills` (`id`, `name`) VALUES(13, 'Electrician');
INSERT INTO `skills` (`id`, `name`) VALUES(14, 'Marketing');
INSERT INTO `skills` (`id`, `name`) VALUES(15, 'Metal Smithing');
INSERT INTO `skills` (`id`, `name`) VALUES(16, 'Automation Systems Design');
INSERT INTO `skills` (`id`, `name`) VALUES(17, 'Leatherworker');
INSERT INTO `skills` (`id`, `name`) VALUES(18, 'Machinest');
INSERT INTO `skills` (`id`, `name`) VALUES(19, 'Filmmaking');
INSERT INTO `skills` (`id`, `name`) VALUES(20, 'Bookkeeping');
INSERT INTO `skills` (`id`, `name`) VALUES(21, 'Mechanic');
INSERT INTO `skills` (`id`, `name`) VALUES(22, 'Unix Administration');
INSERT INTO `skills` (`id`, `name`) VALUES(23, 'Sculptor');
INSERT INTO `skills` (`id`, `name`) VALUES(24, 'Counseling');
INSERT INTO `skills` (`id`, `name`) VALUES(25, 'Performer');
INSERT INTO `skills` (`id`, `name`) VALUES(26, 'Driver');
INSERT INTO `skills` (`id`, `name`) VALUES(27, 'Beekeeper');
INSERT INTO `skills` (`id`, `name`) VALUES(28, 'Accounting');
INSERT INTO `skills` (`id`, `name`) VALUES(29, 'Planner');
INSERT INTO `skills` (`id`, `name`) VALUES(30, 'Nursing');
INSERT INTO `skills` (`id`, `name`) VALUES(31, 'Doctor');
INSERT INTO `skills` (`id`, `name`) VALUES(32, 'Child Care');

-- --------------------------------------------------------

--
-- Table structure for table `spaces`
--

DROP TABLE IF EXISTS `spaces`;
CREATE TABLE `spaces` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary key of the space.',
    `name` varchar(4000) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name of the space.',
    `icon` varchar(4000) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Icon for the space.',
    PRIMARY KEY (`id`)
)  ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE = utf8_unicode_ci COMMENT='spaces' AUTO_INCREMENT=12;

--
-- Dumping data for table `spaces`
--

INSERT INTO `spaces` (`id`, `name`, `icon`) VALUES(1, 'Room in House / Flat', 'roominhouse.png');
INSERT INTO `spaces` (`id`, `name`, `icon`) VALUES(2, 'Room & Board / Flat', 'roomandboard.png');
INSERT INTO `spaces` (`id`, `name`, `icon`) VALUES(3, 'Holiday Home / Flat', 'holidayhome.png');
INSERT INTO `spaces` (`id`, `name`, `icon`) VALUES(4, 'Caravan / RV / Mobile Home', 'mobilehome.png');
INSERT INTO `spaces` (`id`, `name`, `icon`) VALUES(5, 'Garage', 'garage.png');
INSERT INTO `spaces` (`id`, `name`, `icon`) VALUES(6, 'Office', 'office.png');
INSERT INTO `spaces` (`id`, `name`, `icon`) VALUES(7, 'Studio', 'studio.png');
INSERT INTO `spaces` (`id`, `name`, `icon`) VALUES(8, 'Shop', 'shop.png');
INSERT INTO `spaces` (`id`, `name`, `icon`) VALUES(9, 'Community Space', 'communityspace.png');
INSERT INTO `spaces` (`id`, `name`, `icon`) VALUES(10, 'Land', 'land.png');
INSERT INTO `spaces` (`id`, `name`, `icon`) VALUES(11, 'Makerspace', 'makerspace.png');

-- --------------------------------------------------------

--
-- Table structure for table `userobjects`
--

DROP TABLE IF EXISTS `userobjects`;
CREATE TABLE `userobjects` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'primary key',
    `userid` int(11) NOT NULL COMMENT 'foreign key to users',
    `name` varchar(500) COLLATE utf8_unicode_ci NOT NULL COMMENT 'name of the object',
    `description` varchar(4000) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'description of the object',
    `image` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'image name',
    `permanenceid` int(11) DEFAULT NULL COMMENT 'foregin key to objectpermanence',
    `categoryid` int(11) DEFAULT NULL COMMENT 'foreign key to objectcategory',
	`latitude` double DEFAULT NULL COMMENT 'latitude of the Object',
	`longitude` double DEFAULT NULL COMMENT 'longitude of the Object',
	`location` varchar(4000) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'location of the Object',
    PRIMARY KEY (`id`),
    KEY `userid` (`userid`)
)  ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE = utf8_unicode_ci COMMENT='holds objects for users' AUTO_INCREMENT=19;

--
-- Dumping data for table `userobjects`
--


-- --------------------------------------------------------

--
-- Table structure for table `userobjectsview`
--

DROP VIEW IF EXISTS `userobjectsview`;
CREATE 
VIEW `userobjectsview` AS
    select 
        `uo`.`id` AS `id`,
        `uo`.`userid` AS `userid`,
        `uo`.`name` AS `name`,
        `uo`.`description` AS `description`,
        `uo`.`image` AS `image`,
        `uo`.`permanenceid` AS `permanenceid`,
        `uo`.`categoryid` AS `categoryid`,
		`uo`.`latitude` AS `latitude`,
		`uo`.`longitude` AS `longitude`,
		`uo`.`location` AS `location`,
        `oc`.`name` AS `categoryname`,
        `op`.`name` AS `permanencename`,
        `u`.`fullname` AS `fullname`
    from
        (((`userobjects` `uo`
        join `objectcategory` `oc` ON ((`uo`.`categoryid` = `oc`.`id`)))
        join `objectpermanence` `op` ON ((`uo`.`permanenceid` = `op`.`id`)))
        join `users` `u` ON ((`uo`.`userid` = `u`.`id`)));


-- --------------------------------------------------------

--
-- Table structure for table `userphasesteps`
--

DROP TABLE IF EXISTS `userphasesteps`;
CREATE TABLE `userphasesteps` (
    `userid` int(11) NOT NULL COMMENT 'Foreign Key to users',
    `phasestepid` int(11) NOT NULL COMMENT 'Foreign Key to phasesteps',
    `completed` tinyint(1) NOT NULL COMMENT 'whether or not the step is complete',
    PRIMARY KEY (`userid` , `phasestepid`)
)  ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE = latin1_general_ci COMMENT='Stores the users step completion';

--
-- Dumping data for table `userphasesteps`
--


-- --------------------------------------------------------

--
-- Table structure for table `userreviews`
--

DROP TABLE IF EXISTS `userreviews`;
CREATE TABLE `userreviews` (
    `reviewerid` int(11) NOT NULL COMMENT 'The reviwerid; foreign key to users',
    `revieweeid` int(11) NOT NULL COMMENT 'The reviewee; foreign key to users',
    `name` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'name given to the review',
    `review` varchar(4000) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The review text',
    `lastupdated` date DEFAULT NULL COMMENT 'The date the review was last updated',
    PRIMARY KEY (`reviewerid` , `revieweeid`)
)  ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE = utf8_unicode_ci COMMENT='Stores User Reviews';

--
-- Dumping data for table `userreviews`
--


-- --------------------------------------------------------

--
-- Table structure for table `userreviewsview`
--

DROP VIEW IF EXISTS `userreviewsview`;
CREATE View `userreviewsview` AS
    select 
        `userreviews`.`reviewerid` AS `reviewerid`,
        `userreviews`.`revieweeid` AS `revieweeid`,
        `userreviews`.`name` AS `name`,
        `userreviews`.`review` AS `review`,
        `userreviews`.`lastupdated` AS `lastupdated`,
        `users`.`fullname` AS `reviewerfullname`
    from
        (`userreviews`
        join `users` ON ((`userreviews`.`reviewerid` = `users`.`id`)));

--
-- Dumping data for table `userreviewsview`
--




-- --------------------------------------------------------

--
-- Table structure for table `userskills`
--

DROP TABLE IF EXISTS `userskills`;
CREATE TABLE `userskills` (
    `userid` int(11) NOT NULL COMMENT 'foreign key to user/signup id in nationbuilder',
    `skillid` int(11) NOT NULL COMMENT 'foreign key to skills table',
    `userrating` double DEFAULT '0' COMMENT 'user chosen rating',
	`description` varchar(4000) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Description of the user''s skill',
    PRIMARY KEY (`userid` , `skillid`),
    KEY `skillid` (`skillid`)
)  ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE = utf8_unicode_ci COMMENT='holds user-skill associations';


-- --------------------------------------------------------

--
-- Table structure for table `userskillsview`
--

DROP VIEW IF EXISTS `userskillsview`;
CREATE 
VIEW `userskillsview` AS
    select 
        `userskills`.`userid` AS `userid`,
        `userskills`.`skillid` AS `skillid`,
        `userskills`.`userrating` AS `userrating`,
		`userskills`.`description` AS `description`,
        `users`.`fullname` AS `fullname`,
        `skills`.`name` AS `name`
    from
        ((`userskills`
        join `skills` ON ((`userskills`.`skillid` = `skills`.`id`)))
        join `users` ON ((`userskills`.`userid` = `users`.`id`)));



-- --------------------------------------------------------

--
-- Table structure for table `userspaces`
--

DROP TABLE IF EXISTS `userspaces`;
CREATE TABLE `userspaces` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `userid` int(11) NOT NULL COMMENT 'Foreign key to users table.',
    `spaceid` int(11) NOT NULL COMMENT 'Foreign key to spaces table.',
    `privacy` int(11) DEFAULT NULL COMMENT 'The privacy setting for the space.',
    `name` varchar(4000) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The name of the space.',
    `description` varchar(4000) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The description of the space.',
    `location` varchar(4000) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Where the space is located.',
    `latitude` double NOT NULL COMMENT 'The latitude based on the location from google maps.',
    `longitude` double NOT NULL COMMENT 'The longitude based on the location from google maps.',
    PRIMARY KEY (`id`),
    KEY `userid` (`userid`),
    KEY `spaceid` (`spaceid`)
)  ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE = utf8_unicode_ci COMMENT='Contains information on the spaces that users have.' AUTO_INCREMENT=12;

--
-- Dumping data for table `userspaces`
--


-- --------------------------------------------------------

--
-- Table structure for table `userspacesview`
--

DROP VIEW IF EXISTS `userspacesview`;
CREATE 
VIEW `userspacesview` AS
    select 
        `users`.`fullname` AS `fullname`,
        `userspaces`.`id` AS `id`,
        `userspaces`.`userid` AS `userid`,
        `userspaces`.`spaceid` AS `spaceid`,
        `userspaces`.`privacy` AS `privacy`,
        `userspaces`.`name` AS `name`,
        `userspaces`.`description` AS `description`,
        `userspaces`.`location` AS `location`,
        `userspaces`.`latitude` AS `latitude`,
        `userspaces`.`longitude` AS `longitude`,
        `spaces`.`icon` AS `icon`
    from
        ((`users`
        join `userspaces` ON ((`users`.`id` = `userspaces`.`userid`)))
        join `spaces` ON ((`userspaces`.`spaceid` = `spaces`.`id`)));

		
--
-- Table structure for table `userphasestepsview`
--
DROP VIEW IF EXISTS	`userphasestepsview`;
CREATE VIEW `userphasestepsview` AS
SELECT ups.*, pp.name as planphasename, ps.name as phasestepname, ps.number as phasestepnumber, u.fullname, ps.planphaseid, ps.pointpersontask, u.pointpersonid, (select count(*) from userphasesteps inner join phasesteps on userphasesteps.phasestepid = phasesteps.id where completed=0 and userid = u.id and phasesteps.planphaseid = pp.id) as stepsremaining
FROM  userphasesteps ups 
inner join phasesteps ps on ps.id = ups.phasestepid 
inner join planphases pp on pp.id = ps.planphaseid
inner join users u on u.id = ups.userid;




--
-- Table structure for table `userbuds`
--

DROP TABLE IF EXISTS `userbuds`;
CREATE TABLE `userbuds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL COMMENT 'User who created the Bud',
  `name` varchar(500) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name of the BUD',
  `description` varchar(4000) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Description of the BUD',
  `seedperson` varchar(500) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The Seed Person for the BUD',
  `type` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'type of bud',
  `latitude` double DEFAULT NULL COMMENT 'latitude of the BUD',
  `longitude` double DEFAULT NULL COMMENT 'longitude of the BUD',
  `location` varchar(4000) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'location of the BUD',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Holds User created BUDs' AUTO_INCREMENT=1;


--
-- Table structure for table `budtypes`
--

DROP TABLE IF EXISTS `budtypes`;
CREATE TABLE `budtypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Id of the bud type',
  `name` varchar(500) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name of the bud type',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Contains types of BUDS' AUTO_INCREMENT=9 ;

--
-- Table structure for view 'userbudsview'
--

DROP VIEW IF EXISTS `userbudsview`;
CREATE 
VIEW `userbudsview` AS
    select ub.*, GROUP_CONCAT(bt.name SEPARATOR ', ') as typenames, u.fullname
    from
		userbuds ub inner join
		users u on u.id = ub.userid left join
		budtypes bt on FIND_IN_SET(bt.id, ub.type) > 0 
		group by ub.id;

--
-- Table structure for table `userbudsmembership`
--

DROP TABLE IF EXISTS `userbudsmembership`;
CREATE TABLE `userbudsmembership` (
  `userbudid` int(11) NOT NULL COMMENT 'id of the userbud record',
  `userid` int(11) NOT NULL COMMENT 'id of the user record',
  `status` int(11) DEFAULT NULL COMMENT 'membership status',
  PRIMARY KEY (`userbudid`,`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Stores information on who is a member of each bud';
		
--
-- Table structure for view 'userbudsmembershipallstatusview
--
DROP VIEW IF EXISTS `userbudsmembershipallstatusview`;
CREATE
VIEW `userbudsmembershipallstatusview` as 
	select 
		u.id, ub.id as ubid, ubm.*, (select count(*) from userbudsmembership ubm1 where ubm1.userbudid = ubm.userbudid and ubm1.status = 2) as membershipcount
	from 
		users u cross join 
		userbudsview ub left join
		userbudsmembership ubm on ubm.userid = u.id and ubm.userbudid = ub.id;
		
--
-- Table structure for view 'userbudsmembershipstatusview
--
DROP VIEW IF EXISTS `userbudsmembershipstatusview`;
CREATE 
VIEW `userbudsmembershipstatusview` AS
    select 
        ub.*, COALESCE(ubmsv.status,0) as status, ubmsv.id as membershipuserid, ubmsv.membershipcount
    from
		userbudsview ub left join
		userbudsmembershipallstatusview ubmsv on ubmsv.ubid = ub.id;



--
-- Table structure for view 'userbudsmembershipview'
--

DROP VIEW IF EXISTS `userbudsmembershipview`;
CREATE 
VIEW `userbudsmembershipview` AS
    select 
        ubm.*, u.fullname, ub.fullname as userbudowner, ub.name as userbudname, ub.description as userbuddescription, ub.seedperson as userbudseedperson
    from
		userbudsmembership ubm inner join
		users u on u.id = ubm.userid inner join
		userbudsview ub on ubm.userbudid = ub.id;
		
		
--
-- Table structure for table `locations`
--

DROP TABLE IF EXISTS `locations`;
CREATE TABLE `locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `name` varchar(4000) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name of the location',
  `icon` varchar(4000) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Icon to display on the map ',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='Stores information about location types that are available.' AUTO_INCREMENT=19 ;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` VALUES(1, 'Local Farms', NULL);
INSERT INTO `locations` VALUES(2, 'Community Supported Agriculture', NULL);
INSERT INTO `locations` VALUES(3, 'Community Gardens', NULL);
INSERT INTO `locations` VALUES(4, 'Farmer''s Markets', NULL);
INSERT INTO `locations` VALUES(5, 'Food Co-ops', NULL);
INSERT INTO `locations` VALUES(6, 'Wild Edibles', 'wildedibles.png');
INSERT INTO `locations` VALUES(7, 'Food Forest', NULL);
INSERT INTO `locations` VALUES(8, 'Bagel Shop', NULL);
INSERT INTO `locations` VALUES(9, 'Food Pantry', 'foodpantry.png');
INSERT INTO `locations` VALUES(10, 'Soup Kitchen', 'soupkitchen.png');
INSERT INTO `locations` VALUES(11, 'Happy Hour', 'happyhour.png');
INSERT INTO `locations` VALUES(12, 'Grocery Samples', 'grocerysamples.png');
INSERT INTO `locations` VALUES(13, 'Birthday Meals', 'birthdaymeals.png');
INSERT INTO `locations` VALUES(14, 'Pay What You Can Afford Restaurant', 'paywhatyoucanaffordrestaurant.png');
INSERT INTO `locations` VALUES(15, 'Potluck', 'potluck.png');
INSERT INTO `locations` VALUES(16, 'Food Not Bombs', 'foodnotbombs.png');
INSERT INTO `locations` VALUES(17, 'Food Is Free', 'foodisfree.png');
INSERT INTO `locations` VALUES(18, 'Falling Fruit', 'fallingfruit.png');
INSERT INTO `locations` VALUES(19, 'Dumpster/Thrown Away', 'treasure_chest.png');

-- --------------------------------------------------------

--
-- Table structure for table `userlocations`
--

DROP TABLE IF EXISTS `userlocations`;
CREATE TABLE `userlocations` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `userid` int(11) NOT NULL COMMENT 'Foreign Key to users',
  `locationid` int(11) NOT NULL COMMENT 'Foreign Key to locations',
  `name` varchar(500) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name of the location',
  `description` varchar(4000) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Description of the location',
  `location` varchar(4000) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Address of the location',
  `latitude` double NOT NULL COMMENT 'Latitude of the location',
  `longitude` double NOT NULL COMMENT 'Longitude of the location',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Holds information for user entered locations that are displa' AUTO_INCREMENT=2 ;

--
-- Table structure for table `userlocationsview`
--

DROP TABLE IF EXISTS `userlocationsview`;
CREATE VIEW `userlocationsview` AS select `users`.`fullname` AS `fullname`,`userlocations`.`id` AS `id`,`userlocations`.`userid` AS `userid`,`userlocations`.`locationid` AS `locationid`,`userlocations`.`name` AS `name`,`userlocations`.`description` AS `description`,`userlocations`.`location` AS `location`,`userlocations`.`latitude` AS `latitude`,`userlocations`.`longitude` AS `longitude`,`locations`.`icon` AS `icon` from ((`users` join `userlocations` on((`users`.`id` = `userlocations`.`userid`))) join `locations` on((`userlocations`.`locationid` = `locations`.`id`)));

--
-- Table structure for table `currentphasenumberbyuser`
--

DROP View IF EXISTS `currentphasenumberbyuser`;
CREATE  VIEW `currentphasenumberbyuser` AS select min(`planphases`.`number`) AS `number`,`userphasestepsview`.`userid` AS `userid` from (`userphasestepsview` join `planphases` on((`userphasestepsview`.`planphaseid` = `planphases`.`id`))) where `userphasestepsview`.`completed` = 0 group by `userphasestepsview`.`userid`;

--
-- Dumping data for table `budtypes`
--

INSERT INTO `budtypes` VALUES(1, 'Study & Analysis');
INSERT INTO `budtypes` VALUES(2, 'Strategy');
INSERT INTO `budtypes` VALUES(3, 'Support');
INSERT INTO `budtypes` VALUES(4, 'Training');
INSERT INTO `budtypes` VALUES(5, 'Cost Reduction');
INSERT INTO `budtypes` VALUES(6, 'Income Generation');
INSERT INTO `budtypes` VALUES(7, 'Community Creation');
INSERT INTO `budtypes` VALUES(8, 'Social Change');

--
-- Structure for view `phasestepsview`
--
Drop view if exists phasestepsview;
CREATE VIEW phasestepsview AS SELECT ps.* , pp.name AS `planname` , pp.number AS `plannumber` 
FROM 
 phasesteps ps 
inner JOIN planphases pp ON 
 ps.planphaseid = pp.id; 

--
-- Dumping data for table `phasesteps`
--

INSERT INTO `phasesteps` (`id`, `planphaseid`, `name`, `number`) VALUES
(2, 1, '<div align="left">I am ready and willing to meet new people</div>', 1),
(3, 1, '<div align="left">I know about <a href="http://www.ayahuasca-wasi.com/english/articles/NVC.pdf">non-violent communication</a> or I watched this <a href="https://www.youtube.com/watch?v=YwXH4hNfgPg">video​</a></div>', 3),
(4, 1, '<div align="left">I have <a href="/member_skills">skills</a> that I am willing to share</div>', 2),
(5, 1, '<div align="left">I am ready and willing to <a href="/take_action">participate</a> in the Transition​</div>', 4),
(6, 2, 'I joined or <a href="/steps_to_organizing_a_local_bud_group">created</a> a BUD​', 40),
(7, 2, 'I attended the meeting of one or more BUDs', 30),
(8, 2, 'I contacted the seed person of one or more BUDs', 20),
(9, 2, 'I am aware of the available <a href="/buds">BUDs</a>', 10),
(10, 3, 'Our BUD has 5 - 15 people​', 5),
(11, 3, 'Our BUD has created a written Trust​', 1),
(12, 3, 'Our BUD has a minimum of one meeting per month which are posted on the <a href="/calendar">Events</a> page​', 3),
(13, 3, 'I am participating in one or more projects for our BUD​', 2),
(14, 4, 'Our BUD is functioning, but not yet self-sustaining​', 6),
(15, 4, 'Our BUD has pooled all of it''s resources into a "Stone Soup" inventory which is actively managed', 4),
(16, 4, 'All of our BUD members are located on the land', 3),
(17, 4, 'Our BUD agreed on a location and acquired land', 2),
(18, 5, 'Our BUD is self-sustainable', 3),
(19, 5, 'Our BUD identified areas for improvement', 1),
(20, 6, 'Our BUD has started a new BUD', 3),
(21, 6, 'Our BUD is producing more than it can consume', 1),
(22, 1, 'I&nbsp;am invested in improving my <a href="/learn">education</a>', 5),
(23, 1, 'I have reviewed and endorsed The Transition''s <a href="/our_trust">Trust​</a>.', 6),
(24, 5, 'Our BUD has proposed, assessed, and developed at least one Ephemeralization Project to fully sustain the Basic Needs of the target population.', 2),
(25, 3, 'Our BUD has done at least one program from The Transition Contributor Handbook<br>', 4),
(26, 4, 'Our BUD has started a cottage industry, business or another stream of income<br>', 5),
(27, 4, 'Our BUD has found a mentoring BUD<br>', 1),
(28, 5, 'Our BUD is now paying <a href="/become_a_contributor">contributor dues</a> on behalf of it''s members​', 5),
(29, 5, 'Our BUD is acting in an egalitarian manner', 3),
(30, 6, 'Our BUD is helping to pollinate upcoming Transitional Communities​', 2),
(31, 6, 'Our BUD has developed educational materials for the <a href="/community_brain">Community Brain</a><br>', 4),
(32, 7, 'I have learned about <a href="/decision_making">decision-making</a> and the use of <a href="/formal_consensus_used_with_buds">Formal Consensus</a> in BUDs​', 4),
(33, 7, 'I have learned about <a href="/bud_meeting_facilitation">BUD meeting facilitation​</a>', 3),
(34, 7, 'I know about the different <a href="/types_of_buds">types of BUDS​</a>', 2),
(35, 7, 'I understand <a href="/the_function_of_buds">the function of BUDs</a>', 1),
(36, 7, 'I have reviewed the following pages on <a href="/conflict_resolution">Conflict Resolution​</a>:<br><ul><li><a href="/assumptions_about_conflict">Assumptions About Conflict</a></li><li><a href="/separate_identification_from_resolution">Seperate Identification From Resolution</a></li></ul>', 5),
(37, 7, 'I have reviewed the various <a href="/stages_dynamics_of_conflict_resolution">Stages and Dynamics of Conflict Resolution</a>:<br><ul><li><a href="/conciliation">Conciliation</a></li><li><a href="/negotiation_bargaining">Negotiation &amp; Bargaining</a></li><li><a href="/implementation">Implementation</a></li><li><a href="/evaluation">Evaluation</a></li><li><a href="/the_role_of_third_party_neutral">The Role of Third Party Neutral</a></li><li><a href="/processes_for_resolving_conflicts">Processes for Resolving Conflicts</a></li></ul>', 8),
(38, 7, 'I have reviewed the <a href="/conflict_analysis">Conflict Analysis Material​</a>', 7),
(39, 7, 'I have reviewed the <a href="/dimensions_of_conflict">Dimensions of Conflict</a>', 6),
(40, 2, '<font color="#2f2f2f" face="tahoma, arial, verdana, sans-serif">​I have watched the following videos called<a href="http://thetransition.nationbuilder.com/the_basics"> The Basics</a>:</font><div><ul><li><font color="#2f2f2f" face="tahoma, arial, verdana, sans-serif"><a href="http://thetransition.nationbuilder.com/the_most_astounding_fact">The Most Astounding Fact</a></font></li><li><font color="#2f2f2f" face="tahoma, arial, verdana, sans-serif"><a href="http://thetransition.nationbuilder.com/you_the_people_have_the_power">You the People Have the Power</a></font></li><li><a href="http://thetransition.nationbuilder.com/what_really_motivates_us">What Really Motivates Us</a></li><li><a href="http://thetransition.nationbuilder.com/unnecessary_consumption">Unnecessary Consumption</a></li><li><a href="http://thetransition.nationbuilder.com/our_declaration_of_interdependence">Declaration of Interdependence</a></li><li><a href="http://thetransition.nationbuilder.com/starting_a_movement">Start a Movement</a></li><li><a href="http://thetransition.nationbuilder.com/the_pale_blue_dot_you_are_here">The Pale Blue Dot</a></li></ul></div>', 1),
(41, 1, 'Help The Transition <a href="/spread_the_word">spread the word</a> by referring one new website user<br>', 100),
(42, 2, 'Help The Transition <a href="/spread_the_word">spread the word</a> by referring one new website user<br>', 100),
(43, 3, 'Help The Transition <a href="/spread_the_word">spread the word</a> by referring one new website user<br>', 100),
(44, 4, 'Help The Transition <a href="/spread_the_word">spread the word</a> by referring one new website user<br>', 100),
(45, 5, 'Help The Transition <a href="/spread_the_word">spread the word</a> by referring one new website user<br>', 100),
(46, 6, 'Help The Transition <a href="/spread_the_word">spread the word</a> by referring one new website user<br>', 100),
(47, 7, 'Help The Transition <a href="/spread_the_word">spread the word</a> by referring one new website user<br>', 100),
(49, 1, 'I have read <a href="/set_your_intention">Set your intention​</a>', 8),
(51, 1, 'I have read <a href="/get_off_your_buts">Get off your buts​</a>', 7),
(52, 1, 'I have reviewed and agree to follow ​the <a href="/standards">Standards</a>.<br>', 9),
(54, 2, 'I am invested in continuing to improve my <a href="/learn">education</a>.', 99),
(55, 3, 'I am invested in continuing to improve my <a href="/learn">education</a>.', 99),
(56, 4, 'I am invested in continuing to improve my <a href="/learn">education</a>.', 99),
(57, 5, 'I am invested in continuing to improve my <a href="/learn">education</a>.', 99),
(58, 6, 'I am invested in continuing to improve my <a href="/learn">education</a>.', 99),
(59, 7, 'I am invested in continuing to improve my <a href="/learn">education</a>.', 99),
(60, 2, 'I am aware of how to <a href="/find_nearby_contributors">find nearby members</a><br>', 2),
(61, 2, 'I am aware of the available <a href="/spaces">spaces​</a>', 3),
(62, 2, 'I am aware of the available <a href="/objects">objects​</a>', 4);



--
-- Table structure for table `usersuggestions`
--

DROP TABLE IF EXISTS `usersuggestions`;
CREATE TABLE `usersuggestions` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id of the suggestion',
  `userid` int(11) NOT NULL COMMENT 'Id of the user who made the suggestion',
  `name` varchar(1000) COLLATE utf8_unicode_ci NOT NULL COMMENT 'name or title of the suggestion',
  `description` varchar(4000) COLLATE utf8_unicode_ci NOT NULL COMMENT 'description of the suggestion',
  `slug` varchar(1000) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The slug of the page where the suggestion was made',
  `lastupdated` datetime NOT NULL COMMENT 'Date record was last updated',
  `officialresponse` varchar(4000) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The official response given for the suggestion',
  `creationdt` datetime NOT NULL COMMENT 'Date the comment was created',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Stores page level user created suggestions.' AUTO_INCREMENT=3 ;

--
-- Structure for view `usersuggestionsview`
--
Drop view if exists usersuggestionsview;
CREATE VIEW usersuggestionsview AS SELECT us.* , u.fullname
FROM 
 usersuggestions us 
inner JOIN users u ON 
 u.id = us.userid; 
 
 
 --
-- Table structure for table `userorgsignup`
--

CREATE TABLE IF NOT EXISTS `userorgsignup` (
  `id` int(11) NOT NULL COMMENT 'id of the the signup',
  `userid` int(11) NOT NULL COMMENT 'userid of the user who is signing up',
  `type` varchar(500) COLLATE utf8_unicode_ci NOT NULL COMMENT 'type of organization',
  `membercount` int(11) NOT NULL COMMENT 'number of members',
  `websitelink` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'link to the website',
  `facebooklink` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'link to the facebook page',
  `additionalinfo` varchar(4000) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Additional information about the organization',
  `name` varchar(500) COLLATE utf8_unicode_ci NOT NULL COMMENT 'name of the organization'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Stores organizations signup information';

