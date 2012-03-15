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

require_once("config.php");
require_once("query.php");
require_once("query-downtime.php");
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
  unset($_GET['step']);
  unset($_GET['defaultlevel']);
  unset($_GET['maxlen_stinfo']);
  unset($_GET['maxlen_host']);
  unset($_GET['maxlen_svc']);
  unset($_GET['maxlen_groups']);
  unset($_GET['frame']);
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
$query_global_notif = "SELECT notifications_enabled FROM nagios_programstatus ;";
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
$SEPARATOR         = ", ";               //SEPARATOR FOR GROUPS
$MY_USER           = $_SESSION['USER'];  //USER VIEW
$MY_SVCFILT        = "1,2,3";            //STATUS SVC FILTER
$MY_HOSTFILT       = "1";                //STATUS HOST FILTER
$MY_SVCACKLIST     = 0;                  //SVC ACKNOWLEDGE
$MY_HOSTACKLIST    = 0;                  //HOST ACKNOWLEDGE
$MY_HOSTDOWNOP     = '=';                //HOST DOWNTIME DEPTH COMPARISON OPERATOR
$MY_HOSTDOWNVAL    = 0;                  //HOST DOWNTIME DEPTH COMPARED VALUE
$MY_SVCDOWNOP      = '=';                //SVC DOWNTIME DEPTH COMPARISON OPERATOR
$MY_SVCDOWNVAL     = 0;                  //SVC DOWNTIME DEPTH COMPARED VALUE
$MY_ACKLISTOP      = '=';                //DOWNTIME AND ACK SVC FOR ACK AND DOWNTIME HOST, COMP OP
$MY_ACKLISTVAL     = 0;                  //DOWNTIME AND ACK SVC FOR ACK AND DOWNTIME HOST, COMP VAL
$MY_NOSVC          = 1;                  //NO SVC FOR CRITICAL HOST
$MY_DISABLE        = 1;                  //DISABLE ALERT ARE TREATED LIKE ACK AND DOWN
$MY_SOFT           = 0;                  //SOFT ALERTS (0 print soft alerts)
$SORTFIELD         = "COEF, DURATION";   //SORT FIELD 
$SORTORDERFIELD    = "ASC";              //SORT ORDER
$FIRST             = "0";                //FIRST GET ROW 
$MY_SEARCH['host'] = '';                 //SEARCH HOST PARAMETER
$MY_SEARCH['svc']  = '';                 //SEARCH SVC PARAMETER
$FILTER            = '';                 //FILTER BY DEFAULT
$MY_CHECK_DISABLE  = "0,1";              //DISABLED CHECK
$MY_TRACK_ANY      = 0 ;                 //TRACK ANYTHING

/* Search filtering */
$SFILTER['svc'] = array (
  'H.display_name' => "h:",
  "S.display_name" => "s:",
  "H.address"      => "i:",
  "OHG.name1"      => "g:",
  "SS.output"      => "o:"
) ;

$SFILTER['host'] = array (
  'H.display_name' => "h:",
  "'--host--'"     => "s:",
  "H.address"      => "i:",
  "OHG.name1"      => "g:",
  "HS.output"      => "o:"
) ;


/* PROCESS GET DATA */

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
  $FILTER = $_GET['filtering'] ;
  if (preg_match_all('/([!]?[hsigo]{1}:)+([a-zA-Z0-9@*()\._-]+)?[ ]?([&|]{1}){0,}[ ]?/', $_GET['filtering'], $keyword)) {
    foreach ( array('host', 'svc') AS $f ) {
      foreach ($keyword[1] AS $k => $v) {
        foreach ($SFILTER[$f] AS $t => $s) {
          if ( ( ($s == $v) || ($v == "!".$s) ) && (isset($keyword[2][$k])) ) {
            if ( ($k > 0) && (isset($keyword[3][$k-1])) ) {
              if ( $keyword[3][$k-1] == "|" ) $MY_SEARCH[$f] .= " OR " ;
              else $MY_SEARCH[$f] .= " AND " ;
            }
            if ($v == "!".$s) 
              $MY_SEARCH[$f] .= $t." NOT LIKE '".mysql_real_escape_string($keyword[2][$k], $dbconn)."' " ;
            else
              $MY_SEARCH[$f] .= $t." LIKE '".mysql_real_escape_string($keyword[2][$k], $dbconn)."' " ;
            $MY_SEARCH[$f] = str_replace("*", "%", $MY_SEARCH[$f]) ;
          }
        }//end foreach
      }//end foreach
      if ( $MY_SEARCH[$f] != "" )
        $MY_SEARCH[$f] = " ( ".$MY_SEARCH[$f]." ) AND " ;
    }//end foreach
  } //End if advanced search
  else {
    $MY_FILTER = "'%".mysql_real_escape_string($FILTER, $dbconn)."%'" ;
    $MY_FILTER = str_replace('*', '%', $MY_FILTER) ;
    if (substr($MY_FILTER, 0, 3) == "'%!") $MY_LIKE = "NOT LIKE" ;
    else $MY_LIKE = "LIKE" ;
    $MY_FILTER = str_replace('!', '', $MY_FILTER) ;
    $sub_cols_in_search['host'] = array (
      "group"   => "OR  OHG.name1       ".$MY_LIKE."  ".$MY_FILTER, 
      "IP"      => "OR  H.address       ".$MY_LIKE."  ".$MY_FILTER,
      "stinfo"  => "OR  HS.output       ".$MY_LIKE."  ".$MY_FILTER,
      "service" => "OR  '--host--'      ".$MY_LIKE."  ".$MY_FILTER,
    ) ;
    $sub_cols_in_search['svc']  = array (
      "group"   => "OR  OHG.name1       ".$MY_LIKE."  ".$MY_FILTER, 
      "IP"      => "OR  H.address       ".$MY_LIKE."  ".$MY_FILTER,
      "stinfo"  => "OR  SS.output       ".$MY_LIKE."  ".$MY_FILTER,
      "service" => "OR  S.display_name  ".$MY_LIKE."  ".$MY_FILTER,
    ) ;
    foreach (array('host', 'svc') AS $f ) {
      $MY_SEARCH[$f] = "(      H.display_name  ".$MY_LIKE."  ".$MY_FILTER ;
      foreach ($sub_cols_in_search[$f] AS $k => $v) {
        if (!isset($_SESSION[$k]))
          $MY_SEARCH[$f] .= $v ;
      } //end foreach
      $MY_SEARCH[$f] .= " ) AND " ;
    } //end foreach
  } //end else
} //end if (FILTERING)

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

/* SET / UNSET COLS DISPLAYED */
if(!isset($_SESSION['NO_COLS'])) {
  foreach($NO_COLS AS $key => $val)
    $_SESSION[$val] = 1;
  $_SESSION['NO_COLS'] = 1;
}
foreach($COLS AS $key => $val) {
  if(isset($_GET['option'])) {
    if(!isset($_GET[$key]) && $key != 'machine') {
      unset($COLS[$key]);
      $_SESSION[$key] = 1;
      if ( (isset($MY_FILTER)) && (preg_match('/(service|group|stinfo|IP)/',$key)) ) {
        $MY_SEARCH['svc']  = str_replace($sub_cols_in_search['svc'][$key], ' ', $MY_SEARCH['svc']) ;
        $MY_SEARCH['host'] = str_replace($sub_cols_in_search['host'][$key], ' ', $MY_SEARCH['host']) ;
      }
    }
    else if(isset($_SESSION[$key]))
      unset($_SESSION[$key]);
  }
  else if(isset($_SESSION[$key]) && $key != 'machine') {
    unset($COLS[$key]);
    if ( (isset($MY_FILTER)) && (preg_match('/(service|group|stinfo|IP)/',$key)) ) {
      $MY_SEARCH['svc']  = str_replace($sub_cols_in_search['svc'][$key], ' ', $MY_SEARCH['svc']) ;
      $MY_SEARCH['host'] = str_replace($sub_cols_in_search['host'][$key], ' ', $MY_SEARCH['host']) ;
    }
  }
}

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

/* QUERY AND SET GLOBAL COUNTER */
if (!($rep_glob = mysql_query($QUERY_GLOBAL_COUNT, $dbconn))) {
  $errno = mysql_errno($dbconn);
  $txt_error = mysql_error($dbconn);
  error_log("invalid query : ".$errno." : ".$txt_error);
  die_refresh("invalid query") ;
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

while ($glob_counter = mysql_fetch_array($rep_glob, MYSQL_ASSOC) ) {
  if      ( $glob_counter['STATE']  == 3 ) $glob_unknown  += $glob_counter['NSTATE'] ;
  else if ( $glob_counter['STATE']  == 2 ) $glob_critical += $glob_counter['NSTATE'] ;
  else if ( $glob_counter['STATE']  == 1 ) $glob_warning  += $glob_counter['NSTATE'] ;
  else if ( $glob_counter['STATE']  == 0 ) $glob_ok       += $glob_counter['NSTATE'] ;
  if      ( $glob_counter['ACK']    == 1 ) $glob_ack      += $glob_counter['NACK'] ;
  if      ( $glob_counter['DOWN']   == 1 ) $glob_down     += $glob_counter['NDOWN'] ;
  if      ( $glob_counter['NOTIF']  == 0 ) $glob_notif    += $glob_counter['NNOTIF'] ;
  if      ( $glob_counter['SCHECK'] == 0 ) $glob_check    += $glob_counter['NCHECK'] ;
  if      ( $glob_counter['NSTATE'] >  0 ) $glob_all      += $glob_counter['NSTATE'] ;
}

/* FORGE QUERY (AUTO CHANGE LEVEL IN MONITOR MODE) */
$nb_rows = 0;
$level = $LEVEL;
while ( ($nb_rows <= 0) && ($level <= $MAXLEVEL) ) {
  $query = $QUERY;
  $replacement = array (
  'define_my_separator'       =>  mysql_real_escape_string($SEPARATOR, $dbconn),
  'define_my_host_search'     =>  $MY_SEARCH['host'],
  'define_my_svc_search'      =>  $MY_SEARCH['svc'],
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
  'define_sortsensfield'      =>  mysql_real_escape_string($SORTORDERFIELD, $dbconn),
  'define_sortfield'          =>  mysql_real_escape_string($SORTFIELD, $dbconn),
  'define_first'              =>  mysql_real_escape_string($FIRST, $dbconn),
  'define_step'               =>  mysql_real_escape_string($LINE_BY_PAGE, $dbconn),
  'define_my_check_disable'   =>  mysql_real_escape_string($MY_CHECK_DISABLE, $dbconn),
  'define_track_anything'     => $MY_TRACK_ANY,
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
  $str_query_time = '%01.4fs';
  if ( (($nb_rows = mysql_num_rows($rep)) > 0) || (!isset($_GET['monitor'])) )
    break;
  else
    select_level($level++);
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
$hit_any      = 0;
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
  if ( ($data['DISABLECHECK'] == 0) && ($data['CHECKTYPE'] == 0) ) $hit_check++;
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

if (isset($_GET['monitor'])) unset($COLS['checkbox']);
require_once("action.php");
require_once("alert.php");
require_once("footer.php");

mysql_close($dbconn);
mysql_free_result($rep);

?>
