<?php 
/*
  OpenPOM
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
*/

if (basename($_SERVER['SCRIPT_NAME']) != "index.php") die(); 
if (!isset($_SESSION['USER'])) die();
?>


<style type="text/css">
table#alert * {
    font-size: <?php echo $_SESSION['FONTSIZE'] ?>px;
}
</style>

<table width="100%" id="alert">
    <tr>
<?php foreach ($COLS as $col => $def) { ?>

        <th class="<?php echo $col ?>">
            <?php $def['hfmt']($col, $def); ?>
        </th>

<?php } ?>
    </tr>

<?php if (isset($_GET['monitor']) && $global_notif == 'ena_notif') { ?>
    <!-- extra row in monitor mode when global notifications are disabled -->
    <tr>
        <td id="notif_warning" colspan="<?php echo count($COLS) ?>">
            <div>
                <?php echo lang($MYLANG, 'notif_warning'); ?>
            </div>
            <script type="text/javascript">
                blink_button($('td#notif_warning > div'));
            </script>
        </td>
    </tr>
<?php } ?>

<?php while ($data = mysql_fetch_array($rep, MYSQL_ASSOC)) { ?>
<?php

switch ($data['STATUS']) {
    case 0:     $data['__color_class'] = $OK;       break;
    case 1:     $data['__color_class'] = $WARNING;  break;
    case 2:     $data['__color_class'] = $CRITICAL; break;
    default:    $data['__color_class'] = $UNKNOWN;  break;
}

if ($data['COMMENT'] & ENTRY_COMMENT_TRACK)
    $data['__color_class'] = $data['STATUS'] > 0 ? $TRACK_ERROR : $TRACK_OK;

if ($data['STATETYPE'] == 0)
    $data['__color_class'] .= ' soft';

$data['__action_target'] = 'nagios;'.$data['HOSTNAME'].';'.$data['SERVICE'].';'.$data['CHECKNAME'];
$data['__popin_url'] = "status-nagios.php?arg1=${data['TYPE']}&arg2=${data['STATUSID']}";

?>

    <tr class="alert-item <?php echo $data['__color_class'] ?>"
        id="<?php echo $data['STATUSID'] ?>"
        onmouseover="set_popin('<?php echo $data['__popin_url'] ?>');"
        onmouseout="unset_popin();"
        onclick="selectline(this, event);">

<?php foreach ($COLS as $col => $def) { ?>

        <td class="<?php echo "$col ${data['__color_class']}" ?>">
            <?php $def['rfmt']($col, $def, $data); ?>
        </td>

<?php } /* end foreach column */ ?>

    </tr>

<?php } /* end while mysql fetch array */ ?>
</table>


<?php
/*
mysql_data_seek($rep, 0);
while($data = mysql_fetch_array($rep, MYSQL_ASSOC)) { 
  echo "<pre>";
  print_r($data);
  echo "</pre>";
}
*/
?>
