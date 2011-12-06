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


/*******************************************************************************
 * ACTIONS ON EVENTS: common utility functions (any action type) 
 ******************************************************************************/

/* validate_comment_value
 * check if a valid comment has been posted 
 */
function validate_comment_value(&$out) {
  global $ILLEGAL_CHAR;
  
  /* require comment */
  if (!isset($_POST['comment'])) {
    return false;
  }
  
  /* this has to do with function special_char calling 
   * htmlspecialchars */
  $out = html_entity_decode($_POST['comment'], ENT_QUOTES);
  
  return !empty($out) 
    && !preg_match('/' . preg_quote($ILLEGAL_CHAR) . '/', $out);
}


/* validate_downtime_range
 * check if a valid start/end date range has been posted 
 */
function validate_downtime_range(&$start, &$end) {
  /* fixed start/end dates */
  if (isset($_POST['start']) && isset($_POST['end'])) {
    $pat = '/[0-9]{1,2}[-]{1}[0-9]{1,2}[-]{1}[0-9]{4} [0-9]{1,2}:[0-9]{1,2}/';
    if (preg_match($pat, $_POST['start']) && preg_match($pat, $_POST['end'])) {
      $start = strtotime($_POST['start']);
      $end = strtotime($_POST['end']);
    }
  }
  
  /* fixed from now */
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
  return isset($start) && isset($end);
}


/*******************************************************************************
 * ACTIONS ON EVENTS: function to get per-type action templates 
 ******************************************************************************/

/* get_nagios_cmd_template
 * return "nagios" command template
 */
function get_nagios_cmd_template($action, $ts, $target, $recurse = false) {
  global $EXT_CMD;
  $out = '';
  
  if (isset($EXT_CMD[$action])) {
    
    /* the key host is inappropriate, don't modify that and
     * keep it for backward compatibility of config variables */
    if (empty($target) 
          && isset($EXT_CMD[$action]['host'])) {
      
      foreach ($EXT_CMD[$action]['host'] AS $n => $array ) {
        $out .= "[$ts] " . implode(';', $array) . "\n";
      }
    }
    
    /* target has 2+ elements, host and svc are present and svc is
     * not denoting a host, so this is a service template */
    else if (count($target) > 1 
              && !empty($target[1]) 
              && $target[1] != '--host--'
              && isset($EXT_CMD[$action]['svc'])) {
      
      foreach ($EXT_CMD[$action]['svc'] AS $n => $array ) {
        $out .= "[$ts] " . str_replace(
                              array('$host', '$svc'), 
                              array($target[0], $target[1]),
                              implode(';', $array)) . "\n";
      }
    }
    
    /* otherwise if target has 1+ element, this is a host template */
    else if (count($target) > 0
              && isset($EXT_CMD[$action]['host'])) {
      
      foreach ($EXT_CMD[$action]['host'] AS $n => $array ) {
        $out .= "[$ts] " . str_replace(
                              '$host', $target[0], implode(';', $array)) . "\n";
      }
    }
    
    /* handle track option here
     * put the track before the actual commands so it appear faster */
    if (!$recurse && !empty($out) && isset($_POST['track'])) {
      $out = get_nagios_cmd_template('track', $ts, $target, true) . $out;
    }
  }
  
  return $out;
}


/*******************************************************************************
 * ACTIONS ON EVENTS: prepare action function for type "nagios" 
 ******************************************************************************/

/* prepare_action_nagios__down
 * prepare command for "nagios" action "downtime"
 */
function prepare_action_nagios__down($action, $ts, $target) {
  /* this action requires at least one element in target, 
   * the host name */
  if (count($target) < 1) {
    return false;
  }
  
  /* this action requires a valid comment */
  if (!validate_comment_value($c)) {
    return false;
  }
  
  /* this action requires a valid start/end date range */
  if (!validate_downtime_range($start, $end)) {
    return false;
  }
  
  /* build command */
  $out = get_nagios_cmd_template($action, $ts, $target);
  return str_replace(
    array('$start_time', '$end_time', '$user', '$comment'), 
    array($start, $end, $_SESSION['USER'], $c), 
    $out);
}


/* prepare_action_nagios__ack
 * prepare command for "nagios" action "acknowledge"
 */
function prepare_action_nagios__ack($action, $ts, $target) {
  /* this action requires at least one element in target, 
   * the host name */
  if (count($target) < 1) {
    return false;
  }
  
  /* this action requires a valid comment */
  if (!validate_comment_value($c)) {
    return false;
  }
  
  /* build command */
  $out = get_nagios_cmd_template($action, $ts, $target);
  return str_replace(
    array('$user', '$comment'), 
    array($_SESSION['USER'], $c), 
    $out);
}


/* prepare_action_nagios__comment_persistent
 * prepare command for "nagios" action "comment_persistent"
 */
function prepare_action_nagios__comment_persistent($action, $ts, $target) {
  return prepare_action_nagios__ack($action, $ts, $target);
}


/* prepare_action_nagios__disable
 * prepare command for "nagios" action "disable"
 */
function prepare_action_nagios__disable($action, $ts, $target) {
  return prepare_action_nagios__ack($action, $ts, $target);
}


/* prepare_action_nagios__ena_notif
 * prepare command for "nagios" action "ena_notif" (global)
 */
function prepare_action_nagios__ena_notif($action, $ts, $target) {
  $out = get_nagios_cmd_template($action, $ts, $target);
  return $out;
}


/* prepare_action_nagios__disa_notif
 * prepare command for "nagios" action "disa_notif" (global)
 */
function prepare_action_nagios__disa_notif($action, $ts, $target) {
  return prepare_action_nagios__ena_notif($action, $ts, $target);
}


/* prepare_action_nagios__recheck
 * prepare command for "nagios" action "recheck"
 */
function prepare_action_nagios__recheck($action, $ts, $target) {
  $out = get_nagios_cmd_template($action, $ts, $target);
  $out = str_replace('$next', $ts, $out);
  return $out;
}


/* prepare_action_nagios__reset
 * prepare command for "nagios" action "reset"
 */
function prepare_action_nagios__reset($action, $ts, $target) {
  global $QUERY_DOWNTIME_SVC_ID;
  global $QUERY_DOWNTIME_HOST_ID;
  global $dbconn;
  
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
  if (!($rep_down = mysql_query($query, $dbconn))) {
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


/*******************************************************************************
 * ACTIONS ON EVENTS: functions for caching prepared action
 ******************************************************************************/

/* cache_action_nagios
 * append a pepared "nagios" action to the cache
 */
function cache_action_nagios($type, $prepared, &$cache) {
  /* init cache */
  if (!isset($cache[$type])) {
    $cache[$type] = '';
  }
  
  /* add prepared action if not already present */
  if (!strstr($cache[$type], $prepared)) {
    $cache[$type] .= $prepared;
  }
}


/*******************************************************************************
 * ACTIONS ON EVENTS: functions for executing prepared actions
 ******************************************************************************/

/* execute_prepared_actions_nagios
 * execute prepared actions of type "nagios" 
 */
function execute_prepared_actions_nagios($actions) {
  global $CMD_FILE;
  global $EXEC_CMD;
  global $EXEC_PARAM;
  global $SUDO_EXEC;
  global $SUDO_PARAM;
  
  if (!empty($actions) && !empty($EXEC_CMD)) {
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
    $args[] = escapeshellarg($actions);
    $args[] = '&';
    
    /* execute */
    exec(implode(' ', $args));
  }
}


/*******************************************************************************
 * ACTIONS ON EVENTS: main handling function
 ******************************************************************************/

/* handle_action
 * process requested action, send a command to nagios pipe
 * in most of the cases
 */
function handle_action($action, $target) {
  
  /* cache_action
   * prepared actions cached in an array indexed per action type
   * 
   * prepact['nagios']      string of Nagios commands, one per line
   */
  $cache_action = array();
  $ts = time();
  
  /* loop on targets */
  foreach ($target as $t) {
    
    /* field 0: type (required)
     * field 1: host (optional)
     * field 2: svc  (optional) */
    $t = explode(';', $t);
    
    if (count($t) > 0) {
      $type = $t[0];
      $fct = 'prepare_action_' . $type . '__' . $action;
      array_shift($t);
      
      if (function_exists($fct)) {
        $ret = call_user_func($fct, $action, $ts, $t);
        $fct = 'cache_action_' . $type;
        
        if ($ret && function_exists($fct)) {
          
          /* argument 3 should be declared as a reference in
           * function definition, otherwise cache would never
           * be updated */
          call_user_func_array($fct, array($type, $ret, &$cache_action));
        }
      }
    }
  }
  
  /* execute cached actions */
  foreach ($cache_action as $type => $actions) {
    $fct = 'execute_prepared_actions_' . $type;
    if (function_exists($fct)) {
      call_user_func($fct, $actions);
    }
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
