<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-05-22 21:40:01
 * @modify date 2022-05-23 10:09:12
 * @license GPLv3
 * @desc [description]
 */

use SLiMS\DB;

if (!function_exists('setBiblioRating'))
{
    function setBiblioRating($biblio_id, $star)
    {
        // create database instance with PDO
        $db = DB::getInstance();

        // Convert to int, just make it safe
        $star = (int)$star;
        $url = $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];

        // Csrf token check for rating
        if (!\Volnix\CSRF\CSRF::validate($_POST, 'rating_csrf')) {
            session_unset();
            echo '<script type="text/javascript">';
            echo 'alert("Invalid login form!");';
            echo 'location.href = \'' . $url . '\';';
            echo '</script>';
            exit();
        }

        // Checking data
        $existsState = $db->prepare('select `id`,`biblio_id` from `biblio_rating` where `biblio_id` = ?');
        $existsState->execute([$biblio_id]);

        // Set state based on available data
        $state = 'insert into';
        if ($existsState->rowCount() === 1) $state = 'update';
        
        // Set query
        $SQL = "{$state} `biblio_rating` set `{$star}` = `{$star}` + 1";

        // Set criteria
        if ($state === 'insert into') $SQL .= ', `biblio_id` = ?';
        if ($state === 'update') $SQL .= " where `biblio_id` = ?";

        // set prepare statement
        $statement = $db->prepare($SQL);
        $statement->execute([$biblio_id]);

        // set ip
        $ip = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];

        // set prepare data to insert log
        $logState = $db->prepare('insert into `biblio_rating_log` set `biblio_rating_id` = ?, `star` = ?, `ip` = ?');
        if ($state === 'insert into')
        {
            $lastId = $db->lastInsertId();
            $logState->execute([$lastId, $star, $ip]);
        }
        else
        {
            $lastData = $existsState->fetch(PDO::FETCH_OBJ);
            $logState->execute([$lastData->id, $star, $ip]);
        }

        utility::jsAlert('Terimkasih untuk penilaian anda');

        echo '<script type="text/javascript">';
        echo 'location.href = \'' . $url . '\';';
        echo '</script>';
        exit;
    }
}

if (!function_exists('getBiblioRating'))
{
    function getBiblioRating($biblio_id)
    {
        $db = DB::getInstance();

        // set query statement
        $statement = $db->prepare('select `1`,`2`,`3`,`4`,`5` from `biblio_rating` where `biblio_id` = ?');
        $statement->execute([$biblio_id]);

        // Check if no data
        $data = ['decimal' => null, 'percent' => []];
        if ($statement->rowCount() < 1) return $data;

        // get data
        $ratingData = $statement->fetch(PDO::FETCH_NUM);

        // Processing
        $allData = array_sum($ratingData);

        $data['percent'] = [
            1 => round(($ratingData[0] / $allData * 100), 1),
            2 => round(($ratingData[1] / $allData * 100), 1),
            3 => round(($ratingData[2] / $allData * 100), 1),
            4 => round(($ratingData[3] / $allData * 100), 1),
            5 => round(($ratingData[4] / $allData * 100), 1)
        ];

        $data['decimal'] = round((
            ($ratingData[0] * 1) + 
            ($ratingData[1] * 2) + 
            ($ratingData[2] * 3) + 
            ($ratingData[3] * 4) + 
            ($ratingData[4] * 5)
        ) / $allData, 1);

        return $data;
    }
}

if (!function_exists('generateRating'))
{
    function generateRating($biblio_id)
    {
        $rating = getBiblioRating($biblio_id);
        ?>
            <h5 class="mt-4 mb-1">Penilaian</h5>
            <div class="d-flex">
                <div class="text-center">
                    <h1 style="font-size: 3rem;padding: 0 5px 0 5px;"><?= $rating['decimal']??'0,0' ?></h1>
                    <strong class="text-sm">dari 5</strong>
                </div>
                <div>
                    <?php for($star = 5; $star >= 1; $star--): ?>
                        <div class="d-flex my-1 justify-content-end">
                        <?php for($perstar = 1; $perstar <= $star; $perstar++): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" fill="currentColor" style="margin: 0 2px 0 2px" viewBox="0 0 16 16">
                                <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
                            </svg>
                        <?php endfor; ?>
                        </div>
                    <?php endfor; ?>
                </div>
                <div class="w-25 ml-1">
                    <?php for($star = 5; $star >= 1; $star--): ?>
                        <div class="progress my-1" style="height: 8px">
                            <div class="progress-bar" style="width: <?= isset($rating['percent'][$star]) ? str_replace(',', '.', $rating['percent'][$star]) : 0?>%" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
            <div class="w-50">
                <form class="d-flex" method="POST">
                    <?= \Volnix\CSRF\CSRF::getHiddenInputString('rating_csrf') ?>
                    <input type="hidden" name="biblio_id" value="<?= (int)$_GET['id'] ?>"/>
                    <input type="hidden" name="star" value="0"/>
                    Penilaian anda saat ini : &nbsp;
                    <?php for($star = 1; $star <= 5; $star++): ?>
                        <div class="d-flex my-1 cursor-pointer">
                            <svg id="parentStar<?= $star ?>" data-active="unstar<?= $star ?>" xmlns="http://www.w3.org/2000/svg" width="22" height="22" style="margin: 0 2px 0 2px" viewBox="0 0 16 16">
                                <path id="star<?= $star ?>" data-no="<?= $star ?>" class="starhover d-none" style="fill: #007bff" d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
                                <path id="unstar<?= $star ?>" data-no="<?= $star ?>" class="starhover" d="M2.866 14.85c-.078.444.36.791.746.593l4.39-2.256 4.389 2.256c.386.198.824-.149.746-.592l-.83-4.73 3.522-3.356c.33-.314.16-.888-.282-.95l-4.898-.696L8.465.792a.513.513 0 0 0-.927 0L5.354 5.12l-4.898.696c-.441.062-.612.636-.283.95l3.523 3.356-.83 4.73zm4.905-2.767-3.686 1.894.694-3.957a.565.565 0 0 0-.163-.505L1.71 6.745l4.052-.576a.525.525 0 0 0 .393-.288L8 2.223l1.847 3.658a.525.525 0 0 0 .393.288l4.052.575-2.906 2.77a.565.565 0 0 0-.163.506l.694 3.957-3.686-1.894a.503.503 0 0 0-.461 0z"/>
                            </svg>
                        </div>
                    <?php endfor; ?>
                    <button type="submit" name="rateIt" class="btn btn-sm btn-success mx-2">Simpan</button>
                </form>
            </div>
            <hr>
        <?php
    }
}

if (!function_exists('backupFile'))
{
    function backupFile()
    {
        if (!file_exists(SB . 'index.orig.php'))
        {
            @copy(SB . 'index.php', SB . 'index.orig.php');
        }

        @copy(__DIR__ . DS . 'pages' . DS . 'index.slims.php', SB . 'index.php');
    }
}

if (!function_exists('pluginRatingUrl'))
{
    function pluginRatingUrl($additionalUrl = '')
    {
        $baseUrl = str_replace(SB, SWB, __DIR__) . DS;

        return $baseUrl . $additionalUrl;
    }
}