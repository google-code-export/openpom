<?php
/*
  OpenPOM
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
*/

/* special_char functions
 * fix data, in particular if magic_quotes is on
 * 
 * we choose here to replace special html characters by their html  
 * entities in both keys and values
 */
function special_char_array(&$in) {
  $keys = array_keys($in);
  
  foreach ($keys as $k) {
    $v = $in[$k];
    unset($in[$k]);
    
    $k = htmlspecialchars($k);
    if (get_magic_quotes_gpc()) {
      $k = stripslashes($k);
    }
    
    if (is_array($v)) {
      special_char_array($v);
      
    } else {
      $v = htmlspecialchars($v);
      if (get_magic_quotes_gpc()) {
        $v = stripslashes($v);
      }
    }
    
    $in[$k] = $v;
  }
  reset($in);
}

function special_char() {
  special_char_array($_GET);
  special_char_array($_POST);
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
  global $MY_HOSTDOWNOP;
  global $MY_HOSTDOWNVAL;
  global $MY_SVCDOWNOP;
  global $MY_SVCDOWNVAL;
  global $MY_ACKLISTOP;
  global $MY_ACKLISTVAL;
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
    $MY_HOSTDOWNOP    = '>=';
    $MY_HOSTDOWNVAL   = 0;
    $MY_SVCDOWNOP     = '>=';
    $MY_SVCDOWNVAL    = 0;
    $MY_DISABLE       = "0,1";
  }
  else if ($LEVEL == 5) {
    $MY_HOSTFILT      = "1,2";
    $MY_HOSTACKLIST   = "0,1";
    $MY_SVCACKLIST    = "0,1";
    $MY_HOSTDOWNOP    = '>=';
    $MY_HOSTDOWNVAL   = 0;
    $MY_SVCDOWNOP     = '>=';
    $MY_SVCDOWNVAL    = 0;
    $MY_DISABLE       = "0,1";
  }
  else if ($LEVEL == 6) {
    $MY_HOSTFILT      = "1,2";
    $MY_HOSTACKLIST   = "0,1";
    $MY_SVCACKLIST    = "0,1";
    $MY_HOSTDOWNOP    = '>=';
    $MY_HOSTDOWNVAL   = 0;
    $MY_SVCDOWNOP     = '>=';
    $MY_SVCDOWNVAL    = 0;
    $MY_ACKLISTOP     = '>=';
    $MY_ACKLISTVAL    = 0;
    $MY_NOSVC         = "0";
    $MY_DISABLE       = "0,1";
  }
  else if ($LEVEL == 7) {
    $MY_SVCFILT       = "0,1,2,3";
    $MY_HOSTFILT      = "0,1,2";
    $MY_HOSTACKLIST   = "0,1";
    $MY_SVCACKLIST    = "0,1";
    $MY_HOSTDOWNOP    = '>=';
    $MY_HOSTDOWNVAL   = 0;
    $MY_SVCDOWNOP     = '>=';
    $MY_SVCDOWNVAL    = 0;
    $MY_ACKLISTOP     = '>=';
    $MY_ACKLISTVAL    = 0;
    $MY_NOSVC         = "0";
    $MY_DISABLE       = "0,1";
  }
}


/* get_nagios_cmd_template
 * return nagios command template with replaced target host/svc values
 */
function get_nagios_cmd_template($action, $ts, $target) {
  global $EXT_CMD;
  $out = '';
  
  if (isset($EXT_CMD[$action])) {
    /* the key host is inappropriate, don't modify that and
     * keep it for backward compatibility of config variables */
    if (empty($target) 
          && isset($EXT_CMD[$action]['host'])) {
      
      foreach($EXT_CMD[$action]['host'] AS $n => $array ) {
        $out .= "[$ts] " . implode(';', $array) . "\n";
      }
    }
    
    else if (count($target) > 1 
              && !empty($target[1]) 
              && $target[1] != '--host--'
              && isset($EXT_CMD[$action]['svc'])) {
      
      foreach($EXT_CMD[$action]['svc'] AS $n => $array ) {
        $out .= "[$ts] " . str_replace(
                              array('$host', '$svc'), 
                              array($target[0], $target[1]),
                              implode(';', $array)) . "\n";
      }
    }
    
    else if (count($target) > 0
              && isset($EXT_CMD[$action]['host'])) {
      
      foreach($EXT_CMD[$action]['host'] AS $n => $array ) {
        $out .= "[$ts] " . str_replace(
                              '$host', $target[0], implode(';', $array)) . "\n";
      }
      
    }
  }
  
  return $out;
}


/* validate_nagios_cmd_comment
 * check if a valid comment has been posted 
 */
function validate_nagios_cmd_comment() {
  global $ILLEGAL_CHAR;
  
  return (
    isset($_POST['comment'])
      && !empty($_POST['comment'])
      && strcspn($_POST['comment'], $ILLEGAL_CHAR) == strlen($_POST['comment']));
}


/* build_nagios_cmd__down
 * prepare command to downtime nagios action
 */
function build_nagios_cmd__down($action, $ts, $target, $dblink) {
  /* this action requires at least one element in target, 
   * the host name */
  if (count($target) < 1) {
    return false;
  }
  
  /* this action requires a valid comment */
  if (!validate_nagios_cmd_comment()) {
    return false;
  }
  
  /* fixed start/end dates */
  if (isset($_POST['start']) && isset($_POST['end'])) {
    $pat = '/[0-9]{1,2}[-]{1}[0-9]{1,2}[-]{1}[0-9]{4} [0-9]{1,2}:[0-9]{1,2}/';
    if (preg_match($pat, $_POST['start']) && preg_match($pat, $_POST['end'])) {
      $start = strtotime($_POST['start']);
      $end = strtotime($_POST['end']);
    }
  }
  
  /* flexible time */
  $endf = 0;
  if (isset($_POST['hour']) || isset($_POST['minute'])) {
    if (is_numeric($_POST['hour'])) {
      $start = time();
      $endf = $start + ($_POST['hour'] * 3600);
    }
    
    if (is_numeric($_POST['minute'])) {
      $start = time();
      
      if ($endf != 0) {
        $endf = $endf + $_POST['minute'] * 60;
      } else {
        $endf = $start + $_POST['minute'] * 60;
      }
    }
  }
  
  /* check dates */
  if ($endf != 0) $end = $endf;
  if (!isset($start) || !isset($end)) return false;
  
  /* build command */
  $out = get_nagios_cmd_template($action, $ts, $target);
  return str_replace(
    array('$start_time', '$end_time', '$user', '$comment'), 
    array($start, $end, $_SESSION['USER'], $_POST['comment']), 
    $out);
}


/* build_nagios_cmd__ack
 * prepare command to acknowledge nagios action
 */
function build_nagios_cmd__ack($action, $ts, $target, $dblink) {
  /* this action requires at least one element in target, 
   * the host name */
  if (count($target) < 1) {
    return false;
  }
  
  /* this action requires a valid comment */
  if (!validate_nagios_cmd_comment()) {
    return false;
  }
  
  /* build command */
  $out = get_nagios_cmd_template($action, $ts, $target);
  return str_replace(
    array('$user', '$comment'), 
    array($_SESSION['USER'], $_POST['comment']), 
    $out);
}


/* build_nagios_cmd__comment_persistent
 * prepare command to persistent comment nagios action
 */
function build_nagios_cmd__comment_persistent($action, $ts, $target, $dblink) {
  return build_nagios_cmd__ack($action, $ts, $target, $dblink);
}


/* build_nagios_cmd__disable
 * prepare command to disable host/svc notifications nagios action
 */
function build_nagios_cmd__disable($action, $ts, $target, $dblink) {
  /* this action requires at least one element in target, 
   * the host name */
  if (count($target) < 1) {
    return false;
  }
  
  /* build command */
  $out = get_nagios_cmd_template($action, $ts, $target);
  $out = str_replace('$user', $_SESSION['USER'], $out);
  return $out;
}


/* build_nagios_cmd__ena_notif
 * prepare command to enable global notifications nagios action
 */
function build_nagios_cmd__ena_notif($action, $ts, $target, $dblink) {
  $out = get_nagios_cmd_template($action, $ts, $target);
  return $out;
}


/* build_nagios_cmd__disa_notif
 * prepare command to disable global notifications nagios action
 */
function build_nagios_cmd__disa_notif($action, $ts, $target, $dblink) {
  $out = get_nagios_cmd_template($action, $ts, $target);
  return $out;
}


/* build_nagios_cmd__recheck
 * prepare command to force recheck host/svc nagios action
 */
function build_nagios_cmd__recheck($action, $ts, $target, $dblink) {
  $out = get_nagios_cmd_template($action, $ts, $target);
  $out = str_replace('$next', $ts+5, $out);
  return $out;
}


/* build_nagios_cmd__reset
 * prepare command to reset host/svc nagios action
 */
function build_nagios_cmd__reset($action, $ts, $target, $dblink) {
  global $QUERY_DOWNTIME_SVC_ID;
  global $QUERY_DOWNTIME_HOST_ID;
  
  /* ugly hack for resetting downtime 
   * downtime can be > 1 (there can be multiple downtime scheduled) */
  if (count($target) > 1 && !empty($target[1]) && $target[1] != '--host--') {
    $query = str_replace('define_mhost', $target[0], $QUERY_DOWNTIME_SVC_ID);
    $query = str_replace('define_msvc', $target[1], $query);
  } else if (count($target) > 0) {
    $query = str_replace('define_mhost', $target[0], $QUERY_DOWNTIME_HOST_ID);
  } else {
    return false;
  }
  
  
  
  /* get partial commands template */
  $out = get_nagios_cmd_template($action, $ts, $target);
  
  /* fetch downtime ids from database */
  if (!($rep_down = mysql_query($query, $dblink))) {
    return false;
  }
  
  /* extract the part to be repeated for each scheduled downtime */
  if (!preg_match('/^(.+DOWNTIME;\$downtime_id)$/m', $out, $capture)) {
    return false;
  }
  
  /* prepare downtime delete commands */
  $dt_cmds = '';
  while (($row = mysql_fetch_row($rep_down))) {
    $dt_cmds .= str_replace('$downtime_id', $row[2], $capture[1]) . "\n";
  }
  mysql_free_result($rep_down);
  
  /* replace in the partial template */
  $out = str_replace($capture[1] . "\n", $dt_cmds, $out);
  return $out;
}


/* handle_action
 * process requested action, send a command to nagios pipe
 * in most of the cases
 * 
 * preconditions: 
 * isset($_POST['action']) 
 * isset($_POST['target']) 
 * is_array($_POST['target'])
 */
function handle_action($dblink) {
  global $CMD_FILE;
  global $EXEC_CMD;
  global $EXEC_PARAM;
  global $SUDO_EXEC;
  global $SUDO_PARAM;
  
  /* list of commands to be sent to nagios 
   * this is for target of type nagios */
  $nagios_cmds = '';
  $ts = time();
  
  /* loop on targets */
  foreach ($_POST['target'] as $t) {
    
    /* field 0: type (required)
     * field 1: host (optional)
     * field 2: svc  (optional) */
    $t = explode(';', $t);
    if (count($t) < 1) {
      continue;
    }
    
    if ($t[0] == 'nagios') {
      array_shift($t);
      
      if (function_exists('build_nagios_cmd__' . $_POST['action'])) {
        $ret = call_user_func('build_nagios_cmd__' . $_POST['action'], 
          $_POST['action'], 
          $ts, 
          $t, 
          $dblink);
        
        if ($ret !== false) {
          /* put the track command first so it appear faster */
          if (isset($_POST['track'])) {
            $track = get_nagios_cmd_template('track', $ts, $t);
            $track = str_replace('$user', $_SESSION['USER'], $track);
            $nagios_cmds .= $track;
          }
          
          /* append action commands */
          $nagios_cmds .= $ret;
        }
      }
    }
  }
  
  /* process nagios */
  if (!empty($nagios_cmds) && !empty($EXEC_CMD)) {
    $args = array();
    
    /* sudo command path, if set */
    if (isset($SUDO_EXEC)) {
      $args[] = escapeshellarg($SUDO_EXEC);
    }
    
    /* sudo parameters, if any */
    if (isset($SUDO_PARAM) && is_array($SUDO_PARAM)) {
      foreach ($SUDO_PARAM as $p) {
        $args[] = escapeshellarg($p);
      }
    }
    
    /* actual command path to execute */
    $args[] = escapeshellarg($EXEC_CMD);
    
    /* script parameters, if any */
    if (isset($EXEC_PARAM) && is_array($EXEC_PARAM)) {
      foreach ($EXEC_PARAM as $p) {
        $args[] = escapeshellarg($p);
      }
    }
    
    $args[] = escapeshellarg($CMD_FILE);
    $args[] = escapeshellarg($nagios_cmds);
    $args[] = '&';
    
    /* execute */
    exec(implode(' ', $args));
  }
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

function die_refresh($message, $timeout = 10, $url = null) {
  global $CODENAME;
  
  if (is_null($url)) {
    $url = $_SERVER['PHP_SELF'];
  }

  $js_timeout = json_encode($timeout);
  $js_url = json_encode($url);

  echo <<<__EOFEOF__
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
  <head>
    <title>$CODENAME - Error</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta http-equiv="CACHE-CONTROL" content="NO-CACHE" />
    <meta http-equiv="PRAGMA" content="NO-CACHE" /> 
    <link rel="stylesheet" type="text/css" href="style.css" />
    <style type="text/css">
      body { margin: 20px;     }
      h1   { font-size: 16px;  }
      div  { fonct-size: 12px; }
    </style>
    <script type="text/javascript">
      var interval = null;
      var timeout = $js_timeout;
      var url = $js_url;
      function refresh_countdown() {
        if (interval == null) {
          return;
        }

        timeout--;
        document.getElementById('timeout').innerHTML = timeout;

        if (timeout <= 0) {
          clearInterval(interval);
          interval = null;
          window.location.href = url;
        }
      }
    </script>
  </head>
  <body onload="interval = window.setInterval(refresh_countdown, 1000);">
    <h1>An error as occurred</h1>
    <div>Error: $message</div>
    <div>Refresh in <span id="timeout">$timeout</span> sec.</div>
  </body>
</html>
__EOFEOF__;

  /* terminates here */
  exit(1);
}

?>
