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
session_cache_limiter('nocache');
ini_set('session.gc_maxlifetime', 3600*24*365);
ini_set('session.cookie_lifetime', 3600*24*365);
session_start();

if (isset($_SERVER['REMOTE_USER'])) 
  $_SESSION['USER'] = strip_tags(addslashes(htmlspecialchars($_SERVER['REMOTE_USER']))) ;
else 
  die_refresh("no user");

require_once("config.php");
require_once("query.php");
require_once("query-downtime.php");
require_once("utils.php");
special_char(); 

/* RESET BUTTON ON OPTION */
if (isset($_GET['reset'])) {
  foreach ($_SESSION AS $key => $val) {
    unset($_SESSION[$key]) ;
  }
  session_destroy() ;
  session_unset() ;
  unset($_GET);
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
if ( (isset($_GET['option'])) && ($_GET['option'] != "Ok") ) {
  unset($_GET['refresh']);
  unset($_GET['lang']);
  unset($_GET['step']);
  unset($_GET['defaultlevel']);
  unset($_GET['maxlentd']);
  unset($_GET['frame']);
  unset($_GET['option']);
}

/* FORCE CLEAN MODE MONITOR (NO FILTER, DEFAULT LEVEL ...)*/
if (isset($_GET['monitor'])) {
  foreach($_GET AS $key => $val)
    if ( ($key != "monitor") && ($key != "sort") && ($key != "order") )
      unset($_GET[$key]);
  unset($_POST);
}

/* GLOBAL NOTIF */
$query_global_notif = "SELECT notifications_enabled FROM nagios_programstatus ;";
$res_global_notif = mysql_query($query_global_notif, $dbconn);
$row_global_notif = mysql_fetch_row($res_global_notif);
if ($row_global_notif[0] == 0) $global_notif = "ena_notif" ;
else $global_notif = "disa_notif";

/* PROCESS POST DATA AND SEND CMD TO NAGIOS OR ICINGA */
if (isset($_POST['action']) 
      && isset($_POST['target']) 
      && is_array($_POST['target'])) {
  handle_action($dbconn);
}

/* INIT DEFAULT VALUES (see config.php for other) */
$SEPARATOR       = ", ";               //SEPARATOR FOR GROUPS
$MY_FILTER       = "%%";               //NO FILTER
$MY_USER         = $_SESSION['USER'];  //USER VIEW
$MY_SVCFILT      = "1,2,3";            //STATUS SVC FILTER
$MY_HOSTFILT     = "1";                //STATUS HOST FILTER
$MY_SVCACKLIST   = 0;                  //SVC ACKNOWLEDGE
$MY_HOSTACKLIST  = 0;                  //HOST ACKNOWLEDGE
$MY_HOSTDOWNOP   = '=';                //HOST DOWNTIME DEPTH COMPARISON OPERATOR
$MY_HOSTDOWNVAL  = 0;                  //HOST DOWNTIME DEPTH COMPARED VALUE
$MY_SVCDOWNOP    = '=';                //SVC DOWNTIME DEPTH COMPARISON OPERATOR
$MY_SVCDOWNVAL   = 0;                  //SVC DOWNTIME DEPTH COMPARED VALUE
$MY_ACKLISTOP    = '=';                //DOWNTIME AND ACK SVC FOR ACK AND DOWNTIME HOST, COMP OP
$MY_ACKLISTVAL   = 0;                  //DOWNTIME AND ACK SVC FOR ACK AND DOWNTIME HOST, COMP VAL
$MY_NOSVC        = 1;                  //NO SVC FOR CRITICAL HOST
$MY_DISABLE      = 1;                  //DISABLE ALERT ARE TREATED LIKE ACK AND DOWN
$MY_SOFT         = 0;                  //SOFT ALERTS (0 print soft alerts)
$MY_ORAND        = "OR";               //FILTER CONDITION
$MY_LIKE         = "LIKE";             //FILTER RESTRICTION
$SORTFIELD       = "COEF, DURATION";   //SORT FIELD 
$SORTORDERFIELD  = "ASC";              //SORT ORDER
$FIRST           = "0";                //FIRST GET ROW 
$FILTER          = '';                 //NO FILTER

/* PROCESS GET DATA */

/* GET DEFAULT LEVEL */
if ( (isset($_GET['defaultlevel'])) && (is_numeric($_GET['defaultlevel'])) &&
     ($_GET['defaultlevel'] > 0) && ($_GET['defaultlevel'] <= $MAXLEVEL) ) {
  $LEVEL = substr($_GET['defaultlevel'], 0, 1);
  $_SESSION['LEVEL'] = $LEVEL;
}
else if (isset($_SESSION['LEVEL']))
  $LEVEL = $_SESSION['LEVEL'];
else
  $_SESSION['LEVEL'] = $LEVEL;

/* SELECT LEVEL */
if ( (isset($_GET['level'])) && (is_numeric($_GET['level'])) &&
  ($_GET['level'] > 0) && ($_GET['level'] <= $MAXLEVEL) ) 
  $LEVEL = substr($_GET['level'], 0, 1);

select_level($LEVEL);

/* FORGE FILTER */
if (isset($_GET['clear'])) { 
  if (isset($_GET['filtering'])) unset($_GET['filtering']);
}
else if ( (isset($_GET['filtering'])) && (strlen($_GET['filtering']) < 2) ) {
  unset($_GET['filtering']);
}
if ( (isset($_GET['filtering'])) && (!isset($_GET['clear'])) ) {
  if (strlen($_GET['filtering']) > 100) 
    die_refresh("filter too long");
  foreach(str_split($ILLEGAL_CHAR) AS $char) {
    $pos = strpos($_GET['filtering'], $char);
    if ( ($pos === 0) || ($pos > 0) )
      die_refresh("invalid char in filter");
  }
  if (preg_match('/^(= |not )/',$_GET['filtering'],$keyword)) {
    if ($keyword[0] == "= ") {
      $FILTER = $_GET['filtering'];
      $MY_FILTER = substr($_GET['filtering'], 2);
      $MY_LIKE = "=";
    }
    else if ($keyword[0] == "not ") {
      $FILTER = $_GET['filtering'];
      $MY_FILTER = '%'.substr($_GET['filtering'], 4).'%';
      $MY_LIKE = "NOT LIKE";
      $MY_ORAND = "AND";
    }
  }
  else {
    $FILTER = $_GET['filtering'];
    $MY_FILTER = '%'.$FILTER.'%';
  }
}

/* SORT FIELD ORDER */
if (isset($_GET['sort'])) {
  foreach($COLS AS $key => $val) {
    if ($_GET['sort'] == $key) {
      $SORTFIELD = $val;
      if ( (isset($_GET['order'])) && ($_GET['order'] != 0) )
        $SORTORDERFIELD = "DESC";
    }
  }
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

/* GET MAX LINE PER PAGE */
if ( (isset($_GET['step'])) && (is_numeric($_GET['step'])) &&
     ($_GET['step'] > 0) && ($_GET['step'] < 1000) ) {
  $LINE_BY_PAGE = $_GET['step'];
  $_SESSION['STEP'] = $LINE_BY_PAGE;
}
else if (isset($_SESSION['STEP']))
  $LINE_BY_PAGE = $_SESSION['STEP'];
else
  $_SESSION['STEP'] = $LINE_BY_PAGE;

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

/* GET MAX CHARACTERS FOR STINFO COLUMN */
if ( (isset($_GET['maxlen_stinfo'])) && (is_numeric($_GET['maxlen_stinfo'])) &&
     ($_GET['maxlen_stinfo'] > 0) && ($_GET['maxlen_stinfo'] < 1000) ) {
  $MAXLEN_STINFO = $_GET['maxlen_stinfo'];
  $_SESSION['MAXLEN_STINFO'] = $MAXLEN_STINFO;
}
else if (isset($_SESSION['MAXLEN_STINFO']))
  $MAXLEN_STINFO = $_SESSION['MAXLEN_STINFO'];
else
  $_SESSION['MAXLEN_STINFO'] = $MAXLEN_STINFO;

/* GET MAX CHARACTERS FOR HOST/MACHINE COLUMN */
if ( (isset($_GET['maxlen_host'])) && (is_numeric($_GET['maxlen_host'])) &&
     ($_GET['maxlen_host'] > 0) && ($_GET['maxlen_host'] < 1000) ) {
  $MAXLEN_HOST = $_GET['maxlen_host'];
  $_SESSION['MAXLEN_HOST'] = $MAXLEN_HOST;
}
else if (isset($_SESSION['MAXLEN_HOST']))
  $MAXLEN_HOST = $_SESSION['MAXLEN_HOST'];
else
  $_SESSION['MAXLEN_HOST'] = $MAXLEN_HOST;

/* GET MAX CHARACTERS FOR SERVICE COLUMN */
if ( (isset($_GET['maxlen_svc'])) && (is_numeric($_GET['maxlen_svc'])) &&
     ($_GET['maxlen_svc'] > 0) && ($_GET['maxlen_svc'] < 1000) ) {
  $MAXLEN_SVC = $_GET['maxlen_svc'];
  $_SESSION['MAXLEN_SVC'] = $MAXLEN_SVC;
}
else if (isset($_SESSION['MAXLEN_SVC']))
  $MAXLEN_SVC = $_SESSION['MAXLEN_SVC'];
else
  $_SESSION['MAXLEN_SVC'] = $MAXLEN_SVC;

/* GET MAX CHARACTERS FOR GROUPS COLUMN */
if ( (isset($_GET['maxlen_groups'])) && (is_numeric($_GET['maxlen_groups'])) &&
     ($_GET['maxlen_groups'] > 0) && ($_GET['maxlen_groups'] < 1000) ) {
  $MAXLEN_GROUPS = $_GET['maxlen_groups'];
  $_SESSION['MAXLEN_GROUPS'] = $MAXLEN_GROUPS;
}
else if (isset($_SESSION['MAXLEN_GROUPS']))
  $MAXLEN_GROUPS = $_SESSION['MAXLEN_GROUPS'];
else
  $_SESSION['MAXLEN_GROUPS'] = $MAXLEN_GROUPS;

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

/* SET / UNSET COLS DISPLAYED */
if(!isset($_SESSION['NO_COLS'])) {
  foreach($NO_COLS AS $key => $val)
    $_SESSION[$val] = 1;
  $_SESSION['NO_COLS'] = 1;
}
foreach($COLS AS $key => $val) {
  if(isset($_GET['option'])) {
    if(isset($_GET[$key])) {
      unset($COLS[$key]);
      $_SESSION[$key] = 1;
      if (preg_match('/(machine|service|group|stinfo|IP)/',$key))
        $QUERY = preg_replace("/.*define_my_like 'define_my_".$key."_filter'/",' ',$QUERY);
    }
    else if(isset($_SESSION[$key]))
      unset($_SESSION[$key]);
  }
  else if(isset($_SESSION[$key])) {
    unset($COLS[$key]);
    if (preg_match('/(machine|service|group|stinfo|IP)/',$key))
      $QUERY = preg_replace("/.*define_my_like 'define_my_".$key."_filter'/",' ',$QUERY);
  }
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

/* KEEP GET FOR FUTUR LINK */
if (isset($_GET['n']))
  unset($_GET['n']);
if (isset($_GET['option']))
  unset($_GET['option']);
$MY_GET = "?";
if (isset($_GET)) {
  foreach($_GET AS $key => $val) {
    $MY_GET = $MY_GET."&".$key."=".$val;
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

/* FORGE QUERY (AUTO CHANGE LEVEL IN MONITOR MODE) */
$nb_rows = 0;
$level = $LEVEL;
while ( ($nb_rows <= 0) && ($level <= $MAXLEVEL) ) {
  $query = $QUERY;
  $replacement = array (
  'define_my_separator'       =>  mysql_real_escape_string($SEPARATOR, $dbconn),
  'define_my_machine_filter'  =>  mysql_real_escape_string($MY_FILTER, $dbconn),
  'define_my_service_filter'  =>  mysql_real_escape_string($MY_FILTER, $dbconn),
  'define_my_group_filter'    =>  mysql_real_escape_string($MY_FILTER, $dbconn),
  'define_my_stinfo_filter'   =>  mysql_real_escape_string($MY_FILTER, $dbconn),
  'define_my_IP_filter'       =>  mysql_real_escape_string($MY_FILTER, $dbconn),
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
  'define_or_and'             =>  mysql_real_escape_string($MY_ORAND, $dbconn),
  'define_my_like'            =>  mysql_real_escape_string($MY_LIKE, $dbconn),
  'define_sortsensfield'      =>  mysql_real_escape_string($SORTORDERFIELD, $dbconn),
  'define_sortfield'          =>  mysql_real_escape_string($SORTFIELD, $dbconn),
  'define_first'              =>  mysql_real_escape_string($FIRST, $dbconn),
  'define_step'               =>  mysql_real_escape_string($LINE_BY_PAGE, $dbconn),
  ) ;

  foreach($replacement AS $replace => $val)
    $query = str_replace($replace, $val, $query);

  /*
  echo "<pre>";
  echo $query;
  echo "</pre>";
  */

  $query_start = getmicrotime();
  if (!($rep = mysql_query($query, $dbconn))) {
    $errno = mysql_errno($dbconn);
    $txt_error = mysql_error($dbconn);
    error_log("invalid query : ".$errno." : ".$txt_error);
    die_refresh("invalid query") ;
  }
  $query_time = getmicrotime() - $query_start;
  $str_query_time = '%01.4f s';
  if ( (($nb_rows = mysql_num_rows($rep)) > 0) || (!isset($_GET['monitor'])) )
    break;
  else
    select_level($level++);
}

$array_total_rows = mysql_fetch_row( mysql_query( "SELECT FOUND_ROWS( )", $dbconn ) );
$total_rows       = $array_total_rows[0];

/* PREPARE DISPLAY */
$hit_ok        = 0;
$hit_warning   = 0; 
$hit_critical  = 0;
$hit_unknown   = 0;
$hit_down      = 0;
$hit_ack       = 0;
$hit_any       = 0;
$line          = 1;

while ($data = mysql_fetch_array($rep, MYSQL_ASSOC) ) {
  switch($data['STATUS']) {
    case 0: $hit_ok++;       break;
    case 1: $hit_warning++;  break;
    case 2: $hit_critical++; break;                                                      
    case 3: $hit_unknown++;  break;
  }
  if ($data['ACK'] == 1) $hit_ack++;
  if ($data['DOWNTIME'] > 0) $hit_down++;
  $hit_any++;
}
$hit_any = $total_rows;

if      ($hit_critical > 0) $framecolor = $CRITICAL;
else if ($hit_warning > 0)  $framecolor = $WARNING;
else if ($hit_unknown > 0)  $framecolor = $UNKNOWN;
else if ($hit_ok > 0)       $framecolor = $OK;
else                        $framecolor = $OTHER;

if ($nb_rows > 0)
  mysql_data_seek($rep, 0);

require_once("header.php");

if (isset($_GET['monitor'])) 
  unset($COLS['checkbox']);
else {
  require_once("action.php");
}
require_once("alert.php");
require_once("footer.php");

mysql_close($dbconn);
mysql_free_result($rep);

?>
