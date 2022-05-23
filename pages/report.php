<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-05-23 09:29:29
 * @modify date 2022-05-23 14:05:50
 * @license GPLv3
 * @desc [description]
 */

defined('INDEX_AUTH') OR die('Direct access not allowed!');

// IP based access limitation
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');
// start the session
require SB . 'admin/default/session.inc.php';
require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO . 'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO . 'simbio_DB/datagrid/simbio_dbgrid.inc.php';

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');

if (!$can_read) {
    die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
}

function httpQuery($query = [])
{
    return http_build_query(array_unique(array_merge($_GET, $query)));
}

?>
<div class="menuBox">
    <div class="menuBoxInner printIcon">
        <div class="per_title">
            <h2><?php echo __('Rating Report'); ?></h2>
        </div>
        <div class="sub_section">
            <form name="read_counter" action="<?= $_SERVER['PHP_SELF'] . '?' . httpQuery() ?>" id="search" method="get" class="form-inline"><?php echo __('Title'); ?>&nbsp;:&nbsp;
                <input type="text" name="title" class="form-control col-md-3" autocomplete="off"/>
                <select name="star" class="form-control">
                    <option value="0">Urutkan berdasarkan bintang</option>
                    <?php 
                    for ($i=1; $i <= 5; $i++) { 
                        echo "<option value=\"{$i}\">Bintang {$i}</option>";
                    }
                    ?>
                </select>
                <input type="submit" value="<?php echo __('Search'); ?>"class="s-btn btn btn-success"/>
            </form>
        </div>
    </div>
</div>

<?php
$grid = new simbio_datagrid('class="table table-striped"');
$grid->setSQLColumn("title AS '" . __('Title') . "'", "`1` AS `Bintang 1`","`2` AS `Bintang 2`","`3` AS `Bintang 3`","`4` AS `Bintang 4`","`5` AS `Bintang 5`");
$grid->setSQLorder('`5` DESC');

$criteria = '';
if (isset($_GET['title']) && !empty($_GET['title']))
{
    $title = $dbs->escape_string(str_replace(['\'', '"'], '', strip_tags($_GET['title'])));
    $criteria = 'title LIKE \'%'.$title.'%\'';
}

if (isset($_GET['star']) && $_GET['star'] > 0)
{
    $star = (int)$_GET['star'];
    $grid->setSQLorder("`{$star}` DESC");
}

$grid->setSQLcriteria($criteria);

echo $grid->createDataGrid($dbs, 'biblio_rating INNER JOIN biblio ON biblio.biblio_id = biblio_rating.biblio_id');