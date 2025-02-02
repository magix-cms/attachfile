CREATE TABLE IF NOT EXISTS `mc_product_attachfile` (
    `id_paf` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_product` int(11) UNSIGNED NOT NULL,
    `name_paf` varchar(150) DEFAULT NULL,
    `type_paf` varchar(10) DEFAULT NULL,
    `order_paf` int(11) UNSIGNED NOT NULL DEFAULT '0',
    `date_register` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_paf`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
