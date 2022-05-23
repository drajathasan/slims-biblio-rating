<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-05-22 21:43:21
 * @modify date 2022-05-23 08:57:47
 * @license GPLv3
 * @desc [description]
 */

include_once __DIR__ . DS . '..' . DS . 'helper.php';

class CreateBiblioRating extends \SLiMS\Migration\Migration
{
    function up()
    {
        backupFile();
        $SQL = <<<SQL
            CREATE TABLE IF NOT EXISTS `biblio_rating` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `biblio_id` int(11) NOT NULL,
                `1` int(11) NOT NULL DEFAULT 0,
                `2` int(11) NOT NULL DEFAULT 0,
                `3` int(11) NOT NULL DEFAULT 0,
                `4` int(11) NOT NULL DEFAULT 0,
                `5` int(11) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `biblio_id` (`biblio_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
            CREATE TABLE IF NOT EXISTS `biblio_rating_log` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `biblio_rating_id` int(11) NOT NULL DEFAULT 0,
                `star` int(11) NOT NULL DEFAULT 0,
                `ip` varchar(15) COLLATE utf8mb4_bin DEFAULT NULL,
                `created_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `biblio_rating_id` (`biblio_rating_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        SQL;
        
        \SLiMS\DB::getInstance()->query($SQL);
    }

    function down()
    {
        
    }
}