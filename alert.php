<? 
/*
  OpenPom $Revision$
  $HeadURL$
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
 
  $Date$

  Sylvain Choisnard - schoisnard@exosec.fr                                                 
*/

  if (basename($_SERVER['SCRIPT_NAME']) != "index.php") die(); 
  if (!isset($_SESSION['USER'])) die();
?>

    <table width="100%" class="alert" id="alert">
      <tr class="headers">
        <? 
          foreach($COLS AS $key => $val) { 
            if ($key == 'checkbox') {
        ?>
        <th class="checkall">
          <input type="checkbox" onclick='selectall(<?=$nb_rows?>);' />
        </th>
        <? } else { ?>
        <th style="white-space:nowrap;">
          <? if ( ($SORTFIELD == $val) && ($SORTORDERFIELD == "ASC") ) { ?>
          <a class="col_sort_up" href="<?=$MY_GET_NO_SORT?>&sort=<?=$key?>&order=1">
          <? } else if ($SORTFIELD == $val) { ?>
          <a class="col_sort_down" href="<?=$MY_GET_NO_SORT?>&sort=<?=$key?>&order=0">
	  <? } else { ?>
          <a class="col_no_sort" href="<?=$MY_GET_NO_SORT?>&sort=<?=$key?>&order=0">
          <? } ?>
            <?=ucfirst($LANG[$MYLANG][$key])?>
          </a>
        </th>
        <? } } ?>
      </tr>
      <?
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
      ?>
      <tr class="<?=$COLOR?>" id="<?=$data['SVCID']?>" style="font-size:<?=$_SESSION['FONTSIZE']?>px;"
         onmouseover='to = setTimeout("get_data(\"<?=$data['TYPE']?>\", \"<?=$data['SVCID']?>\")",500); 
           this.className="over<?=$soft?>";' 
           onmouseout='clearTimeout(to); hide_data(); this.className="<?=$COLOR?>";'
         onclick='selectline(<?=$line?>);'>
        <? 
          foreach($COLS AS $key => $val) { 

            if ($key == 'checkbox')
              $toprint = '<input id="check_'.$line.'" name="'.$data['SVCID'].'" value="'.$data['MACHINE_NAME'].' '.$data['SERVICES'].'" type="checkbox" onclick=\'selectline('.$line.');\' />';

            else if ($key == 'flag') {

              if ($data['TYPE'] == "svc") 
                $toprint = '<a onclick="selectline('.$line.');" target ="_BLANK" href="'.$NAGIOSLINK.'?type=2&host='.$data["MACHINE_NAME"].'&service='.$data["SERVICES"].'"><img src="img/svc.png" border="0" alt="S" title="'.ucfirst($LANG[$MYLANG]['service']).'" /></a>'; 
              else
                $toprint = '<a onclick="selectline('.$line.');" class="col_no_sort" target ="_BLANK" href="'.$NAGIOSLINK.'?type=1&host='.$data["MACHINE_NAME"].'"><img src="img/host.png" border="0" alt="H" title="'.ucfirst($LANG[$MYLANG]['host']).'" /></a>'; 

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
        <td<?=($key=="flag")?" class=\"".$COLOR."dark\"":""?> style="white-space:nowrap;">
        <? } else { ?>
        <td<?=($key=="checkbox")?" style=\"text-align:center;\" class=\"".$COLOR."dark\"":""?> style="white-space:nowrap;">
        <? } ?>
          <?=$toprint?>
        </td>
        <? } //end foreach ?>
      </tr>
      <?
          $line++;  
        } //end while
      ?>
    </table>
    <? if (isset($_GET['monitor'])) { ?>
    <?=ucfirst($LANG[$MYLANG]['refreshing'])?> <b><span id="refreshspan"></span></b> <?=ucfirst($LANG[$MYLANG]['second'])?>
    <a href="index.php"><?=ucfirst($LANG[$MYLANG]['mode0'])?></a>
    <? } else { ?>
    </form>
    <? } ?>

<? /*
if ($nb_rows > 0) {
mysql_data_seek($rep, 0);
while($data = mysql_fetch_array($rep, MYSQL_ASSOC)) { 
  echo "<pre>";
  print_r($data);
  echo "</pre>";
}
} */
?>
