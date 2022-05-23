<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-05-23 09:29:40
 * @modify date 2022-05-23 13:30:43
 * @license GPLv3
 * @desc [description]
 */
defined('INDEX_AUTH') OR die('Direct access not allowed!');

// IP based access limitation
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-reporting');
// start the session
require SB . 'admin/default/session.inc.php';
require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO . 'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO . 'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require __DIR__ . DS . '..' . DS . 'helper.php';

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');

if (!$can_read) {
    die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
}

function httpQuery($query = [])
{
    return http_build_query(array_unique(array_merge($_GET, $query)));
}

function makeCache($data)
{
    file_put_contents(SB . 'files/cache/cache_rating_chat.json', json_encode($data));
}

function getCache()
{
    if (file_exists($file = SB . 'files/cache/cache_rating_chat.json'))
    {
        return json_decode(file_get_contents($file), true);
    }
}

function responseJson($data, $header = '')
{
    if ($header === 'cached') 
    {
        $data['cached'] = true;
    }

    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

if (isset($_GET['stat']))
{
    $cache = getCache();
    if (!is_null($cache) && $cache['time'] > strtotime(date('Y-m-d H:i:s'))) responseJson($cache['data'], 'cached');

    $db = \SLiMS\DB::getInstance();
    $day = function($howManyDay) use($db) {
        $data = [
            'star1' => [],
            'star2' => [],
            'star3' => [],
            'star4' => [],
            'star5' => [],
            'day' => []
        ];
        $SQL = <<<SQL
            SELECT 
                (SELECT COUNT(`star`) FROM `biblio_rating_log` WHERE `star` = 1 AND SUBSTRING(`created_at`, 1,10) = ?) AS `score_star_1`,
                (SELECT COUNT(`star`) FROM `biblio_rating_log` WHERE `star` = 2 AND SUBSTRING(`created_at`, 1,10) = ?) AS `score_star_2`,
                (SELECT COUNT(`star`) FROM `biblio_rating_log` WHERE `star` = 3 AND SUBSTRING(`created_at`, 1,10) = ?) AS `score_star_3`,
                (SELECT COUNT(`star`) FROM `biblio_rating_log` WHERE `star` = 4 AND SUBSTRING(`created_at`, 1,10) = ?) AS `score_star_4`,
                (SELECT COUNT(`star`) FROM `biblio_rating_log` WHERE `star` = 5 AND SUBSTRING(`created_at`, 1,10) = ?) AS `score_star_5`
        SQL;

        $state = $db->prepare($SQL);
        for ($i = ($howManyDay - 1); $i >= 0; $i--) { 
            $date = date('Y-m-d', strtotime("-{$i} days"));
            if ($i === 0) $date = date('Y-m-d');
            
            $state->execute([$date,$date,$date,$date,$date]);

            $dataState = $state->fetch(PDO::FETCH_ASSOC);
            
            // Set star
            $data['star1'][] = $dataState['score_star_1'];
            $data['star2'][] = $dataState['score_star_2'];
            $data['star3'][] = $dataState['score_star_3'];
            $data['star4'][] = $dataState['score_star_4'];
            $data['star5'][] = $dataState['score_star_5'];

            // // set day
            $data['day'][] = $date;
        }

        return $data;
    };
    
    $data = $day(7);
    makeCache(['time' => strtotime(date('Y-m-d H:i:s', strtotime('+5 minutes'))), 'data' => $data]);
    responseJson($data);
}
?>
<link rel="stylesheet" href="<?= pluginRatingUrl('assets/css/toastui-chart.min.css') ?>"/>
<div class="menuBox">
    <div class="menuBoxInner printIcon">
        <div class="per_title">
            <h2><?php echo __('Rating Chart'); ?></h2>
        </div>
        <?php
        if (!is_writable(SB . 'files/cache/'))
        {
            exit(<<<HTML
                <div class="errorBox font-weight-bold">Direktori files/cache/ tidak dapat ditulis!</div>
            HTML);
        }
        ?>
        <div class="infoBox font-weight-bold">Data statistik dibawah akan diperbaharui setiap 5 menit sekali.</div>
    </div>
</div>
<div id="chart">

</div>
<script src="<?= pluginRatingUrl('assets/js/toastui-chart.min.js') ?>"></script>
<script>
    (async function(){
        const el = document.getElementById('chart');
        
        try {
            let responseData = await (await fetch('<?= $_SERVER['PHP_SELF'] . '?' . httpQuery(['stat' => 'yes']) ?>')).json()

            const data = {
            categories: responseData.day,
            series: [
                {
                name: 'Bintang 1',
                data: responseData.star1,
                },
                {
                name: 'Bintang 2',
                data: responseData.star2,
                },
                {
                name: 'Bintang 3',
                data: responseData.star3,
                },
                {
                name: 'Bintang 4',
                data: responseData.star4,
                },
                {
                name: 'Bintang 5',
                data: responseData.star5,
                },
            ],
            };
            const options = {
                chart: { title: 'Jumlah Data Perbintang selama 1 pekan', width: '100%', height: 400 },
            };

            toastui.Chart.columnChart({ el, data, options });
        } catch (error) {
            el.innerHTML = '<div class="errorBox">Yah ada yang error : (</div>';
        }
    })()
    
</script>