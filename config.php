<?php
/*
  OpenPom $Revision$
  $HeadURL$
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
 
  $Date$
*/

/* NAGIOS or ICINGA */
$BACKEND            = "nagios" ;
//$BACKEND            = "icinga" ;

/* SQL VARIABLE */
$SQL_HOST           = "127.0.0.1";
$SQL_USER           = "openpom";
$SQL_PASSWD         = "my_password";
$SQL_DB             = "ndoutils";

/* PAGE REFRESH TIME */
$REFRESHTIME        = 60;
/* MAX LINE PER PAGE AND LIMIT SQL REQUEST */
$LINE_BY_PAGE       = 100;
/* WIDTH MAX FOR A TD */
$MAX_LEN_TD         = 50;
/* NUMBER OF CARACTERS MAX FOR OUTPUT */
$SPLITAT            = 100;
/* SHOW THE COLOR FRAME AROUND THE PAGE */
$FRAME              = 0;
/* FONT SIZE FOR ALERT */
$FONT_SIZE          = 12;

//$LEVEL              = 1; /* SHOW CRITICAL ONLY */
$LEVEL              = 2; /* LEVEL 2 SHOW CRITICAL WARNING AND UNKNOWN */
//$LEVEL              = 3; /* LEVEL 3 SHOW CRITICAL WARNING UNKNOWN AND SOFT */
//$LEVEL              = 4; /* SHOW LEVEL 4 AND ACK AND DOWNTIME */
//$LEVEL              = 5; /* SHOW LEVEL 5 AND OUTAGE */
//$LEVEL              = 6; /* SHOW LEVEL 6 AND SVC FOR ACK/DOWNTIME HOST */
//$LEVEL              = 7; /* LEVEL 7 SHOW ALL */
$MAXLEVEL             = 7;

/* TABLE ALERT COLUMNS ORDER */
$COLS               = array(
"checkbox"          => 'none',
"flag"              => 'COEF',
"duration"          => 'DURATION',
"machine"           => 'MACHINE_NAME',
"service"           => 'SERVICES' ,
"stinfo"            => 'OUTPUT',
"group"             => 'GROUPE',
"last"              => 'LASTCHECK',
);

/* 
  ALERT COLOR  you must define the new color in style.css (tr.color) 
  red yellow orange green black
*/
$CRITICAL           = "red";
$WARNING            = "yellow";
$OK                 = "green";
$UNKNOWN            = "orange";
$OTHER              = "white";
$TRACK              = "blue";


/*
  set default lang (see supported lang in lang.php) 
  default lang is en or
  call nox/index.php?lang=xx  where xx is the country abreviation code
*/
$MYLANG             = "en"; 

/* OTHER VARIABLE */
$VERSION            = "1.0.9";
$CODENAME           = "OpenPom";

/* NAGIOS AND ICINGA VARIABLES */
$EXEC_CMD           = "./send-order";
$EXEC_PARAM         = "";
$SUDO_EXEC          = "/usr/bin/sudo";
$SUDO_PARAM         = "";
$CMD_FILE           = "/usr/local/nagios/var/rw/nagios.cmd";
//$CMD_FILE           = "/var/lib/icinga/rw/icinga.cmd";
$LINK               = "/".$BACKEND."/cgi-bin/extinfo.cgi";

/* SHOW GRPAH FROM EXTERNAL SOURCE ON STATUS */
$GRAPH_WIDTH        = 550;
$GRAPH_HEIGHT       = 300;
$GRAPH_STATUS       = "".$BACKEND."/cgi-bin/trends.cgi?createimage&host=_HOSTNAME_&service=_SERVICE_&backtrack=4&zoom=4";
/* SHOW GRAPH ICON ON ALERT (open a popup with external graph) */
$GRAPH_POPUP        = "".$BACKEND."/cgi-bin/trends.cgi?createimage&t1=".strtotime("-10 day")."&t2=".time()."&host=_HOSTNAME_&service=_SERVICE_&backtrack=4&zoom=4";


/* ILLEGAL_CHAR IN POST / GET DATA */
$ILLEGAL_CHAR       = "`~!$%^&*|'\"<>?(),;"; 

/* ACKNOWLEDGEMENT */
$EXT_CMD['ack']['host'][0]      = array(
'ACKNOWLEDGE_HOST_PROBLEM',
'$host',
'1;1;0',
'$user',
'$comment');

$EXT_CMD['ack']['svc'][0]       = array(
'ACKNOWLEDGE_SVC_PROBLEM',
'$host',
'$svc',
'1;1;0',
'$user',
'$comment');

/* DOWNTIME */
$EXT_CMD['down']['host'][0]     = array(
'SCHEDULE_HOST_DOWNTIME',
'$host',
'$now',
'$end_time',
'1;0',
'$time',
'$user',
'$comment');

$EXT_CMD['down']['svc'][0]      = array(
'SCHEDULE_SVC_DOWNTIME',
'$host',
'$svc',
'$now',
'$end_time',
'1;0',
'$time',
'$user',
'$comment');

/* RECHECK */
$EXT_CMD['recheck']['host'][0]  = array(
'SCHEDULE_FORCED_HOST_SVC_CHECKS',
'$host',
'$next',
); 
$EXT_CMD['recheck']['svc'][0]   = array(
'SCHEDULE_FORCED_SVC_CHECK',
'$host',
'$svc',
'$next',
);

/* RESET STATE */
$EXT_CMD['reset']['host'][0]    = array(
'REMOVE_HOST_ACKNOWLEDGEMENT',
'$host');

$EXT_CMD['reset']['host'][1]    = array(
'ENABLE_HOST_NOTIFICATIONS',
'$host');

$EXT_CMD['reset']['host'][2]    = array(
'ENABLE_HOST_SVC_NOTIFICATIONS',
'$host');

$EXT_CMD['reset']['host'][3]    = array(
'DEL_HOST_DOWNTIME',
'$downtime_id');

$EXT_CMD['reset']['host'][4]    = array(
'DEL_ALL_HOST_COMMENTS',
'$host');

$EXT_CMD['reset']['svc'][0]     = array(
'REMOVE_SVC_ACKNOWLEDGEMENT',
'$host',
'$svc');

$EXT_CMD['reset']['svc'][1]     = array(
'ENABLE_SVC_NOTIFICATIONS',
'$host',
'$svc');

$EXT_CMD['reset']['svc'][2]    = array(
'DEL_SVC_DOWNTIME',
'$downtime_id');

$EXT_CMD['reset']['svc'][3]    = array(
'DEL_ALL_SVC_COMMENTS',
'$host',
'$svc');

/* DISABLE NOTIFICATION */
$EXT_CMD['disable']['host'][0]  = array(
'DISABLE_HOST_NOTIFICATIONS',
'$host');

$EXT_CMD['disable']['svc'][0]   = array(
'DISABLE_SVC_NOTIFICATIONS',
'$host',
'$svc');

/* ADD COMMENT */
$EXT_CMD['comment_persistent']['host'][0]  = array(
'ADD_HOST_COMMENT',
'$host',
'1',
'$user',
'$comment');

$EXT_CMD['comment_persistent']['svc'][0]  = array(
'ADD_SVC_COMMENT',
'$host',
'$svc',
'1',
'$user',
'$comment');


?>
