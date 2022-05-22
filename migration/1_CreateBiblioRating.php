<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-05-22 21:43:21
 * @modify date 2022-05-22 21:50:13
 * @license GPLv3
 * @desc [description]
 */

class CreateBiblioRating extends \SLiMS\Migration\Migration
{
    function up()
    {
        \SLiMS\DB::getInstance()->query("CREATE TABLE IF NOT EXISTS `biblio_rating` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `biblio_id` int(11) NOT NULL,
            `1` int(11) NOT NULL DEFAULT 0,
            `2` int(11) NOT NULL DEFAULT 0,
            `3` int(11) NOT NULL DEFAULT 0,
            `4` int(11) NOT NULL DEFAULT 0,
            `5` int(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`),
            KEY `biblio_id` (`biblio_id`)
          ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;");
    }

    function down()
    {
        
    }
}