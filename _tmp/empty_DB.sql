CREATE DATABASE `investorportal` /*!40100 DEFAULT CHARACTER SET utf8 */;

CREATE TABLE `investorportal`.`ip_usergroup` (
  `ugID` varchar(8) NOT NULL,
  `ugName` varchar(75) NOT NULL,
  PRIMARY KEY (`ugID`),
  UNIQUE KEY `ugID_UNIQUE` (`ugID`),
  UNIQUE KEY `ugName_UNIQUE` (`ugName`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
INSERT INTO `investorportal`.`ip_usergroup` (`ugID`,`ugName`) VALUES ('adm','Admin');
INSERT INTO `investorportal`.`ip_usergroup` (`ugID`,`ugName`) VALUES ('acct','Accounting');
INSERT INTO `investorportal`.`ip_usergroup` (`ugID`,`ugName`) VALUES ('astmgr','Asset Manager');
INSERT INTO `investorportal`.`ip_usergroup` (`ugID`,`ugName`) VALUES ('amadm','Asset Manager Admin');
INSERT INTO `investorportal`.`ip_usergroup` (`ugID`,`ugName`) VALUES ('exec','Executive');
INSERT INTO `investorportal`.`ip_usergroup` (`ugID`,`ugName`) VALUES ('inv','Investor');

CREATE TABLE `investorportal`.`ip_investors` (
  `iID` int(11) NOT NULL AUTO_INCREMENT,
  `iName` varchar(150) NOT NULL,
  PRIMARY KEY (`iID`),
  UNIQUE KEY `iID_UNIQUE` (`iID`),
  UNIQUE KEY `iName_UNIQUE` (`iName`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
INSERT INTO `investorportal`.`ip_investors` (`iName`) VALUES ('Default');

CREATE TABLE `investorportal`.`ip_reportgroup` (
  `rgID` int(11) NOT NULL AUTO_INCREMENT,
  `rgName` varchar(100) NOT NULL,
  `rgHide` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`rgID`),
  UNIQUE KEY `rgID_UNIQUE` (`rgID`),
  UNIQUE KEY `rgName_UNIQUE` (`rgName`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `investorportal`.`ip_reports` (
  `rID` int(11) NOT NULL AUTO_INCREMENT,
  `rName` varchar(250) NOT NULL,
  `rFile` varchar(150) NOT NULL,
  `rDateTimeStamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ReportGroup` int(11) NOT NULL,
  `Investor` int(11) NOT NULL,
  `ReportStatus` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`rID`),
  UNIQUE KEY `rID_UNIQUE` (`rID`),
  KEY `rgID` (`ReportGroup`),
  KEY `InvID` (`Investor`),
  CONSTRAINT `InvID` FOREIGN KEY (`Investor`) REFERENCES `investorportal`.`ip_investors` (`iID`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `rgID` FOREIGN KEY (`ReportGroup`) REFERENCES `investorportal`.`ip_reportgroup` (`rgID`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `investorportal`.`ip_users` (
  `uID` int(11) NOT NULL AUTO_INCREMENT,
  `uName` varchar(25) NOT NULL,
  `FirstName` varchar(45) NOT NULL,
  `LastName` varchar(45) NOT NULL,
  `eMail` varchar(100) DEFAULT NULL,
  `ugID` varchar(8) NOT NULL,
  `iID` int(11) NOT NULL,
  `ePassword` varchar(255) NOT NULL,
  PRIMARY KEY (`uID`),
  UNIQUE KEY `idUsers_UNIQUE` (`uID`),
  UNIQUE KEY `uName_UNIQUE` (`uName`),
  KEY `iID` (`iID`),
  KEY `ugID` (`ugID`),
  KEY `investID` (`iID`),
  KEY `usergrpID` (`ugID`),
  CONSTRAINT `investID` FOREIGN KEY (`iID`) REFERENCES `investorportal`.`ip_investors` (`iID`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `usergrpID` FOREIGN KEY (`ugID`) REFERENCES `investorportal`.`ip_usergroup` (`ugID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE USER 'ipadmin'@'localhost' IDENTIFIED BY 'r3p0rT1ng';
GRANT DELETE, INSERT, SELECT, UPDATE ON `investorportal`.* TO 'ipadmin'@'localhost';
INSERT INTO `investorportal`.`ip_users` (`uName`,`FirstName`,`LastName`,`ugID`,`iID`,`ePassword`) VALUES ('admin','Default','Admin','adm',1,'$2y$07$gys0Bvpi7MleWLh3SqHIhe1T5xI53ZgMe0R2BtsmNb3tAZsoEajHS');
