<?php
/*
  OpenPOM

  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
*/

/* NAGIOS or ICINGA */
$BACKEND            = "nagios";
//$BACKEND          = "icinga";

/* SQL VARIABLE */
$SQL_HOST           = "127.0.0.1";
$SQL_USER           = "openpom";
$SQL_PASSWD         = "my_password";
$SQL_DB             = "ndoutils";

$REFRESHTIME        = 60;   /* PAGE REFRESH TIME */
$LINE_BY_PAGE       = 100;  /* MAX LINE PER PAGE AND LIMIT SQL REQUEST */
$FRAME              = 0;    /* SHOW THE COLOR FRAME AROUND THE PAGE */
$FONT_SIZE          = 11;   /* FONT SIZE FOR ALERT */
$POPIN              = 1;    /* SHOW POPIN ON ROW MOUSE OVER */

//$LEVEL              = 1; /* SHOW CRITICAL ONLY */
$LEVEL                = 2; /* LEVEL 2 SHOW CRITICAL WARNING AND UNKNOWN */
//$LEVEL              = 3; /* LEVEL 3 SHOW CRITICAL WARNING UNKNOWN AND SOFT */
//$LEVEL              = 4; /* SHOW LEVEL 4 AND ACK AND DOWNTIME */
//$LEVEL              = 5; /* SHOW LEVEL 5 AND OUTAGE */
//$LEVEL              = 6; /* SHOW LEVEL 6 AND SVC FOR ACK/DOWNTIME HOST */
//$LEVEL              = 7; /* LEVEL 7 SHOW ALL */
//$LEVEL              = 8; /* LEVEL 8 SHOW ONLY ACKNOWLEDGE */
//$LEVEL              = 9; /* LEVEL 9 SHOW ONLY DOWNTIME */
//$LEVEL              = 10; /* LEVEL 10 SHOW ONLY DISABLE NOTIFICATION */
//$LEVEL              = 11; /* LEVEL 11 SHOW ONLY CRITICAL HOST AND SERVICE SOFT AND HARD */
//$LEVEL              = 12; /* LEVEL 12 SHOW ONLY WARNING SERVICE SOFT AND HARD */
//$LEVEL              = 13; /* LEVEL 13 SHOW ONLY UNKNOWN SERVICE SOFT AND HARD */
//$LEVEL              = 14; /* LEVEL 14 SHOW ONLY OK */
$MAXLEVEL             = 14;

/* Service name given to host entries */
$HOST_SERVICE = '--host--';

/* Default column to use for sorting */
$SORTCOL = 'flags';

/* Default sort direction: 0 column default, 1 reverse */
$SORTDIR = 0;

/* Columns definition */
define('COL_MUST_DISPLAY',      0x0001);    /* column must be displayed no matter what */
define('COL_NO_USER_PREF',      0x0002);    /* user cannot turn column on/off */
define('COL_FMT_DURATION',      0x0004);    /* format value as time duration */
define('COL_FILTER_LINK',       0x0008);    /* make filter links, requires a filter property */
define('COL_MULTI',             0x0010);    /* split data, useful for filter links */
define('COL_NO_MONITOR',        0x0020);    /* remove in monitor mode */
define('COL_DATA_FIRST',        0x0040);    /* first non null data */
define('COL_ENABLED',           0x0080);    /* column is turned on by default */

define('COL_FILTER',            0x0100);    /* internal: column is used in filter */
define('COL_DEPEND',            0x0200);    /* internal: column is used by another */

/* Supported types of columns:
 * - Non-SQL custom columns, like "checkbox" or "flags"
 * - SQL columns from union sub-queries, like "hostname"
 * - SQL columns from custom variables, like "client" (exemple below)
 * - SQL columns from expressions put on the main wrap query, like "duration"
 *
 * Non-SQL custom columns:
 * - have a look to query.php for exemples
 * - implement a format_header_<column> function
 * - implement a format_row_<column> function
 * - supported properties: sort, opts
 *
 * SQL columns from union sub-queries:
 * - have a look to sub-queries of $QUERY in query.php
 * - supported properties: sort, opts, data, key, filter, lmax
 *
 * SQL columns from custom variables:
 * - supported properties: opts, lmax, key
 * - overridable auto-defined properties: sort, data, filter
 * - have a look at init_column() in query.php for defaults
 * - provides two SQL column: HCVAR_<column>, SCVAR_<column>
 *
 * SQL columns from expressions:
 * - supported properties: opts, lmax
 * - overridable auto-defined properties: sort, data
 * - have a look at init_column() in query.php for defaults
 * - provides SQL column: EXPR_<column>
 *
 * HCVAR_<column>, SCVAR_<column> and EXPR_<column> SQL columns can be
 * used as intermediate columns. They can be referenced in properties of other
 * columns: sort, expr. Intermediate columns does not necessarily need to
 * present in the $COLUMN_ENABLED array.
 *
 * Property sort:
 * Array of ORDER BY specifications: first element is an SQL column or an
 * SQL expression, second element is ASC or DESC.
 *
 * Property opts:
 * Bit field or OR'ed COL_* constants, see above.
 *
 * Property data:
 * The SQL column to use as data value for the column. If an array is provided,
 * the first non-NULL value is used. This array mode is tipically used to
 * display custom variable values: either the one on the service or the host.
 *
 * Property key:
 * Sortcut for filters to address a particular column.
 *
 * Property filter:
 * For each sub-query, the SQL column to use in order to construct a WHERE
 * statement coresponding to the filter. Table aliases depends on the names
 * of $QUERY in query.php, that's why it's marked "internal stuff" below.
 *
 * Property lmax:
 * Truncate values of the column to that many characters.
 *
 * Property is_ref:
 * For custom variable columns (require cvar). The value of the custom
 * variable correspond to the name of another custom variable. The later
 * must be unique.
 */
$COLUMN_DEFINITION = array();

/* Internal column for displaying a checkbox in order to allow the user
 * to select some lines in the table. */
$COLUMN_DEFINITION['checkbox'] = array(
    'opts'   => COL_NO_MONITOR|COL_ENABLED,
);

/* Expression column for building a criticity coeficient based on hosts
 * and services status. It is used for sorting in the flags column. */
$COLUMN_DEFINITION['coef'] = array(
    'expr'   => 'CASE STATUS WHEN 3 THEN 1 WHEN 2 THEN -1 WHEN 1 THEN 0 ELSE 10 END',
    'opts'   => COL_NO_USER_PREF
);

/* Column for displaying small icons and flags depending on the status
 * of monitored host and service entries. */
$COLUMN_DEFINITION['flags'] = array(
    'opts'   => COL_ENABLED,
    'sort'   => array(array('EXPR_coef', 'asc'),
                      array('EXPR_duration', 'asc')),
);

/* Expression column for displaying the duration since last state change
 * of a monitored host or service entry. */
$COLUMN_DEFINITION['duration'] = array(
    'expr'   => 'UNIX_TIMESTAMP() - LASTCHANGE',
    'opts'   => COL_FMT_DURATION|COL_ENABLED,
);

/* Column for displaying the host name of an entry. */
$COLUMN_DEFINITION['hostname'] = array(
    'sort'   => array(array('HOSTNAME', 'asc')),
    'data'   => 'HOSTNAME',
    'lmax'   => 30,
    'opts'   => COL_MUST_DISPLAY|COL_FILTER_LINK,
    'key'    => 'h',

    /* internal stuff */
    'filter' => array('define_host_search'  => 'H.display_name LIKE %f',
                      'define_svc_search'   => 'H.display_name LIKE %f'),
);

/* Column for displaying the host address of an entry. */
$COLUMN_DEFINITION['address'] = array(
    'sort'   => array(array('INET_ATON(ADDRESS)', 'asc')),
    'data'   => 'ADDRESS',
    'opts'   => COL_FILTER_LINK,
    'key'    => 'i',

    /* internal stuff */
    'filter' => array('define_host_search'  => 'H.address LIKE %f',
                      'define_svc_search'   => 'H.address LIKE %f'),
);

/* Column for displaying the service name of an entry. */
$COLUMN_DEFINITION['service'] = array(
    'sort'   => array(array('SERVICE', 'asc')),
    'data'   => 'SERVICE',
    'lmax'   => 30,
    'opts'   => COL_MUST_DISPLAY|COL_FILTER_LINK,
    'key'    => 's',

    /* internal stuff */
    'filter' => array('define_host_search'  => "'$HOST_SERVICE' LIKE %f",
                      'define_svc_search'   => 'S.display_name LIKE %f'),
);

/* Column for displaying the last output returned by the plugin of a
 * monitored host or service entry. */
$COLUMN_DEFINITION['output'] = array(
    'sort'   => array(array('OUTPUT', 'asc')),
    'data'   => 'OUTPUT',
    'lmax'   => 50,
    'key'    => 'o',
    'opts'   => COL_ENABLED,

    /* internal stuff */
    'filter' => array('define_host_search'  => 'HS.output LIKE %f',
                      'define_svc_search'   => 'SS.output LIKE %f'),
);

/* Column for displaying the list of host groups an host entry belongs
 * to. The same list is displayed for host services. */
$COLUMN_DEFINITION['groups'] = array(
    'data'   => array('HGROUPALIASES', 'SGROUPALIASES'),
    'lmax'   => 40,
    'opts'   => COL_FILTER_LINK|COL_MULTI,
    'key'    => 'g',

    /* internal stuff */
    'filter' => array('define_host_search'  => 'HGO.name1 LIKE %f OR SGO.name1 LIKE %f',
                      'define_svc_search'   => 'HGO.name1 LIKE %f OR SGO.name1 LIKE %f'),
);

/* Expression column for displaying the duration since last check of a
 * monitored host or service entry. */
$COLUMN_DEFINITION['last'] = array(
    'expr'   => 'UNIX_TIMESTAMP() - LASTCHECK',
    'opts'   => COL_FMT_DURATION|COL_ENABLED,
);

/* HOWTO display a customer name? */

/* Custom variable _CLIENT to display the name of the customer associated
 * with a host or service entry. This column should be displayed and thus
 * added in $COLUMN_ENABLED.
 */
// $COLUMN_DEFINITION['client'] = array(
//     'cvar'   => 'CLIENT',
//     'opts'   => COL_FILTER_LINK,
//     'key'    => 'c',
// );

/* HOWTO display the time left to fix an incident? */

/* Custom variable _SLA which contains a maximum number of seconds for an
 * incident to be resolved. This value can be used in the timeleft column
 * below. This column is not ment to be displayed, so it should not be listed
 * in $COLUMN_ENABLED.
 */
// $COLUMN_DEFINITION['sla'] = array(
//     'cvar'   => 'SLA',
// );

/* Expression column using the _SLA value in order to display the time left
 * for an incident to be resolved. This column should be displayed and thus
 * added in $COLUMN_ENABLED.
 */
// $COLUMN_DEFINITION['timeleft'] = array(
//     'expr'   => 'if(STATUS = 0, NULL, ifnull(SCVAR_sla, HCVAR_sla) - (UNIX_TIMESTAMP() - LASTCHANGE))',
//     'sort'   => array(array('-1 * EXPR_timeleft', 'desc'),
//                       array('EXPR_coef', 'asc'),
//                       array('EXPR_duration', 'asc')),
//     'opts'   => COL_FMT_DURATION,
// );

/* HOWTO sort the flags column according to a custom severity? */

/* Custom variable _SEVERITY which contains a severity number, the greater
 * the most severe. This column is not ment to be displayed, so it should not
 * be listed in $COLUMN_ENABLED.
 */
// $COLUMN_DEFINITION['severity'] = array(
//     'cvar'   => 'SEVERITY',
// );

/* Expression column to define a custom coeficient used in column flags
 * for sorting. This column is not ment to be displayed, so it should not
 * be listed in $COLUMN_ENABLED.
 */
// $COLUMN_DEFINITION['coef2'] = array(
//     'expr'   => 'CASE STATUS WHEN 3 THEN 1 WHEN 2 THEN 3 WHEN 1 THEN 2 ELSE 0 END * 10 +
//                  ifnull(ifnull(SCVAR_severity, HCVAR_severity), 0)',
// );

/* Modify the sort definition of the flags column. This uses the new
 * coeficient defined just above.
 */
// $COLUMN_DEFINITION['flags'] = array(
//     'sort'   => array(array('EXPR_coef2', 'desc'),
//                       array('EXPR_duration', 'asc')),
// );

/* HOWTO display cells with exceeded SLA (timeleft) in black?  */

// function on_alert_row(&$rdata) {
//     if (isset($rdata['EXPR_timeleft']) && $rdata['EXPR_timeleft'] < 0)
//         $rdata['__timeleft_class'] = 'black';
// }

/*
  ALERT COLOR  you must define the new color in style.css (tr.color)
  red yellow orange green black
*/
$CRITICAL           = "red";
$WARNING            = "yellow";
$OK                 = "green";
$UNKNOWN            = "orange";
$OTHER              = "white";
$TRACK_ERROR        = "blue";
$TRACK_OK           = "bluegreen";

/*
  set default lang (supported lang are en, fr and de)
  default lang is "english" or
  try openpom/index.php?lang=xx where xx is the country abreviation code
*/
$MYLANG             = "en";

/* OTHER VARIABLE */
$CODENAME           = "OpenPOM";
$VERSION            = "1.6";
$ENCODING           = "ISO-8859-1";

/* NAGIOS AND ICINGA VARIABLES */
/* escapeshellarg() is called on each element of $*_PARMS arrays */
$EXEC_CMD           = "./send-order";
$EXEC_PARAM         = array();
//$SUDO_EXEC        = "/usr/bin/sudo";
//$SUDO_PARAM         = array('-u', 'admin');
$CMD_FILE           = "/usr/local/nagios/var/rw/nagios.cmd";
//$CMD_FILE         = "/var/lib/icinga/rw/icinga.cmd";
$BASE_URL           = "" ;
$LINK               = "/" . $BACKEND . "/cgi-bin/extinfo.cgi";

/* SEARCH FILTERING */
$QUICKSEARCH = 1 ;  //disabled direct search on click

/* SHOW GRPAH FROM EXTERNAL SOURCE
 *
 * Default images are generated by Nagios' trends.cgi
 * Image at URI defined in $GRAPH_HOST will be displayed for hosts
 * Image at URI defined in $GRAPH_SVC will be displayed for services
 *
 * Keywords:
 * @@define_host@@ will be replaced by the selected host name
 * @@define_service@@ will be replaced by the selected service name
 */
$GRAPH_HOST = '/' . $BACKEND . '/cgi-bin/trends.cgi'
  . '?createimage'
  . '&backtrack=4'
  . '&zoom=4'
  . '&host=@@define_host@@';

$GRAPH_SVC = '/' . $BACKEND . '/cgi-bin/trends.cgi'
  . '?createimage'
  . '&backtrack=4'
  . '&zoom=4'
  . '&host=@@define_host@@'
  . '&service=@@define_service@@';

/* POPUP CONTENT WHEN CLICKING ON THE GRAPH ICON
 *
 * Default is to use the graph.php provided with OpenPOM which display the
 * image defined in $GRAPH_HOST or $GRAPH_SVC (respectively for an host or
 * a service) and a very basic period selector.
 *
 * Periods available are defined in the array $GRAPH_POPUP_PERIODS where each
 * element is an array of 2 elements defining the start and end value of the
 * period. The start/end values must be valid string for passing to PHP's
 * strtotime() function.
 *
 * See PHP documentation for more information:
 * http://www.php.net/manual/en/datetime.formats.php
 *
 * The start/end timestamps are passed to the URI defined in $GRAPH_HOST and
 * $GRAPH_SVC. The name of the parameters used to pass them are defined
 * repectively in $GRAPH_POPUP_PARAM_START and $GRAPH_POPUP_PARAM_END.
 */
$GRAPH_POPUP_HOST = 'graph.php?host=@@define_host@@';
$GRAPH_POPUP_SVC = 'graph.php?host=@@define_host@@&service=@@define_service@@';
$GRAPH_POPUP_WIDTH = 800;
$GRAPH_POPUP_HEIGHT = 400;

$GRAPH_POPUP_PERIODS = array(
  'Day'   => array('-1 day',    'now'),
  'Week'  => array('-1 week',   'now'),
  'Month' => array('-1 month',  'now'),
  'Year'  => array('-1 year',   'now'));

$GRAPH_POPUP_DEFAULT = 'Week';
$GRAPH_POPUP_PARAM_START = 't1';
$GRAPH_POPUP_PARAM_END = 't2';

/* ILLEGAL_CHAR IN POST / GET DATA
 *
 * the following characters should always be included:
 * - semicolon ";" field separator used by Nagios in commands
 * - tild "~" special comment prefix used for disable and track
 */
$ILLEGAL_CHAR       = '`~$^<>';

/* POPIN WIDTH RESTRICTION */
$POPIN_INITIAL_WIDTH = 500;
$POPIN_FIT_TO_GRAPH_WIDTH = true;

/* POPUP STATUS SIZE */
$STATUS_POPUP_WIDTH  = 600 ;
$STATUS_POPUP_HEIGHT = 500 ;

/* ELEMENT SHOWED ON STATUS POPIN 0 => DO NOT DISPLAY*/
$SHOWSTATUSGRAPH   = 1 ; /*SHOW GRAPH*/
$SHOWSTATUSALL     = 1 ; /*SHOW ELEMENT*/
$SHOWSTATUSLIMIT   = 5 ; /*SHOW NB ELEMENT*/

/* 0 => HIDE , 1 => HIDE OR SHOWED (SEE LIMIT), 2 => ALWAYS SHOWED */
$STATUSPOPIN = array (
  'curstat'      => 1,
  'outputstatus' => 1,
  'checkstatus'  => 1,
  'lastok'       => 1,
  'nextcheck'    => 1,
  'checkinfo'    => 1,
  'checktime'    => 1,
  'laststatus'   => 1,
  'flapping'     => 1,
  'groupstatus'  => 1,
  'notifystatus' => 1,
  'ackcur'       => 2,
  'downcur'      => 2,
  'notifycur'    => 2,
  'disacur'      => 2,
  'commentcur'   => 2,
  'linkhistory'  => 2,
) ;

/* List of custom variales to display is status popin */
$SHOWSTATUSCVAR = array('_PROCEDURE', '_REMOTE_CONNECT');

/* Nagios/Icinga global logs javascript button onclick action */
$GLOBAL_LOGS_ONCLICK = "pop('./showlog.php', 'nagios_log', 700, 600);";

/* POPUP HISTORY SIZE */
$HISTORY_POPUP_WIDTH  = 760 ;
$HISTORY_POPUP_HEIGHT = 450 ;

/* ELEMENT SHOWED IN HISTORY AND ORDER 0 => DO NOT DISPLAY */
$HISTORY = array(
'ack'    => 1,
'down'   => 1,
'com'    => 1,
'notify' => 0,
'state'  => 1,
'flap'   => 1,
) ;

/* ACKNOWLEDGEMENT */
$EXT_CMD['ack']['host'][0]      = array(
'ACKNOWLEDGE_HOST_PROBLEM',
'$host',
'1;1;1',
'$user',
'$comment');

$EXT_CMD['ack']['svc'][0]       = array(
'ACKNOWLEDGE_SVC_PROBLEM',
'$host',
'$svc',
'1;1;1',
'$user',
'$comment');

/* DOWNTIME */
$EXT_CMD['down']['host'][0]     = array(
'SCHEDULE_HOST_DOWNTIME',
'$host',
'$start_time',
'$end_time',
'1;0',
'7200',
'$user',
'$comment');

$EXT_CMD['down']['svc'][0]      = array(
'SCHEDULE_SVC_DOWNTIME',
'$host',
'$svc',
'$start_time',
'$end_time',
'1;0',
'7200',
'$user',
'$comment');

/* RECHECK */
$EXT_CMD['recheck']['host'][0]  = array(
'SCHEDULE_FORCED_HOST_CHECK',
'$host',
'$next',
);
$EXT_CMD['recheck']['host'][1]  = array(
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
'DEL_ALL_HOST_COMMENTS',
'$host');

$EXT_CMD['reset']['host'][1]    = array(
'REMOVE_HOST_ACKNOWLEDGEMENT',
'$host');

$EXT_CMD['reset']['host'][2]    = array(
'ENABLE_HOST_NOTIFICATIONS',
'$host');

$EXT_CMD['reset']['host'][3]    = array(
'DEL_HOST_DOWNTIME',
'$downtime_id');

$EXT_CMD['reset']['host'][4]  = array(
'ENABLE_HOST_CHECK',
'$host');

$EXT_CMD['reset']['svc'][0]    = array(
'DEL_ALL_SVC_COMMENTS',
'$host',
'$svc');

$EXT_CMD['reset']['svc'][1]     = array(
'REMOVE_SVC_ACKNOWLEDGEMENT',
'$host',
'$svc');

$EXT_CMD['reset']['svc'][2]     = array(
'ENABLE_SVC_NOTIFICATIONS',
'$host',
'$svc');

$EXT_CMD['reset']['svc'][3]    = array(
'DEL_SVC_DOWNTIME',
'$downtime_id');

$EXT_CMD['reset']['svc'][4]  = array(
'ENABLE_SVC_CHECK',
'$host',
'$svc');

/* DISABLE NOTIFICATION */
$EXT_CMD['disable']['host'][0]  = array(
'DISABLE_HOST_NOTIFICATIONS',
'$host');

$EXT_CMD['disable']['host'][1]  = array(
'ADD_HOST_COMMENT',
'$host',
'1',
'$user',
'~disable:$comment');

$EXT_CMD['disable']['svc'][0]   = array(
'DISABLE_SVC_NOTIFICATIONS',
'$host',
'$svc');

$EXT_CMD['disable']['svc'][1]  = array(
'ADD_SVC_COMMENT',
'$host',
'$svc',
'1',
'$user',
'~disable:$comment');

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

/* ENABLE GLOBAL NOTIFICATIONS */
$EXT_CMD['ena_notif']['host'][0]  = array(
'ENABLE_NOTIFICATIONS');

/* DISABLE GLOBAL NOTIFICATIONS */
$EXT_CMD['disa_notif']['host'][0]  = array(
'DISABLE_NOTIFICATIONS');

/* TRACK ENTRY */
$EXT_CMD['track']['host'][0]  = array(
'ADD_HOST_COMMENT',
'$host',
'1',
'$user',
'~track:This host is beeing tracked');

$EXT_CMD['track']['svc'][0]  = array(
'ADD_SVC_COMMENT',
'$host',
'$svc',
'1',
'$user',
'~track:This service is beeing tracked');

?>
