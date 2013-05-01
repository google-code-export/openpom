<?php
/*
  OpenPOM
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
*/
  
  if (basename($_SERVER['SCRIPT_NAME']) != "index.php") die() ; 
  if (!isset($_SESSION['USER'])) die(); ?>

    <table id="top">
      <tr>

  <?php if (!isset($_GET['monitor'])) { ?>

      <td style="padding-left: 4px; width: 60px;">
        <nobr>
          <span class="icon-btn icon-refesh"
                title="<?php echo ucfirst(lang($MYLANG, 'refresh'))?>"
                onclick="window.location.href='<?php echo $MY_GET ?>';"></span>&#160;
          
          <span title="<?php echo ucfirst(lang($MYLANG, 'refreshing'))?>" 
                id="refreshspan"><?php echo $REFRESHTIME; ?></span>&#160;&#160;
        </nobr>
      </td>

      <td style="text-align: center;">
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

          <span class="icon-btn icon-disablecheck"
                onclick="clicked_generic_popin('disablecheck.php');"
                title="<?php echo ucfirst(lang($MYLANG, 'disablecheck'))?>"></span>&thinsp;

          <span class="icon-btn icon-reset"
                onclick="clicked_generic_action('reset');"
                title="<?php echo ucfirst(lang($MYLANG, 'reset_title'))?>"></span>&thinsp;

          <span class="icon-btn icon-recheck"
                onclick="clicked_generic_action('recheck');"
                title="<?php echo ucfirst(lang($MYLANG, 'recheck'))?>"></span>
                
      </td>
      
      <td style="padding-left: 10px; padding-right: 10px;">
      </td>
      
      <td style="text-align: center;">
        <?php
          $popin = $POPIN ? 'disa_popin' : 'ena_popin';
        ?>
          <span class="icon-btn icon-<?php echo $popin ?>"
              title="<?php echo ucfirst(lang($MYLANG, $popin))?>"
              onclick="window.location.href='<?php echo $MY_GET.'&popin='.(($POPIN+1)%2) ?>';"></span>&thinsp;
                
          <span class="icon-btn icon-<?php echo $global_notif ?> icon-bg-<?php echo $global_notif ?>"
                title="<?php echo ucfirst(lang($MYLANG, $global_notif))?>"
                onclick="clicked_generic_action('<?php echo $global_notif ?>', false, 'nagios');"></span>&thinsp;
          
          <?php if ($global_notif == 'ena_notif') { ?>
          <!-- blink global notification button -->
          <script type="text/javascript">
              blink_button($('.icon-ena_notif'));
          </script>
          <?php } ?>
                
          <span class="icon-btn icon-nagios"
                title="<?php echo ucfirst(lang($MYLANG, 'show_log')) ?>"
                onclick="<?php echo $GLOBAL_LOGS_ONCLICK ?>"></span>&thinsp;
          
          <span class="icon-btn icon-monitor"
                title="<?php echo ucfirst(lang($MYLANG, 'mode'))?>"
                onclick="window.location.href='?monitor';"></span>&thinsp;
               
          <span class="icon-btn icon-options"
                onclick="popin('option.php', { 
                  onOpen: function() { filtering_has_focus = true; }, 
                  onClosed: function() { filtering_has_focus = false; }
                });"
                title="<?php echo ucfirst(lang($MYLANG, 'option'))?>"></span>
        </td>
        
        <td style="padding-left: 10px; padding-right: 10px;">
        </td>
        
        <td class="filters" style="text-align: center;">
          <form method="get" class="filt" name="filt" action="?filter=1" id="filt">
            
            <span id="metter-wrap">
              <img src="img/metter.png" style="position: absolute; top: 3px; left: 4px;" />
              <select name="level" onChange="this.form.submit();">
                <?php for ($sub_level=1; $sub_level <= $MAXLEVEL; $sub_level++) { ?>
                <?php if ($sub_level == 8) { ?>
                <optgroup label="&nbsp;&nbsp;&nbsp;---------------------------------">
                <?php } ?>
                  <option value="<?php echo $sub_level?>" title="<?php echo lang($MYLANG, 'title'.$sub_level) ?>"
                  <?php echo ($sub_level==$LEVEL)?"selected":""?>>
                  &#160;&#160;&#160;&#160;&#160; <?php if ($sub_level < 8) echo $sub_level.")&nbsp;" ; ?>
                  <?php echo ucfirst(lang($MYLANG, 'level'.$sub_level))?>
                  </option>
                <?php } ?>
                </optgroup>
              </select>
            </span>&thinsp;
            
            <input type="text" 
                   name="filtering" 
                   value="<?php echo $FILTER?>" 
                   id="filtering" 
                   title="<?php echo ucfirst(lang($MYLANG, 'search'))?>" />

            <span class="icon-btn icon-vsearch" 
                  title="<?php echo ucfirst(lang($MYLANG, 'vsearch'))?>"
                  onclick="$('#filt').submit();">
            </span>&nbsp;<span class="icon-btn icon-clear" 
                  title="<?php echo ucfirst(lang($MYLANG, 'clear'))?>"
                  onclick="$('#filtering').val(''); $('#filt').submit();"></span>

           <?php if (isset($_GET['sort'])) { ?>
           <input type="hidden" name="sort" value="<?php echo $_GET['sort'] ; ?>" />
           <?php } ?>

           <?php if (isset($_GET['order'])) { ?>
           <input type="hidden" name="order" value="<?php echo $_GET['order'] ; ?>" />
           <?php } ?>
            
          </form>
        </td>

        <td style="padding-left: 10px; padding-right: 10px;">
        </td>
        
  <?php } ?>

    <?php if (isset($_GET['monitor'])) { ?>

        <td style="text-align: left; cursor: default;" title="<?php echo ucfirst(lang($MYLANG, 'title'.$level)) ?>">
          <?php echo " &nbsp;".$level.") ".ucfirst(lang($MYLANG, 'level'.$level)) ?>
        </td>

        <td style="text-align: center; cursor: default;" title="<?php echo lang($MYLANG, 'meter') ?>" id="glob-monitor">
            <img src="img/flag_critical.png" /><?php echo $hit_critical."/".$glob_critical ?>
            <img src="img/flag_warning.png" /><?php echo $hit_warning."/".$glob_warning ?>
            <img src="img/flag_unknown.png" /><?php echo $hit_unknown."/".$glob_unknown ?>
            <img src="img/flag_ok.png" /><?php echo $hit_ok."/".$glob_ok ?>
            <img src="img/flag_ack.gif" /><?php echo $hit_ack."/".$glob_ack ?>
            <img src="img/flag_downtime.png" /><?php echo $hit_down."/".$glob_down ?>
            <img src="img/flag_notify.png" /><?php echo $hit_notif."/".$glob_notif ?>
            <img src="img/disablecheck.png" /><?php echo $hit_check."/".$glob_check ?>
        </td>

  <?php } ?>

  <?php if (!isset($_GET['monitor'])) { ?>

        <td style="text-align: center; cursor: default;" title="<?php echo lang($MYLANG, 'meter') ?>">
            <table id="glob">
                <tr>

                    <td class="glob-icon"><span><a href="?level=11"><img src="img/flag_critical.png" /></a></span></td>
                    <td><span><a href="?level=11"><?php echo $hit_critical."/".$glob_critical ?></a></span></td>

                    <td class="glob-icon"><span><a href="?level=12"><img src="img/flag_warning.png" /></a></span></td>
                    <td><span><a href="?level=12"><?php echo $hit_warning."/".$glob_warning ?></a></span></td>

                    <td class="glob-icon"><span><a href="?level=8"><img src="img/flag_ack.gif" /></a></span></td>
                    <td><span><a href="?level=8"><?php echo $hit_ack."/".$glob_ack ?></a></span></td>

                    <td class="glob-icon"><span><a href="?level=9"><img src="img/flag_downtime.png" /></a></span></td>
                    <td><span><a href="?level=9"><?php echo $hit_down."/".$glob_down ?></a></span></td>

                </tr>
                <tr>

                    <td class="glob-icon"><span><a href="?level=13"><img src="img/flag_unknown.png" /></a></span></td>
                    <td><span><a href="?level=13"><?php echo $hit_unknown."/".$glob_unknown ?></a></span></td>

                    <td class="glob-icon"><span><a href="?level=14"><img src="img/flag_ok.png" /></a></span></td>
                    <td><span><a href="?level=14"><?php echo $hit_ok."/".$glob_ok ?></a></span></td>

                    <td class="glob-icon"><span><a href="?level=10"><img src="img/flag_notify.png" /></a></span></td>
                    <td><span><a href="?level=10"><?php echo $hit_notif."/".$glob_notif ?></a></span></td>

                    <td class="glob-icon"><span><img src="img/disablecheck.png" /></span></td>
                    <td><span><?php echo $hit_check."/".$glob_check ?></span></td>

              </tr>
            </table>
        </td>

        <td style="padding-left: 10px; padding-right: 10px;">
        </td>

        <td style="text-align: right; padding-right: 4px;">
          <?php if ($FIRST >= $LINE_BY_PAGE) { ?>
            <span class="icon-btn icon-prev"
                  onclick="window.location.href='<?php echo $MY_GET_NO_NEXT?>&prev=<?php echo $FIRST-$LINE_BY_PAGE?>';"
                  title="<?php echo ucfirst(lang($MYLANG, 'prev'))?>"></span>
          <?php } ?>
          
          <span><?php echo $FIRST."-".($FIRST+$nb_rows)."&thinsp;<b>/&thinsp;".$total_rows."</b>" ?></span>
          
          <?php if ($nb_rows >= $LINE_BY_PAGE) { ?>
            <span class="icon-btn icon-next"
                  onclick="window.location.href='<?php echo $MY_GET_NO_NEXT?>&next=<?php echo $FIRST+$LINE_BY_PAGE?>';"
                  title="<?php echo ucfirst(lang($MYLANG, 'next'))?>"></span>
          <?php } ?>
        </td>       

      </tr>
      <tr>
        <td colspan="10" id="wgrad">
          <div id="white"></div>
          <div id="grad"></div>
        </td>

  <?php } if (isset($_GET['monitor'])) { ?>
        <td style="text-align: center; cursor: default;">
          <?php echo ucfirst(lang($MYLANG, 'refreshing')) ?>
          <span id="refreshspan" style="vertical-align: baseline;"><?php echo $REFRESHTIME; ?></span>&#160;<?php echo lang($MYLANG, 'second') ?>
        </td>
        <td style="text-align: right; cursor: default;">
          <a href="index.php"><?php echo ucfirst(lang($MYLANG, 'mode0'))?></a> &nbsp;
        </td>
  <?php } ?>

      </tr>
    </table>

    <div id="top-fixed-tpad"></div>

