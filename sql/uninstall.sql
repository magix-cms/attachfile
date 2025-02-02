TRUNCATE TABLE `mc_product_attachfile`;
DROP TABLE `mc_product_attachfile`;

DELETE FROM `mc_admin_access` WHERE `id_module` IN (
    SELECT `id_module` FROM `mc_module` as m WHERE m.name = 'attachfile'
);