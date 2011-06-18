CREATE TABLE `velib_station` (
  `id` int(11) NOT NULL,
  `bonus` tinyint(4) NOT NULL,
  `fullAddress` text COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lat` float NOT NULL,
  `lng` float NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `open` tinyint(4) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `velib_station_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `station_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `available` int(11) NOT NULL,
  `free` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `ticket` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `station_idx` (`station_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

