CREATE TABLE `wishlist_list_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `extras` text,
  `options` text,
  `sort_order` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

CREATE TABLE `wishlist_lists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `description` longtext,
  `sort_order` int(11) DEFAULT NULL,
  `created_user_id` int(11) DEFAULT NULL,
  `is_enabled` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

CREATE TABLE `wishlist_lists_customers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `shop_customer_id` int(11) DEFAULT NULL,
  `wishlist_list_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;

CREATE TABLE `wishlist_lists_items` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `wishlist_list_id` int(11) DEFAULT NULL,
  `wishlist_list_item_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;