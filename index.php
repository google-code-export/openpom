<?php
/*
  OpenPOM
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
*/

  /*
  echo "<pre>";
  echo "POST : ";
  print_r($_POST);
  echo "</pre>";
  echo "<pre>";
  echo "GET : ";
  print_r($_GET);
  echo "</pre>";
  */


/* SESSION (STOCK LANG, REFRESH, LINE_BY_PAGE, ...) */
require_once("config.php");
session_name($CODENAME);
session_cache_limiter('nocache');
ini_set('session.gc_maxlifetime', 3600*24*365);
ini_set('session.cookie_lifetime', 3600*24*365);
session_start();

require_once("query.php");
require_once("query-downtime.php");
require_once("query-comment.php");
require_once("query-globalcount.php");
require_once("utils.php");

if (isset($_SERVER['REMOTE_USER'])) 
  $_SESSION['USER'] = strip_tags(addslashes(htmlspecialchars($_SERVER['REMOTE_USER']))) ;
else 
  die_refresh("no user");

special_char();

/* RESET BUTTON ON OPTION */
if (isset($_GET['reset'])) {
  foreach ($_SESSION AS $key => $val) {
    unset($_SESSION[$key]) ;
  }
  session_destroy() ;
  session_unset() ;
  unset($_GET);
  session_name($CODENAME);
  session_start();
  $_SESSION['USER'] = $_SERVER['REMOTE_USER'];
}
require_once("lang.php");

/* SQL CONNECT AND SELECT DB */
if (!($dbconn = mysql_connect($SQL_HOST, $SQL_USER, $SQL_PASSWD))) 
  die_refresh("cannot connect to db") ;

if (!mysql_select_db($SQL_DB, $dbconn)) 
  die_refresh("cannot select db");

/* UNSET GET OPTIONS ON CANCEL */
if ( (isset($_GET['option'])) && ($_GET['option'] != "OK") ) {
  unset($_GET['refresh']);
  unset($_GET['lang']);
  unset($_GET['defaultstep']);
  unset($_GET['defaultlevel']);
  unset($_GET['maxlen_stinfo']);
  unset($_GET['maxlen_host']);
  unset($_GET['maxlen_svc']);
  unset($_GET['maxlen_groups']);
  unset($_GET['frame']);
  foreach ($COLS as $k => $v) {
    if (isset($_GET["defaultcols_{$k}"]))
      unset($_GET["defaultcols_{$k}"]);
  }
  unset($_GET['option']);
}

/* FORCE CLEAN MODE MONITOR (NO FILTER, DEFAULT LEVEL ...)*/
if (isset($_GET['monitor'])) {
  foreach($_GET AS $key => $val)
    if ( ($key != "monitor") && ($key != "sort") && ($key != "order") )
      unset($_GET[$key]);
  unset($_POST);
  $MAXLEVEL = 7 ;
}

/* GLOBAL NOTIF */
$query_global_notif = "SELECT notifications_enabled FROM ".$BACKEND."_programstatus ;";
$res_global_notif = mysql_query($query_global_notif, $dbconn);
$row_global_notif = mysql_fetch_row($res_global_notif);
if ($row_global_notif[0] == 0) $global_notif = "ena_notif" ;
else $global_notif = "disa_notif";

/* PROCESS POST DATA AND SEND CMD TO NAGIOS OR ICINGA */
if (isset($_POST['action']) 
      && isset($_POST['target']) 
      && is_array($_POST['target'])) {
  
  handle_action(
    $_POST['action'], 
    $_POST['target']);
}

/* INIT DEFAULT VALUES (see config.php for other) */
$MY_USER            = $_SESSION['USER'];  //USER VIEW
$MY_SVCFILT         = "1,2,3";            //STATUS SVC FILTER
$MY_HOSTFILT        = "1";                //STATUS HOST FILTER
$MY_SVCACKLIST      = 0;                  //SVC ACKNOWLEDGE
$MY_HOSTACKLIST     = 0;                  //HOST ACKNOWLEDGE
$MY_HOSTDOWNOP      = '=';                //HOST DOWNTIME DEPTH COMPARISON OPERATOR
$MY_HOSTDOWNVAL     = 0;                  //HOST DOWNTIME DEPTH COMPARED VALUE
$MY_SVCDOWNOP       = '=';                //SVC DOWNTIME DEPTH COMPARISON OPERATOR
$MY_SVCDOWNVAL      = 0;                  //SVC DOWNTIME DEPTH COMPARED VALUE
$MY_ACKLISTOP       = '=';                //DOWNTIME AND ACK SVC FOR ACK AND DOWNTIME HOST, COMP OP
$MY_ACKLISTVAL      = 0;                  //DOWNTIME AND ACK SVC FOR ACK AND DOWNTIME HOST, COMP VAL
$MY_NOSVC           = 1;                  //NO SVC FOR CRITICAL HOST
$MY_DISABLE         = 1;                  //DISABLE ALERT ARE TREATED LIKE ACK AND DOWN
$MY_SOFT            = 0;                  //SOFT ALERTS (0 print soft alerts)
$FIRST              = "0";                //FIRST GET ROW 
$MY_TRACK_ANY       = 1 ;                 //TRACK ANYTHING

/* Internal flags used to identify the type of comments set on host
 * and services */
define('ENTRY_COMMENT_NORMAL', 0x01);
define('ENTRY_COMMENT_TRACK',  0x02);



/* GET DEFAULT LEVEL */
if ( (isset($_GET['defaultlevel'])) && (is_numeric($_GET['defaultlevel'])) &&
     ($_GET['defaultlevel'] > 0) && ($_GET['defaultlevel'] <= $MAXLEVEL) ) {
  $LEVEL = $_GET['defaultlevel'] ;
  $_SESSION['LEVEL'] = $LEVEL;
}
else if (isset($_SESSION['LEVEL']))
  $LEVEL = $_SESSION['LEVEL'];
else
  $_SESSION['LEVEL'] = $LEVEL;

/* SELECT LEVEL */
if ( (isset($_GET['level'])) && (is_numeric($_GET['level'])) &&
  ($_GET['level'] > 0) && ($_GET['level'] <= $MAXLEVEL) ) 
  $LEVEL = $_GET['level'] ;

select_level($LEVEL);

/* filter */
if (isset($_GET['clear'])) {
    if (isset($_GET['filtering']))
        unset($_GET['filtering']);
    $FILTER = '';
}
else if (isset($_GET['filtering']))
    $FILTER = trim($_GET['filtering']);
else
    $FILTER = '';

/* Sort column */
if (isset($_GET['sort']) && !empty($_GET['sort'])) {
    if (isset($COLUMN_DEFINITION[$_GET['sort']]))
        $SORTCOL = $_GET['sort'];
    else
        unset($_GET['sort']);
}

/* Sort direction */
if (isset($_GET['order'])) {
    if ($_GET['order'] == 0 || $_GET['order'] == 1)
        $SORTDIR = $_GET['order'];
    else
        unset($_GET['order']);
}

/* GET NEXT OR PREVIOUS PAGE */
if (isset($_GET['prev'])) 
  if ( (is_numeric($_GET['prev'])) && ($_GET['prev'] > 0) )
    $FIRST = $_GET['prev'];

if (isset($_GET['next'])) 
  if ( (is_numeric($_GET['next'])) && ($_GET['next'] > 0) )
    $FIRST = $_GET['next'];

/* GET REFRESH VALUE */
if ( (isset($_GET['refresh'])) && (is_numeric($_GET['refresh'])) &&
     ($_GET['refresh'] >= 10) && ($_GET['refresh'] <= 3600) ) {
    $REFRESHTIME = $_GET['refresh'];
    $_SESSION['REFRESH'] = $REFRESHTIME;
}
else if (isset($_SESSION['REFRESH']))
  $REFRESHTIME = $_SESSION['REFRESH'];
else 
  $_SESSION['REFRESH'] = $REFRESHTIME;

/* GET DEFAULT MAX LINE PER PAGE */
if ( (isset($_GET['defaultstep'])) && (is_numeric($_GET['defaultstep'])) &&
     ($_GET['defaultstep'] > 0) && ($_GET['defaultstep'] < 1000) ) {
  $LINE_BY_PAGE = $_GET['defaultstep'];
  $_SESSION['STEP'] = $LINE_BY_PAGE;
}
else if (isset($_SESSION['STEP']))
  $LINE_BY_PAGE = $_SESSION['STEP'];
else
  $_SESSION['STEP'] = $LINE_BY_PAGE;

/* SELECT MAX LINE PER PAGE */
if ( (isset($_GET['step'])) && (is_numeric($_GET['step'])) &&
  ($_GET['step'] > 0) && ($_GET['step'] < 1000) )
  $LINE_BY_PAGE = $_GET['step'] ;

/* GET/SET FONT SIZE FOR ALERT */
if ( (isset($_GET['fontsize'])) && (is_numeric($_GET['fontsize'])) &&
     ($_GET['fontsize'] > 0) && ($_GET['fontsize'] < 101) ) {
  $FONT_SIZE = $_GET['fontsize'];
  $_SESSION['FONTSIZE'] = $FONT_SIZE;
}
else if (isset($_SESSION['FONTSIZE']))
  $FONT_SIZE = $_SESSION['FONTSIZE'];
else
  $_SESSION['FONTSIZE'] = $FONT_SIZE;


/* ENABLE/DISABLE QUICK SEARCH */
if (isset($_GET['quicksearch'])) {
  $QUICKSEARCH = 1;
}
else if (isset($_GET['option'])) {
  $QUICKSEARCH = 0;
}
else if (isset($_SESSION['QUICKSEARCH']))
  $QUICKSEARCH = $_SESSION['QUICKSEARCH'];
$_SESSION['QUICKSEARCH'] = $QUICKSEARCH ;

/* DISPLAY THE FRAME AROUND THE PAGE */
if (isset($_GET['frame'])) {
  $FRAME = 0;
}
else if (isset($_GET['option'])) {
  $FRAME = 1;
}
else if (isset($_SESSION['FRAME'])) 
  $FRAME = $_SESSION['FRAME'];
$_SESSION['FRAME'] = $FRAME;

/* HISTORY TO DISPLAY */
if(isset($_GET['option'])) {
  foreach($HISTORY AS $key => $val) {
    if ( ( ! isset($_GET[$key]) ) && ( isset($_SESSION['HISTORY'][$key]) ) )
      unset($_SESSION['HISTORY'][$key]) ;
    else if ( isset($_GET[$key]) )
      $_SESSION['HISTORY'][$key] = 1 ;
  } //end foreach
}
else if (!isset($_SESSION['HISTORY'])) {
  foreach($HISTORY AS $key => $val) {
    if ( $val == 1 ) $_SESSION['HISTORY'][$key] = 1 ;
  } //end foreach
}

/* INFORMATION ON STATUS POPIN TO DISPLAY */
if(isset($_GET['option'])) {
  if (isset($_GET['showgraph'])) $_SESSION['STATUS']['graph'] = 1;
  else if (isset($_SESSION['STATUS']['graph'])) unset($_SESSION['STATUS']['graph']) ;
  if (isset($_GET['showall']))   $_SESSION['STATUS']['all']   = 1;
  else if (isset($_SESSION['STATUS']['all']))   unset($_SESSION['STATUS']['all']) ;
  if ( (isset($_GET['showlimit'])) && (is_numeric($_GET['showlimit'])) ) 
    $_SESSION['STATUS']['limit'] = $_GET['showlimit'];
  foreach($STATUSPOPIN AS $key => $val) {
    if ( ( ! isset($_GET[$key]) ) && ( isset($_SESSION['STATUS'][$key]) ) )
      unset($_SESSION['STATUS'][$key]) ;
    else if ( isset($_GET[$key]) )
      $_SESSION['STATUS'][$key] = 1 ;
  } //end foreach
}
else if (!isset($_SESSION['STATUS'])) {
  if ($SHOWSTATUSGRAPH == 1) $_SESSION['STATUS']['graph'] = $SHOWSTATUSGRAPH ;
  if ($SHOWSTATUSALL == 1)   $_SESSION['STATUS']['all']   = $SHOWSTATUSALL ;
  $_SESSION['STATUS']['limit'] = $SHOWSTATUSLIMIT ;
  foreach($STATUSPOPIN AS $key => $val) {
    if ( $val == 1 ) $_SESSION['STATUS'][$key] = 1 ;
  } //end foreach
}

/* GET DO WE DISPLAY POPIN */
if (isset($_GET['popin']) 
    && ($_GET['popin'] === '0' || $_GET['popin'] === '1')) {
  $POPIN = $_GET['popin'];
  $_SESSION['POPIN'] = $POPIN;

} else if (isset($_SESSION['POPIN'])) {
  $POPIN = $_SESSION['POPIN'];
} else {
  $_SESSION['POPIN'] = $POPIN;
}


if (!init_columns(&$err))
    die_refresh($err);

if (!isset($_GET['json'])) {

    /* column user preferences */
    foreach ($COLUMN_DEFINITION as $col => &$def) {

        /* some columns cannot be modified */
        if (($def['opts'] & COL_NO_USER_PREF))
            continue;

        /* max length */
        if (isset($_GET['option']) && isset($def['lmax'])) {
            if (isset($_GET["maxlen_$col"]) && is_numeric($_GET["maxlen_$col"]) &&
                $_GET["maxlen_$col"] > 0 && $_GET["maxlen_$col"] < 1000) {

                pdebug("set MAXLEN_$col = ".$_GET["maxlen_$col"]);
                $_SESSION["MAXLEN_$col"] = $_GET["maxlen_$col"];
            }

            if (isset($_SESSION["MAXLEN_$col"]))
                $def['lmax'] = $_SESSION["MAXLEN_$col"];
        }

        /* display of some columns is not modifiable */
        if (($def['opts'] & COL_MUST_DISPLAY))
            continue;

        /* set new preferences */
        if (isset($_GET['option'])) {
            /* on/off */
            if (isset($_GET["defaultcols_$col"]) && $_GET["defaultcols_$col"]) {
                $def['opts'] |= COL_ENABLED;
                $_SESSION["COLS_$col"] = 1;
            }
            else {
                $def['opts'] &= ~COL_ENABLED;
                $_SESSION["COLS_$col"] = 0;
            }
        }
        /* existing preferences in session */
        else if (isset($_SESSION["COLS_$col"])) {
            if ($_SESSION["COLS_$col"])
                $def['opts'] |= COL_ENABLED;
            else
                $def['opts'] &= ~COL_ENABLED;
        }

        /* otherwise use column default, that is, display the column
         * if flag COL_ENABLED is present */
    }
}


/* KEEP GET FOR FUTUR LINK */
if (isset($_GET['n']))
  unset($_GET['n']);
if (isset($_GET['option']))
  unset($_GET['option']);
$MY_GET = "?";
if (isset($_GET)) {
  foreach($_GET AS $key => $val) {
    $MY_GET = $MY_GET."&".rawurlencode($key)."=".rawurlencode($val);
  }
}
$MY_GET_NO_SORT = preg_replace('/[?&]{1}sort=[a-z]+/','',$MY_GET);
$MY_GET_NO_SORT = preg_replace('/[?&]{1}order=[01]+/','',$MY_GET_NO_SORT);
$MY_GET_NO_NEXT = preg_replace('/[?&]{1}next=[0-9]+/','',$MY_GET);
$MY_GET_NO_NEXT = preg_replace('/[?&]{1}prev=[0-9]+/','',$MY_GET_NO_NEXT);
$MY_GET_NO_FILT = preg_replace('/[?&]{1}clear=[^&]+/','',$MY_GET_NO_NEXT);
$MY_GET_NO_FILT = preg_replace('/[?&]{1}filter=[^&]+/','',$MY_GET_NO_FILT);
$MY_GET_NO_FILT = preg_replace('/[?&]{1}filtering=[^&]+/','',$MY_GET_NO_FILT);
$MY_GET_NO_FILT = preg_replace('/[?&]{1}clear=[^&]+/','',$MY_GET_NO_FILT);


$MY_QUERY_PARTS = array(
    'define_host_search' => '',
    'define_svc_search' => '',
    'define_orderby' => '',
    'define_expr_cols' => '',
    'define_cvar_host_cols' => '',
    'define_cvar_svc_cols' => '',
    'define_cvar_host_joins' => '',
    'define_cvar_svc_joins' => '',
    'define_cvar_cols' => '',

  'define_my_user'            =>  mysql_real_escape_string($MY_USER, $dbconn),
  'define_my_svcfilt'         =>  mysql_real_escape_string($MY_SVCFILT, $dbconn),
  'define_my_svcacklist'      =>  mysql_real_escape_string($MY_SVCACKLIST, $dbconn),
  'define_my_hostacklist'     =>  mysql_real_escape_string($MY_HOSTACKLIST, $dbconn),
  'define_my_hostdownop'      =>  $MY_HOSTDOWNOP,
  'define_my_hostdownval'     =>  $MY_HOSTDOWNVAL,
  'define_my_svcdownop'       =>  $MY_SVCDOWNOP,
  'define_my_svcdownval'      =>  $MY_SVCDOWNVAL,
  'define_my_acklistop'       =>  $MY_ACKLISTOP,
  'define_my_acklistval'      =>  $MY_ACKLISTVAL,
  'define_my_disable'         =>  mysql_real_escape_string($MY_DISABLE, $dbconn),
  'define_my_soft'            =>  mysql_real_escape_string($MY_SOFT, $dbconn),
  'define_my_nosvc'           =>  mysql_real_escape_string($MY_NOSVC, $dbconn),
  'define_my_hostfilt'        =>  mysql_real_escape_string($MY_HOSTFILT, $dbconn),
  'define_first'              =>  mysql_real_escape_string($FIRST, $dbconn),
  'define_step'               =>  mysql_real_escape_string($LINE_BY_PAGE, $dbconn),
  'define_track_anything'     =>  $MY_TRACK_ANY,
  'define_host_service'       =>  mysql_real_escape_string($HOST_SERVICE, $dbconn),
);

if (!init_filter(&$err, $FILTER))
    die_refresh($err);

init_orderby();
terminate_query();


/* QUERY AND SET GLOBAL COUNTER */
$query_glob = str_replace(array_keys($MY_QUERY_PARTS),
                          array_values($MY_QUERY_PARTS),
                          $QUERY_GLOBAL_COUNT);


if (!($rep_glob = mysql_query($query_glob, $dbconn))) {
  $errno = mysql_errno($dbconn);
  $txt_error = mysql_error($dbconn);
  error_log("invalid globalcount query: $errno, $txt_error");
  die_refresh("invalid globalcount query: $errno, $txt_error");
}

$glob_ok       = 0;
$glob_warning  = 0;
$glob_critical = 0;
$glob_unknown  = 0;
$glob_ack      = 0;
$glob_down     = 0;
$glob_notif    = 0;
$glob_check    = 0;
$glob_all      = 0;

while (($glob_counter = mysql_fetch_array($rep_glob, MYSQL_ASSOC))) {
    if ($glob_counter['current_state'] == 0) $glob_ok++;
    else if ($glob_counter['current_state'] == 1) $glob_warning++;
    else if ($glob_counter['current_state'] == 2) $glob_critical++;
    else if ($glob_counter['current_state'] == 3) $glob_unknown++;

    if ($glob_counter['problem_has_been_acknowledged'] == 1) $glob_ack++;
    if ($glob_counter['scheduled_downtime_depth'] > 0) $glob_down++;
    if ($glob_counter['notifications_enabled'] == 0) $glob_notif++;

    if ($glob_counter['active_checks_enabled'] == 0 &&
        $glob_counter['passive_checks_enabled'] == 0) $glob_check++;

    $glob_all++;
}

/* FORGE QUERY (AUTO CHANGE LEVEL IN MONITOR MODE) */
$nb_rows = 0;
$level = $LEVEL;
while ( ($nb_rows <= 0) && ($level <= $MAXLEVEL) ) {
  $query = str_replace(array_keys($MY_QUERY_PARTS),
                       array_values($MY_QUERY_PARTS),
                       $QUERY);

  /*
  echo "<pre>";
  echo $query;
  echo "</pre>";
  */
 
  $query_start = getmicrotime();
  if (!($rep = mysql_query($query, $dbconn))) {
    $errno = mysql_errno($dbconn);
    $txt_error = mysql_error($dbconn);
    error_log("invalid query: $errno, $txt_error");
    die_refresh("invalid query: $errno, $txt_error");
  }
  $query_time = getmicrotime() - $query_start;
  $str_query_time = '%01.4fs';
  if ( (($nb_rows = mysql_num_rows($rep)) > 0) || (!isset($_GET['monitor'])) )
    break;
  else
    select_level($level++);
}

/* json output mode */
if (isset($_GET['json'])) {
  $json = array('time' => $query_time, 'data' => array());
  while ($data = mysql_fetch_array($rep, MYSQL_ASSOC))
    $json['data'][] = $data;
  json_success($json, 'ISO-8859-1');
}

$array_total_rows = mysql_fetch_row( mysql_query( "SELECT FOUND_ROWS( )", $dbconn ) );
$total_rows       = $array_total_rows[0];

/* PREPARE DISPLAY */
$hit_ok       = 0;
$hit_warning  = 0;
$hit_critical = 0;
$hit_unknown  = 0;
$hit_down     = 0;
$hit_ack      = 0;
$hit_notif    = 0;
$hit_check    = 0;
$line         = 1;

while ($data = mysql_fetch_array($rep, MYSQL_ASSOC) ) {
  switch($data['STATUS']) {
    case 0: $hit_ok++;       break;
    case 1: $hit_warning++;  break;
    case 2: $hit_critical++; break;
    case 3: $hit_unknown++;  break;
  }
  if ($data['ACK'] == 1) $hit_ack++;
  if ($data['DOWNTIME'] > 0) $hit_down++;
  if ($data['NOTIF'] == 0) $hit_notif++;
  if (!$data['ACTIVE'] && !$data['PASSIVE']) $hit_check++;
}

if      ($hit_critical > 0) $framecolor = $CRITICAL;
else if ($hit_warning > 0)  $framecolor = $WARNING;
else if ($hit_unknown > 0)  $framecolor = $UNKNOWN;
else if ($hit_ok > 0)       $framecolor = $OK;
else                        $framecolor = $OTHER;

if ($nb_rows > 0)
  mysql_data_seek($rep, 0);

require_once("header.php");
require_once("action.php");
require_once("alert.php");
require_once("footer.php");

mysql_close($dbconn);
mysql_free_result($rep);

?>
