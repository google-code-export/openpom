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

    <table width="100%" class="alert" id="alert">
      <tr class="headers">
        <?php 
          foreach($COLS AS $key => $val) { 
            if ($key == 'checkbox') {
        ?>
        <th class="checkall">
          <input type="checkbox" onclick='selectall(<?php echo $nb_rows?>);' />
        </th>
        <?php } else { ?>
        <th style="white-space:nowrap;">
          <?php if ( ($SORTFIELD == $val) && ($SORTORDERFIELD == "ASC") ) { ?>
          <a class="col_sort_up" href="<?php echo $MY_GET_NO_SORT?>&sort=<?php echo $key?>&order=1">
          <?php } else if ($SORTFIELD == $val) { ?>
          <a class="col_sort_down" href="<?php echo $MY_GET_NO_SORT?>&sort=<?php echo $key?>&order=0">
	  <?php } else { ?>
          <a class="col_no_sort" href="<?php echo $MY_GET_NO_SORT?>&sort=<?php echo $key?>&order=0">
          <?php } ?>
            <?php echo ucfirst($LANG[$MYLANG][$key])?>
          </a>
        </th>
        <?php } } ?>
      </tr>
      <?php
        while($data = mysql_fetch_array($rep, MYSQL_ASSOC)) { 

          if ($data['SVCST'] == 0) { $COLOR = "soft"; $soft= "soft"; }
          else { $COLOR = ""; $soft = ""; }
          switch($data['STATUS']) {
            case 0: $COLOR = $OK; 
                    break; 
            case 1: $COLOR = $WARNING.$COLOR; 
                    break;
            case 2: $COLOR = $CRITICAL.$COLOR; 
                    break;
            case 3: $COLOR = $UNKNOWN.$COLOR; 
                    break;
	  }
          if ( ($data['ACK'] == "1") && ($LEVEL < 3) ) { 
             $COLOR = $TRACK;
          }
      ?>
      <tr class="<?php echo $COLOR?>" id="<?php echo $data['SVCID']?>" style="font-size:<?php echo $_SESSION['FONTSIZE']?>px;"
         onmouseover='to = setTimeout("get_data(\"<?php echo $data['TYPE']?>\", \"<?php echo $data['SVCID']?>\")",500); 
           this.className="over<?php echo $soft?>";' 
           onmouseout='clearTimeout(to); hide_data(); this.className="<?php echo $COLOR?>";'
         onclick='selectline(<?php echo $line?>);'>
        <?php 
          foreach($COLS AS $key => $val) { 

            if ($key == 'checkbox')
              $toprint = '<input id="check_'.$line.'" name="'.$data['SVCID'].'" value="'.$data['MACHINE_NAME'].' '.$data['SERVICES'].'" type="checkbox" onclick=\'selectline('.$line.');\' />';

            else if ($key == 'flag') {

              if ($data['TYPE'] == "svc") 
                $toprint = '<a onclick="selectline('.$line.');" target ="_BLANK" href="'.$LINK.'?type=2&host='.$data["MACHINE_NAME"].'&service='.$data["SERVICES"].'"><img src="img/svc.png" border="0" alt="S" title="'.ucfirst($LANG[$MYLANG]['service']).'" /></a>'; 
              else
                $toprint = '<a onclick="selectline('.$line.');" class="col_no_sort" target ="_BLANK" href="'.$LINK.'?type=1&host='.$data["MACHINE_NAME"].'"><img src="img/host.png" border="0" alt="H" title="'.ucfirst($LANG[$MYLANG]['host']).'" /></a>'; 

              if ($data['ACK'] == "1") 
                $toprint = $toprint.'<img src="img/ack.gif" alt="A" title="'.ucfirst($LANG[$MYLANG]['acknowledge']).'" />';

              if ($data['NOTIF'] == "0")
                $toprint = $toprint.'<img src="img/notify.gif" alt="N" />';

              if ($data['DOWNTIME'] == "1") 
                $toprint = $toprint.'<img src="img/downtime.gif" alt="D" title="'.ucfirst($LANG[$MYLANG]['downtime']).'" />';

              if ($data['COMMENT'] > 0)
                $toprint = $toprint.'<img src="img/comment.gif" alt="C" />';

            }
            else if ( ($key == 'duration') || ($key == 'last') ) 
              $toprint = printtime($data[$val]);
            else if (strlen($data[$val]) > $MAX_LEN_TD) 
              $toprint = htmlspecialchars(substr($data[$val], 0, $MAX_LEN_TD))." ...";
            else
              $toprint = htmlspecialchars($data[$val]);
            if ( ($key == "machine") || ($key == "service") )
              $toprint = '<a href="'.$MY_GET_NO_FILT.'&filter=1&filtering='.$data[$val].'">'.$toprint.'</a>';
            else if ($key == "group") {
              $groups = explode(', ',$toprint);
              $my_groups = "";
              foreach ($groups AS $group) {
                if (substr($group, -4) == " ...") 
                  $my_groups .= " ...";
                else
                  $my_groups .= '<a href="'.$MY_GET_NO_FILT.'&filter=1&filtering='.$group.'">'.$group.'</a> ';
              }
              $toprint = $my_groups;
            }
          if (isset($_GET['monitor'])) {
        ?>
        <td<?php echo ($key=="flag")?" class=\"".$COLOR."dark\"":""?> style="white-space:nowrap;">
        <?php } else { ?>
        <td<?php echo ($key=="checkbox")?" style=\"text-align:center;\" class=\"".$COLOR."dark\"":""?> style="white-space:nowrap;">
        <?php } ?>
          <?php echo $toprint?>
        </td>
        <?php } //end foreach ?>
      </tr>
      <?php
          $line++;  
        } //end while
      ?>
    </table>
    <?php if (isset($_GET['monitor'])) { ?>
    <?php echo ucfirst($LANG[$MYLANG]['refreshing'])?> <b><span id="refreshspan"></span></b> <?php echo ucfirst($LANG[$MYLANG]['second'])?>
    <a href="index.php"><?php echo ucfirst($LANG[$MYLANG]['mode0'])?></a>
    <?php } else { ?>
    </form>
    <?php } ?>

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
