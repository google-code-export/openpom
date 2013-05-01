<?php
/*
  OpenPOM
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
*/
  
if (basename($_SERVER['SCRIPT_NAME']) != "index.php") die() ;
if (!isset($_SESSION['USER'])) die(); ?>

<?php
/* pagination */
$has_page_prev = $FIRST > 0;
$has_page_next = ($FIRST + $nb_rows) < $total_rows;
$page_from = $FIRST + 1;
$page_to = $FIRST + $nb_rows;
$page_prev = max(0, $FIRST - $LINE_BY_PAGE);
$page_next = $FIRST + $LINE_BY_PAGE;
?>

<form method="get" id="filter">
    <table id="top">
        <tr>

<?php if (!isset($_GET['monitor'])) { ?>

            <td id="top-refresh">
                <div>
                    <span class="icon-btn icon-refesh"
                          title="<?php echo ucfirst(lang($MYLANG, 'refresh'))?>"
                          onclick="window.location.href='<?php echo $MY_GET ?>';"></span>&thinsp;

                    <span title="<?php echo ucfirst(lang($MYLANG, 'refreshing'))?>"
                          id="refresh-countdown"><?php echo $REFRESHTIME; ?></span>
                </div>
            </td>

            <td class="top-separator">
            </td>

            <td id="top-actions">
                <div>
                    <span class="icon-btn icon-ack"
                          onclick="clicked_generic_popin('ack.php');"
                          title="<?php echo ucfirst(lang($MYLANG, 'acknowledge'))?>"></span>&thinsp;

                    <span class="icon-btn icon-down"
                          onclick="clicked_generic_popin('down.php');"
                          title="<?php echo ucfirst(lang($MYLANG, 'downtime'))?>"></span>&thinsp;

                    <span class="icon-btn icon-disable"
                          onclick="clicked_generic_popin('disable.php');"
                          title="<?php echo ucfirst(lang($MYLANG, 'disable_title'))?>"></span>&thinsp;

                    <span class="icon-btn icon-comment"
                          onclick="clicked_generic_popin('comment.php');"
                          title="<?php echo ucfirst(lang($MYLANG, 'comment0'))?>"></span>&thinsp;

                    <span class="icon-btn icon-reset"
                          onclick="clicked_generic_action('reset');"
                          title="<?php echo ucfirst(lang($MYLANG, 'reset_title'))?>"></span>&thinsp;

                    <span class="icon-btn icon-recheck"
                          onclick="clicked_generic_action('recheck');"
                          title="<?php echo ucfirst(lang($MYLANG, 'recheck'))?>"></span>
                </div>
            </td>

            <td class="top-separator">
            </td>

            <td id="top-settings">
                <div>
                    <?php $popin = $POPIN ? 'disa_popin' : 'ena_popin'; ?>
                    <span class="icon-btn icon-<?php echo $popin ?>"
                          title="<?php echo ucfirst(lang($MYLANG, $popin))?>"
                          onclick="window.location.href='<?php echo $MY_GET.'&popin='.(($POPIN+1)%2) ?>';"></span>&thinsp;

                    <span class="icon-btn icon-<?php echo $global_notif ?> icon-bg-<?php echo $global_notif ?>"
                          title="<?php echo ucfirst(lang($MYLANG, $global_notif))?>"
                          onclick="clicked_generic_action('<?php echo $global_notif ?>', false, 'nagios');"></span>&thinsp;

                    <span class="icon-btn icon-nagios"
                          title="<?php echo ucfirst(lang($MYLANG, 'show_log')) ?>"
                          onclick="<?php echo $GLOBAL_LOGS_ONCLICK ?>"></span>&thinsp;

                    <span class="icon-btn icon-monitor"
                          title="<?php echo ucfirst(lang($MYLANG, 'mode'))?>"
                          onclick="window.location.href='?monitor';"></span>&thinsp;

                    <span class="icon-btn icon-options"
                          title="<?php echo ucfirst(lang($MYLANG, 'option'))?>"
                          onclick="popin('option.php', {
                            onOpen: function() { filtering_has_focus = true; },
                            onClosed: function() { filtering_has_focus = false; }
                          });"></span>
                </div>
            </td>

            <td class="top-separator">
            </td>

            <td id="top-level">
                <span id="metter-wrap">
                    <img src="img/metter.png" style="position: absolute; top: 3px; left: 4px;" />
                    <select name="level" onchange="$('form#filter').submit();">
                    <?php
                    for ($l = 1; $l <= $MAXLEVEL; $l++) {
                        if ($l == 8) echo '<optgroup label="&nbsp;"></optgroup>';
                        $selected = ($l == $LEVEL) ? 'selected="selected"' : '';
                        echo "<option value=\"$l\" title=\"".lang($MYLANG, "title$l")."\" $selected>";
                        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                        if ($l < 8) echo "$l)&nbsp;&nbsp;";
                        echo ucfirst(lang($MYLANG, 'level'.$l));
                        echo "</option>";
                    }
                    ?>
                    </select>
                </span>
            </td>

            <td class="top-separator" style="padding-left: 10px; padding-right: 10px;">
            </td>

            <td id="top-filter-input">
                <input type="text"
                       name="filtering"
                       value="<?php echo $FILTER?>"
                       id="filtering"
                       title="<?php echo ucfirst(lang($MYLANG, 'search'))?>" />
            </td>

            <td id="top-filter-buttons">
                <div>
                    <span class="icon-btn icon-vsearch"
                          title="<?php echo ucfirst(lang($MYLANG, 'vsearch'))?>"
                          onclick="$('form#filter').submit();"></span>&thinsp;

                    <span class="icon-btn icon-clear"
                          title="<?php echo ucfirst(lang($MYLANG, 'clear'))?>"
                          onclick="$('#filtering').val(''); $('form#filter').submit();"></span>

                    <?php if (isset($_GET['sort'])) { ?>
                    <input type="hidden" name="sort" value="<?php echo $_GET['sort'] ; ?>" />
                    <?php } ?>

                    <?php if (isset($_GET['order'])) { ?>
                    <input type="hidden" name="order" value="<?php echo $_GET['order'] ; ?>" />
                    <?php } ?>
                </div>
            </td>

            <td class="top-separator">
            </td>

<?php } ?>

<?php if (isset($_GET['monitor'])) { ?>

            <td id="top-monitor-level" title="<?php echo ucfirst(lang($MYLANG, 'title'.$level)) ?>">
                <?php echo "$level) ".ucfirst(lang($MYLANG, 'level'.$level)) ?>
            </td>

            <td id="top-monitor-glob" title="<?php echo lang($MYLANG, 'meter') ?>">
                <img src="img/flag_critical.png" /><span><?php echo $hit_critical."/".$glob_critical ?></span>
                <img src="img/flag_warning.png" /><span><?php echo $hit_warning."/".$glob_warning ?></span>
                <img src="img/flag_unknown.png" /><span><?php echo $hit_unknown."/".$glob_unknown ?></span>
                <img src="img/flag_ok.png" /><span><?php echo $hit_ok."/".$glob_ok ?></span>
                <img src="img/flag_ack.gif" /><span><?php echo $hit_ack."/".$glob_ack ?></span>
                <img src="img/flag_downtime.png" /><span><?php echo $hit_down."/".$glob_down ?></span>
                <img src="img/flag_notify.png" /><span><?php echo $hit_notif."/".$glob_notif ?></span>
                <img src="img/disablecheck.png" /><span><?php echo $hit_check."/".$glob_check ?></span>
            </td>

<?php } ?>

<?php if (!isset($_GET['monitor'])) { ?>

            <td id="top-glob" title="<?php echo lang($MYLANG, 'meter') ?>">
                <span>
                    <table>
                        <tr>
                            <td class="glob-icon"><span><a href="?level=11"><img src="img/flag_critical.png" /></a></span></td>
                            <td class="glob-text"><span><a href="?level=11"><?php echo $hit_critical."/".$glob_critical ?></a></span></td>

                            <td class="glob-icon"><span><a href="?level=12"><img src="img/flag_warning.png" /></a></span></td>
                            <td class="glob-text"><span><a href="?level=12"><?php echo $hit_warning."/".$glob_warning ?></a></span></td>

                            <td class="glob-icon"><span><a href="?level=8"><img src="img/flag_ack.gif" /></a></span></td>
                            <td class="glob-text"><span><a href="?level=8"><?php echo $hit_ack."/".$glob_ack ?></a></span></td>

                            <td class="glob-icon"><span><a href="?level=9"><img src="img/flag_downtime.png" /></a></span></td>
                            <td class="glob-text"><span><a href="?level=9"><?php echo $hit_down."/".$glob_down ?></a></span></td>
                        </tr>
                        <tr>
                            <td class="glob-icon"><span><a href="?level=13"><img src="img/flag_unknown.png" /></a></span></td>
                            <td class="glob-text"><span><a href="?level=13"><?php echo $hit_unknown."/".$glob_unknown ?></a></span></td>

                            <td class="glob-icon"><span><a href="?level=14"><img src="img/flag_ok.png" /></a></span></td>
                            <td class="glob-text"><span><a href="?level=14"><?php echo $hit_ok."/".$glob_ok ?></a></span></td>

                            <td class="glob-icon"><span><a href="?level=10"><img src="img/flag_notify.png" /></a></span></td>
                            <td class="glob-text"><span><a href="?level=10"><?php echo $hit_notif."/".$glob_notif ?></a></span></td>

                            <td class="glob-icon"><span><img src="img/disablecheck.png" /></span></td>
                            <td class="glob-text"><span><?php echo $hit_check."/".$glob_check ?></span></td>
                        </tr>
                    </table>
                </span>
            </td>

            <td class="top-separator">
            </td>

            <td id="top-pagination">
                <div>
                    <?php if ($has_page_prev) { ?>
                    <span class="icon-btn icon-prev"
                          onclick="window.location.href='<?php echo $MY_GET_NO_NEXT?>&prev=<?php echo $page_prev ?>';"
                          title="<?php echo ucfirst(lang($MYLANG, 'prev'))?>"></span>&thinsp;
                    <?php } ?>

                    <span><?php echo "$page_from-$page_to&thinsp;<b>/&thinsp;$total_rows</b>" ?></span>

                    <?php if ($has_page_next) { ?>
                    &thinsp;<span class="icon-btn icon-next"
                          onclick="window.location.href='<?php echo $MY_GET_NO_NEXT?>&next=<?php echo $page_next ?>';"
                          title="<?php echo ucfirst(lang($MYLANG, 'next'))?>"></span>
                    <?php } ?>
                </div>
            </td>
        </tr>

        <tr>
            <td id="top-gradient" colspan="14">
                <div id="white"></div>
                <div id="grad"></div>
            </td>
<?php } ?>

<?php if (isset($_GET['monitor'])) { ?>
            <td id="top-monitor-refresh">
                <?php echo ucfirst(lang($MYLANG, 'refreshing')) ?>
                <span id="refresh-countdown"
                      style="vertical-align: baseline;"><?php echo $REFRESHTIME; ?></span>
                <?php echo lang($MYLANG, 'second') ?>
            </td>

            <td id="top-monitor-stop">
                <a href="index.php"><?php echo ucfirst(lang($MYLANG, 'mode0'))?></a>
            </td>
<?php } ?>

        </tr>
    </table>
</form>

<div id="top-fixed-height"></div>

<?php if ($global_notif == 'ena_notif') { ?>
<!-- blink global notification button -->
<script type="text/javascript">
    blink_button($('.icon-ena_notif'));
</script>
<?php } ?>
