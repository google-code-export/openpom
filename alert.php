<?php 
/*
  OpenPom $Revision$
  $HeadURL$
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
 
  $Date$
*/

  if (basename($_SERVER['SCRIPT_NAME']) != "index.php") die(); 
  if (!isset($_SESSION['USER'])) die();
?>



    <style type="text/css">
      table#alert * {
        font-size: <?= $_SESSION['FONTSIZE'] ?>px;
      }
    </style>
    
    
    <?php
      if (!isset($_GET['monitor'])) {
        echo '<div style="height: 39px; width: 1px;"></div>';
      } 
    ?>
    
    <table width="100%" id="alert">
      <tr>
        <?php 
          foreach($COLS AS $key => $val) { 
            if ($key == 'checkbox') {
        ?>
        <th class="checkall">
          <span class="checkbox" onclick="selectall(this);">
            <span></span>
          </span>
        </th>
        <?php } else { ?>
          <th <?= !isset($_GET['monitor']) && $key == 'flag' ? 'style="padding-left: 5px;"' : '' ?>>
          <?php if ( ($SORTFIELD == $val) && ($SORTORDERFIELD == "ASC") ) { ?>
          <a class="col_sort_up" href="<?php echo $MY_GET_NO_SORT?>&sort=<?php echo $key?>&order=1">
          <?php } else if ($SORTFIELD == $val) { ?>
          <a class="col_sort_down" href="<?php echo $MY_GET_NO_SORT?>&sort=<?php echo $key?>&order=0">
	  <?php } else { ?>
          <a class="col_no_sort" href="<?php echo $MY_GET_NO_SORT?>&sort=<?php echo $key?>&order=0">
          <?php } ?>
            <?php echo ucfirst(lang($MYLANG, $key))?>
          </a>
        </th>
        <?php } } ?>
      </tr>
      <?php
        while($data = mysql_fetch_array($rep, MYSQL_ASSOC)) { 
          switch($data['STATUS']) {
            case 0: $COLOR = $OK; 
                    break; 
            case 1: $COLOR = $WARNING; 
                    break;
            case 2: $COLOR = $CRITICAL; 
                    break;
            case 3: $COLOR = $UNKNOWN; 
                    break;
          }
          
          if ( ($data['ACK'] == "1") && ($LEVEL < 4) ) { 
            $COLOR = $TRACK;
          }
          if ($data['SVCST'] == 0) { 
            $COLOR .= " soft"; 
          }
      ?>
      <tr class="alert-item <?php echo $COLOR?>" id="<?php echo $data['SVCID']?>"
        <? if ($POPIN) { ?>
          onmouseover="to = setTimeout(function() { get_data('<?= $data['TYPE'] ?>', '<?= $data['SVCID'] ?>'); }, 500);" 
          onmouseout="clearTimeout(to);"
        <? } ?>
          onclick="selectline(this, event);">
        <?php 
          foreach($COLS AS $key => $val) { 

            if ($key == 'checkbox')
              $toprint = '
                <span class="checkbox">
                  <input type="hidden" 
                         class="data"
                         name="'.$data['SVCID'].'" 
                         value="'.$data['MACHINE_NAME'].' '.$data['SERVICES'].'" />
                  <span></span>
                </span>';
              
            else if ($key == 'flag') {

              if ($data['TYPE'] == "svc") 
                $toprint = '<a target="_blank" href="'.$LINK.'?type=2&host='.$data["MACHINE_NAME"].'&service='.$data["SERVICES"].'"><img src="img/flag_svc.png" border="0" alt="S" title="'.ucfirst(lang($MYLANG, 'service')).'" /></a>'; 
              else
                $toprint = '<a class="col_no_sort" target="_blank" href="'.$LINK.'?type=1&host='.$data["MACHINE_NAME"].'"><img src="img/flag_host.png" border="0" alt="H" title="'.ucfirst(lang($MYLANG, 'host')).'" /></a>'; 
              
              $g = get_graph('popup', $data['MACHINE_NAME'], $data['SERVICES']);
              if (!is_null($g)) {
                $toprint .= '<a href="#" onClick="return pop(\''.$g.'\', \''.$data['SVCID'].'\', 800, 400);"><img src="img/flag_graph.png" alt="G" border="0" title="'.ucfirst(lang($MYLANG, 'graph_icon')).'" /></a>';
              }

              if ($data['ACK'] == "1") 
                $toprint = $toprint.'<img src="img/flag_ack.gif" alt="A" title="'.ucfirst(lang($MYLANG, 'acknowledge')).'" />';

              if ($data['NOTIF'] == "0")
                $toprint = $toprint.'<img src="img/flag_notify.png" alt="N" title="'.ucfirst(lang($MYLANG, 'disa_notif')).'" />';

              if ($data['DOWNTIME'] == "1") 
                $toprint = $toprint.'<img src="img/flag_downtime.png" alt="D" title="'.ucfirst(lang($MYLANG, 'downtime')).'" />';

              if ($data['COMMENT'] > 0)
                $toprint = $toprint.'<img src="img/flag_comment.gif" alt="C" title="'.ucfirst(lang($MYLANG, 'comment')).'" />';

            }
            else if ( ($key == 'duration') || ($key == 'last') ) 
              $toprint = printtime($data[$val]);
            else if (strlen($data[$val]) > $MAX_LEN_TD) 
              $toprint = htmlspecialchars(substr($data[$val], 0, $MAX_LEN_TD))." ...";
            else
              $toprint = htmlspecialchars($data[$val]);
            if ( ($key == "machine") || ($key == "service") )
              $toprint = '<a href="'.$MY_GET_NO_FILT.'&filtering='.$data[$val].'">'.$toprint.'</a>';
            else if ($key == "group") {
              $groups = explode(', ',$toprint);
              $my_groups = "";
              foreach ($groups AS $group) {
                if (substr($group, -4) == " ...") 
                  $my_groups .= " ...";
                else
                  $my_groups .= '<a href="'.$MY_GET_NO_FILT.'&filtering='.$group.'">'.$group.'</a> ';
              }
              $toprint = $my_groups;
            }
          if (isset($_GET['monitor'])) {
        ?>
        <td<?php echo ($key=="flag" ? " class=\"".$COLOR." dark\" style=\"text-align: left;\"":"")?>>
        <?php } else { ?>
        <td<?php echo ($key=="checkbox")?" class=\"".$COLOR." dark\"": ($key=="flag" ? " style=\"padding-left: 5px; text-align: left;\"":"")?>>
        <?php } ?>
          <? if ($key != 'checkbox') $toprint = '<span>'.$toprint.'</span>'; ?> 
          <?= $toprint ?>
        </td>
        <?php } //end foreach 
        ?>
      </tr>
      <?php
          $line++;  
        } //end while
      ?>
    </table>


<?php /*
if ($nb_rows > 0) {
mysql_data_seek($rep, 0);
while($data = mysql_fetch_array($rep, MYSQL_ASSOC)) { 
  echo "<pre>";
  print_r($data);
  echo "</pre>";
}
} */
?>
