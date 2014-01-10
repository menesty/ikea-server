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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `paragon_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pargon_id` int(11) DEFAULT NULL,
  `productNumber` varchar(45) DEFAULT NULL,
  `count` decimal(8,2) DEFAULT NULL,
  `shortName` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `warehouse_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `originalProductNumber` varchar(10) DEFAULT NULL,
  `productNumber` varchar(45) DEFAULT NULL,
  `price` decimal(8,2) DEFAULT NULL,
  `count` decimal(8,2) DEFAULT NULL,
  `weight` decimal(8,3) DEFAULT NULL,
  `zestav` bit(1) DEFAULT b'0',
  `visible` bit(1) DEFAULT b'1',
  `shortName` varchar(255) DEFAULT NULL,
  `allowed` bit(1) DEFAULT b'1',
  `orderId` int(11) DEFAULT NULL,
  `invoicePdf` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `counterparty` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `lastName` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;