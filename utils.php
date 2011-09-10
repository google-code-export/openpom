<?php
/*
  OpenPOM
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
*/


function special_char() {
  foreach ($_GET AS $key => $value) {
    $nohtml = htmlspecialchars($value) ;
    $addslashe = addslashes($nohtml) ;
    $_GET[$key] = strip_tags($addslashe) ;
  }
  unset ($key) ;
  unset ($value) ;
  unset ($nohtml) ;
  unset ($addslashe) ;
  foreach ($_POST AS $key => $value) {
    $nohtml = htmlspecialchars($value) ;
    $addslashe = addslashes($nohtml) ;
    $_POST[$key] = strip_tags($addslashe) ;
  }
  unset ($key) ;
  unset ($value) ;
  unset ($nohtml) ;
  unset ($addslashe) ;
}

function printtime($t) {
  $day    = (int)     ( $t / 86400 );
  $hour   = (int)   ( ( $t % 86400 ) / 3600 );
  $minute  = (int) ( ( ( $t % 86400 ) % 3600 ) / 60 );
  $second = (int) ( ( ( $t % 86400 ) % 3600 ) % 60 );
  if ($minute == 0 and $hour == 0 and $day == 0)
    return sprintf("%02ds", $second);
  else if ($hour == 0 and $day == 0)
    return sprintf("%02dm %02ds", $minute, $second);
  else if ($day == 0)
    return sprintf("%02dh %02dm", $hour, $minute);
  else
    return sprintf("%dd %02dh", $day, $hour);
}

function linebreak($t) {
  global $SPLITAT;
  $nbcarmax = $SPLITAT + 1;
  $i = 0; 
  $last_space = -1; 
  $next_break = $nbcarmax;
  $start = 0;
  $out = "";
  $len = strlen($t);
  for ($i=0; $i<$len; $i++) {
    if ($t[$i] == ' ' || $t[$i] == '\t')
      $last_space = $i;
    if ($i == $next_break) {
      if ($last_space == -1) {
        $out .= substr($t, $start, $i - $start);
        $next_break = $i + $nbcarmax;
        $start = $i;
      }
      else {
        $out .= substr($t, $start, $last_space - $start);
        $next_break = $last_space + $nbcarmax;
        $start = $last_space;
      }
      $last_space = -1;
      $out .= "<br />";
    }
  }
  $out .= substr($t, $start);
  return $out;
}

function select_level($LEVEL) {
  global $MY_HOSTFILT;
  global $MY_SVCFILT;
  global $MY_HOSTACKLIST;
  global $MY_SVCACKLIST;
  global $MY_HOSTDOWNLIST;
  global $MY_SVCDOWNLIST;
  global $MY_ACKLIST;
  global $MY_DISABLE;
  global $MY_NOSVC;
  global $MY_SOFT;
  if ($LEVEL == 1) { 
    $MY_SVCFILT       = "2";
    $MY_SOFT          = "1";
  }
  else if ($LEVEL == 2) { 
    $MY_SOFT          = "1";
  }
  else if ($LEVEL == 3) {
    $MY_SOFT          = "0";
  }
  else if ($LEVEL == 4) {
    $MY_HOSTACKLIST   = "0,1";
    $MY_SVCACKLIST    = "0,1";
    $MY_HOSTDOWNLIST  = "0,1";
    $MY_SVCDOWNLIST   = "0,1";
    $MY_DISABLE       = "0,1";
  }
  else if ($LEVEL == 5) {
    $MY_HOSTFILT      = "1,2";
    $MY_HOSTACKLIST   = "0,1";
    $MY_SVCACKLIST    = "0,1";
    $MY_HOSTDOWNLIST  = "0,1";
    $MY_SVCDOWNLIST   = "0,1";
    $MY_DISABLE       = "0,1";
  }
  else if ($LEVEL == 6) {
    $MY_HOSTFILT      = "1,2";
    $MY_HOSTACKLIST   = "0,1";
    $MY_SVCACKLIST    = "0,1";
    $MY_HOSTDOWNLIST  = "0,1";
    $MY_SVCDOWNLIST   = "0,1";
    $MY_ACKLIST       = "0,1";
    $MY_NOSVC         = "0";
    $MY_DISABLE       = "0,1";
  }
  else if ($LEVEL == 7) {
    $MY_SVCFILT       = "0,1,2,3";
    $MY_HOSTFILT      = "0,1,2";
    $MY_HOSTACKLIST   = "0,1";
    $MY_SVCACKLIST    = "0,1";
    $MY_HOSTDOWNLIST  = "0,1";
    $MY_SVCDOWNLIST   = "0,1";
    $MY_ACKLIST       = "0,1";
    $MY_NOSVC         = "0";
    $MY_DISABLE       = "0,1";
  }
}

function post_data_to_cmd($dbconn) {
  if (isset($_POST['down'])) {
    if ( (isset($_POST['start'])) && (isset($_POST['end'])) ) {
      $pat = '/[0-9]{1,2}[-]{1}[0-9]{1,2}[-]{1}[0-9]{4} [0-9]{1,2}:[0-9]{1,2}/';
      if ( (preg_match($pat, $_POST['start'])) && (preg_match($pat, $_POST['end'])) ) {
        $start = strtotime($_POST['start']);
        $end = strtotime($_POST['end']);
      }
    }
    if ( (isset($_POST['hour'])) || (isset($_POST['minute'])) ) {
      $endf = 0;
      if (is_numeric($_POST['hour'])) { 
        $start = time();
        $endf = $start + ($_POST['hour'] * 3600) ;
      }
      if (is_numeric($_POST['minute'])) {
        $start = time();
        if ($endf != 0)
          $endf = $endf + ($_POST['minute'] * 60) ;
        else
          $endf = $start + ($_POST['minute'] * 60) ;
      }
    }
    if ($endf != 0) $end = $endf;
    if ( (!isset($start)) || (!isset($end)) ) return 2;
  }

  global $ILLEGAL_CHAR;
  if ( (isset($_POST['ack']))  || 
       (isset($_POST['down'])) ||
       (isset($_POST['comment_persistent'])) ) {
    if ( (isset($_POST['comment'])) && (!empty($_POST['comment'])) ) {
      foreach(str_split($ILLEGAL_CHAR) AS $char) {
        $pos = strpos($_POST['comment'], $char);
        if ( ($pos === 0) || ($pos > 0) )
          return 2;
      }//end foreach
      $comment = $_POST['comment'];
      if (isset($_POST['track']))
        $comment = "!track ".$comment;
    }
    else
      return 2;
  }

  /* get action */
  foreach ($_POST AS $key => $val) 
    if (preg_match('/^(ack|down|reset|disable|recheck|comment_persistent|ena_notif|disa_notif)$/',$key))
      break; 

  if (!isset($key)) return 2;
  global $EXT_CMD;
  if (!array_key_exists($key, $EXT_CMD))
    return 2;

  $cmds = array();
  $cmd = "";
  $now = time();
  $next = ($now + 15);
  foreach ($_POST AS $name => $value) {
    if (!is_numeric($name)) 
      continue;
    $host_svc = explode(" ",$value, 2);
    if ( (empty($host_svc[0])) || (empty($host_svc[1])) || (count($host_svc) != 2) )
      continue;

    if ($host_svc[1] == "--host--") 
      foreach($EXT_CMD[$key]['host'] AS $n => $array )
        $cmd .= "[$now] ".implode(';', $array)."\\n";
    else
      foreach($EXT_CMD[$key]['svc'] AS $n => $array) 
        $cmd .= "[$now] ".implode(';', $array)."\\n";

    if ($key == 'reset') {
      global $BACKEND ;
      require_once("query-downtime.php");
      if ($host_svc[1] == "--host--") 
        $query = str_replace('define_mhost', $host_svc[0], $QUERY_DOWNTIME_HOST_ID);
      else {
        $query = str_replace('define_mhost', $host_svc[0], $QUERY_DOWNTIME_SVC_ID);
        $query = str_replace('define_msvc', $host_svc[1], $query);
      }
      if (!($rep_down = mysql_query($query, $dbconn))) 
        return 2;
      if (mysql_num_rows($rep_down) == 1) {
        $row = mysql_fetch_row($rep_down);
        $cmd = str_replace('$downtime_id', $row[2], $cmd);
      }
      else {
        $cmd = str_replace("[$now] DEL_HOST_DOWNTIME;", '', $cmd);
        $cmd = str_replace("[$now] DEL_SVC_DOWNTIME;", '', $cmd);
      }
      mysql_free_result($rep_down);
    }

    $cmd = str_replace('$host', $host_svc[0], $cmd);
    $cmd = str_replace('$svc', $host_svc[1], $cmd);
    $cmd = str_replace('$user', $_SESSION['USER'], $cmd);
    $cmd = str_replace('$next', $next, $cmd);
    $cmd = str_replace('$now', $now, $cmd);
    if (isset($comment))
      $cmd = str_replace('$comment', $comment, $cmd);
    if (isset($start)) {
      $cmd = str_replace('$start_time', $start, $cmd);
      $cmd = str_replace('$end_time', $end, $cmd);
    }
    
  }//end foreach

  if ( ($key == "disa_notif") || ($key == "ena_notif") ) {
    $cmd = "[$now] ".$EXT_CMD[$key]['host'][0][0]."\\n";
  }

  global $CMD_FILE;
  global $EXEC_CMD;
  global $EXEC_PARAM;
  global $SUDO_EXEC;
  global $SUDO_PARAM;
  if (!empty($cmd)) {
    if (isset($SUDO_EXEC)) 
      exec("\"$SUDO_EXEC\" $SUDO_PARAM \"$EXEC_CMD\" $EXEC_PARAM \"$CMD_FILE\" \"$cmd\" &");
    else
      exec("\"$EXEC_CMD\" $EXEC_PARAM \"$CMD_FILE\" \"$cmd\" &");
    unset($cmd);
  }
  return 0;
}

function getmicrotime(){
  list($usec, $sec) = explode(" ",microtime());
  return ((float)$usec + (float)$sec);
} 

function get_graph($type, $host, $svc = null) {
  if ($type == 'status') {
    $type = 'GRAPH';
  } else if ($type == 'popup') {
    $type = 'GRAPH_POPUP';
  } else {
    return null;
  }

  if (empty($svc) || $svc == '--host--') {
    $type .= '_HOST';
  } else {
    $type .= '_SVC';
  }

  global $$type;
  $type = $$type;
  $type = str_replace('@@define_host@@', $host, $type);
  $type = str_replace('@@define_service@@', $svc, $type);
  return $type;
}

?>
