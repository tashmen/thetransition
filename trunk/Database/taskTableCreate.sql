delimiter $$

CREATE TABLE `tasks` (
  `taskid` varchar(4000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `interaction` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tasktitle` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `taskdescription` varchar(4000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `commenttext` varchar(4000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `createddate` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `starteddate` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `duedate` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `completeddate` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `closeddate` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2918 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci$$

delimiter $$

CREATE TABLE `taskcomments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `taskid` int(11) NOT NULL,
  `commenttext` varchar(4000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `createddate` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`,`taskid`)
) ENGINE=InnoDB AUTO_INCREMENT=648 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci$$

delimiter $$

CREATE VIEW `tasksview` AS select `tasks`.`taskid` AS `taskid`,`tasks`.`interaction` AS `interaction`,`tasks`.`tasktitle` AS `tasktitle`,`tasks`.`taskdescription` AS `taskdescription`,`tasks`.`commenttext` AS `commenttext`,`tasks`.`createddate` AS `createddate`,`tasks`.`starteddate` AS `starteddate`,`tasks`.`duedate` AS `duedate`,`tasks`.`completeddate` AS `completeddate`,`tasks`.`closeddate` AS `closeddate`,`tasks`.`id` AS `id`,`tasks`.`userid` AS `userid`,`users`.`fullname` AS `fullname` from (`tasks` join `users` on((`tasks`.`userid` = `users`.`id`)))$$

