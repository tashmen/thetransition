-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
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
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='User table synchronized from NationBuilder for easier queryi';

--
-- Table structure for table `usersview`
--

DROP TABLE IF EXISTS `usersview`;
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
    PRIMARY KEY (`id`),
    KEY `planphasesid` (`planphaseid`)
)  ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE = utf8_unicode_ci COMMENT='Stores all of the steps needed to complete a phase.' AUTO_INCREMENT=2;

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

INSERT INTO `planphases` (`id`, `name`, `number`) VALUES(1, 'Phase 0 -�Seed Phase�', 0);
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
SELECT ups.*, pp.name as planphasename, ps.name as phasestepname, ps.number as phasestepnumber, u.fullname, ps.planphaseid 
FROM  userphasesteps ups 
inner join phasesteps ps on ps.id = ups.phasestepid 
inner join planphases pp on pp.id = ps.planphaseid
inner join users u on u.id = ups.userid




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
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Holds User created BUDs' AUTO_INCREMENT=1 ;

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
		group by ub.id
		


--
-- Table structure for view 'userbudsmembershipstatusview
--
DROP VIEW IF EXISTS `userbudsmembershipstatusview`;
CREATE 
VIEW `userbudsmembershipstatusview` AS
    select 
        ub.*, COALESCE(ubmsv.status,0) as status, ubmsv.id as membershipuserid
    from
		userbudsview ub left join
		userbudsmembershipallstatusview ubmsv on ubmsv.ubid = ub.id
		

--
-- Table structure for view 'userbudsmembershipallstatusview
--
DROP VIEW IF EXISTS `userbudsmembershipallstatusview`;
CREATE
VIEW `userbudsmembershipallstatusview` as 
	select 
		u.id, ub.id as ubid, ubm.* 
	from 
		users u cross join 
		userbudsview ub left join
		userbudsmembership ubm on ubm.userid = u.id


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
		userbudsview ub on ubm.userbudid = ub.id
		
		
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

DROP TABLE IF EXISTS `currentphasenumberbyuser`;
CREATE  VIEW `currentphasenumberbyuser` AS select `planphases`.`number` AS `number`,`userphasestepsview`.`userid` AS `userid`,`userphasestepsview`.`phasestepid` AS `phasestepid`,`userphasestepsview`.`completed` AS `completed`,`userphasestepsview`.`planphasename` AS `planphasename`,`userphasestepsview`.`phasestepname` AS `phasestepname`,`userphasestepsview`.`phasestepnumber` AS `phasestepnumber`,`userphasestepsview`.`fullname` AS `fullname`,`userphasestepsview`.`planphaseid` AS `planphaseid` from (`userphasestepsview` join `planphases` on((`userphasestepsview`.`planphaseid` = `planphases`.`id`))) where `userphasestepsview`.`completed` = 0 group by `userphasestepsview`.`planphaseid`,`userphasestepsview`.`userid` order by `planphases`.`number`;

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

