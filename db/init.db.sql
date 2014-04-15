CREATE SCHEMA `u532766986_ikea` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ;

CREATE TABLE `driver` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `paragon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `driver_id` int(11) DEFAULT NULL,
  `counterparty_id` int(11) DEFAULT NULL,
  `createdDate` datetime DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `price` decimal(8,2) DEFAULT NULL,
  `downloaded` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `paragon_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paragonId` int(11) DEFAULT NULL,
  `productNumber` varchar(45) NOT NULL,
  `count` decimal(8,2) DEFAULT NULL,
  `price` decimal(8,2) DEFAULT NULL,
  `shortName` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `warehouse_item` (
  `productId` varchar(45) NOT NULL,
  `productNumber` varchar(45) NOT NULL,
  `weight` decimal(8,3) DEFAULT NULL,
  `zestav` tinyint(4) DEFAULT '0',
  `shortName` varchar(255) DEFAULT NULL,
  `orderId` int(11) DEFAULT NULL,
  KEY `productNumber` (`productNumber`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `warehouse` (
  `productNumber` varchar(45) NOT NULL,
  `count` decimal(8,2) DEFAULT NULL,
  `price` decimal(8,2) DEFAULT NULL,
  `visible` tinyint(4) DEFAULT '1',
  `allowed` tinyint(4) DEFAULT '1',
  KEY `productNumber` (`productNumber`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `counterparty` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `lastName` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `paragon` ADD COLUMN `price` DECIMAL(10,3) NULL  AFTER `order_id` ;
ALTER TABLE `paragon` ADD COLUMN `downloaded` TINYINT(4) NULL DEFAULT 0  AFTER `price` ;
