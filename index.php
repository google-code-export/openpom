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
session_cache_limiter('nocache');
session_set_cookie_params('864000');
session_start();
if (isset($_SERVER['REMOTE_USER'])) 
  $_SESSION['USER'] = strip_tags(addslashes(htmlspecialchars($_SERVER['REMOTE_USER']))) ;
else 
  die("no user");

require_once("query.php");
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
  die("cannot connect to db") ;

if (!mysql_select_db($SQL_DB, $dbconn)) 
  die("cannot select db");

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

/* FORCE CLEAN MODE MONITOR (NO FILTER, NO SORT, DEFAULT LEVEL ...)*/
if (isset($_GET['monitor'])) {
  foreach($_GET AS $key => $val)
    if ($key != "monitor")
      unset($_GET[$key]);
  unset($_POST);
}

/* PROCESS POST DATA AND SEND CMD TO NAGIOS */
if ( (isset($_POST['ack']))     ||
     (isset($_POST['down']))    ||
     (isset($_POST['recheck'])) ||
     (isset($_POST['disable'])) ||
     (isset($_POST['reset']))   ||
     (isset($_POST['comment_persistent'])) )
  post_data_to_cmd($dbconn);

/* INIT DEFAULT VALUES (see config.php for other) */
$SEPARATOR       = ", ";               //SEPARATOR FOR GROUPS
$MY_FILTER       = "%%";               //NO FILTER
$MY_USER         = $_SESSION['USER'];  //USER VIEW
$MY_SVCFILT      = "1,2,3";            //STATUS SVC FILTER
$MY_HOSTFILT     = "1,2";              //STATUS HOST FILTER
$MY_SVCACKLIST   = 0;                  //SVC ACKNOWLEDGE
$MY_HOSTACKLIST  = 0;                  //HOST ACKNOWLEDGE
$MY_HOSTDOWNLIST = 0;                  //HOST DOWNTIME
$MY_SVCDOWNLIST  = 0;                  //SVC DOWNTIME
$MY_ACKLIST      = 0;                  //DOWNTIME AND ACK SVC FOR ACK AND DOWNTIME HOST
$MY_NOSVC        = 1;                  //NO SVC FOR CRITICAL HOST
$MY_ORAND        = "OR";               //FILTER CONDITION
$MY_LIKE         = "LIKE";             //FILTER RESTRICTION
$SORTFIELD       = "COEF, DURATION";   //SORT FIELD 
$SORTORDERFIELD  = "ASC";              //SORT ORDER
$FIRST           = "0";                //FIRST GET ROW 
$FILTER          = '';                 //NO FILTER

/* PROCESS GET DATA */

/* GET DEFAULT LEVEL */
if ( (isset($_GET['defaultlevel'])) && (is_numeric($_GET['defaultlevel'])) &&
     ($_GET['defaultlevel'] > 0) && ($_GET['defaultlevel'] < 7) ) {
  $LEVEL = substr($_GET['defaultlevel'], 0, 1);
  $_SESSION['LEVEL'] = $LEVEL;
}
else if (isset($_SESSION['LEVEL']))
  $LEVEL = $_SESSION['LEVEL'];
else
  $_SESSION['LEVEL'] = $LEVEL;

/* SELECT LEVEL */
if ( (isset($_GET['level'])) && (is_numeric($_GET['level'])) &&
  ($_GET['level'] > 0) && ($_GET['level'] < 7) ) 
  $LEVEL = substr($_GET['level'], 0, 1);

select_level($LEVEL);

/* FORGE FILTER */
if (isset($_GET['clear'])) { 
  unset($_GET['filter']);
  if (isset($_GET['filtering'])) unset($_GET['filtering']);
}
else if ( (isset($_GET['filtering'])) && (strlen($_GET['filtering'] < 2)) ) {
  unset($_GET['filtering']);
  unset($_GET['filter']);
}
if (isset($_GET['filter'])) {
  if ( (isset($_GET['filtering'])) && (!isset($_GET['clear'])) ) {
    if (strlen($_GET['filtering']) > 100) 
      die("filter too long");
    foreach(str_split($ILLEGAL_CHAR) AS $char) {
      $pos = strpos($_GET['filtering'], $char);
      if ( ($pos === 0) || ($pos > 0) )
        die("invalid char in filter");
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

/* GET MAX CHARACTER PER TD */
if ( (isset($_GET['maxlentd'])) && (is_numeric($_GET['maxlentd'])) &&
     ($_GET['maxlentd'] > 0) && ($_GET['maxlentd'] < 1000) ) {
  $MAX_LEN_TD = $_GET['maxlentd'];
  $_SESSION['MAXLENTD'] = $MAX_LEN_TD;
}
else if (isset($_SESSION['MAXLENTD']))
  $MAX_LEN_TD = $_SESSION['MAXLENTD'];
else
  $_SESSION['MAXLENTD'] = $MAX_LEN_TD;

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
if (!isset($_SESSION['COLS'])) 
  $_SESSION['COLS'] = "";
$no_cols = array('flag', 'duration', 'last', 'stinfo', 'group');
foreach($no_cols AS $col) {
  $pattern = ",".$col.",";
  $no_filt_group = '/define_or_and[[:space:]]+OHG.name1[[:space:]]+define_my_like.*/'; 
  if (isset($_GET[$col])) {
    unset($COLS[$col]);
    $QUERY = preg_replace($no_filt_group,' ',$QUERY);
    if (!preg_match($pattern,$_SESSION['COLS']))
      $_SESSION['COLS'] .= $pattern;
  }
  else if (isset($_GET['option']))
    $_SESSION['COLS'] = str_replace($pattern,'',$_SESSION['COLS']);
  else if (preg_match($pattern,$_SESSION['COLS'])) {
    unset($COLS[$col]);
    $QUERY = preg_replace($no_filt_group,' ',$QUERY);
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
while ( ($nb_rows <= 0) && ($level < 7) ) {
  $query = $QUERY;
  $replacement = array (
  'define_my_separator'    =>  mysql_real_escape_string($SEPARATOR, $dbconn),
  'define_my_filter'       =>  mysql_real_escape_string($MY_FILTER, $dbconn),
  'define_my_user'         =>  mysql_real_escape_string($MY_USER, $dbconn),
  'define_my_svcfilt'      =>  mysql_real_escape_string($MY_SVCFILT, $dbconn),
  'define_my_svcacklist'   =>  mysql_real_escape_string($MY_SVCACKLIST, $dbconn),
  'define_my_hostacklist'  =>  mysql_real_escape_string($MY_HOSTACKLIST, $dbconn),
  'define_my_hostdownlist' =>  mysql_real_escape_string($MY_HOSTDOWNLIST, $dbconn),
  'define_my_svcdownlist'  =>  mysql_real_escape_string($MY_SVCDOWNLIST, $dbconn),
  'define_my_acklist'      =>  mysql_real_escape_string($MY_ACKLIST, $dbconn),
  'define_my_nosvc'        =>  mysql_real_escape_string($MY_NOSVC, $dbconn),
  'define_my_hostfilt'     =>  mysql_real_escape_string($MY_HOSTFILT, $dbconn),
  'define_or_and'          =>  mysql_real_escape_string($MY_ORAND, $dbconn),
  'define_my_like'         =>  mysql_real_escape_string($MY_LIKE, $dbconn),
  'define_sortsensfield'   =>  mysql_real_escape_string($SORTORDERFIELD, $dbconn),
  'define_sortfield'       =>  mysql_real_escape_string($SORTFIELD, $dbconn),
  'define_first'           =>  mysql_real_escape_string($FIRST, $dbconn),
  'define_step'            =>  mysql_real_escape_string($LINE_BY_PAGE, $dbconn),
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
    die("invalid query") ;
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
  if ($data['DOWNTIME'] == 1) $hit_down++;
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
  require_once("filter.php");
  require_once("action.php");
}
require_once("alert.php");
require_once("footer.php");

mysql_close($dbconn);
mysql_free_result($rep);

?>
