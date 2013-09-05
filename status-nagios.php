<?php
/*
  OpenPOM

  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
*/

/* FIXME: Rewrite, this is unreadable ! */

require_once("config.php");
session_name($CODENAME);
session_start();
if (!isset($_SESSION['USER'])) die();
require_once("lang.php");
require_once("query-status.php");
require_once("utils.php");
special_char();

/* requires host/svc in arg1
 * requires status id in arg2
 */
if (!isset($_GET['arg1']) || !isset($_GET['arg2'])) {
  die('bad arguments');
}

$type = $_GET['arg1'];
$id = $_GET['arg2'];

/* find query */
if (!isset($QUERY_STATUS[$type])) {
  die('no query');
}

/*  SQL */
if (!($dbconn = mysql_connect($SQL_HOST, $SQL_USER, $SQL_PASSWD)))
  die('cannot connect to db');
if (!mysql_select_db($SQL_DB, $dbconn))
  die('cannot select db');

$quoted_id = mysql_real_escape_string($id, $dbconn);
$query = str_replace('define_my_id', $quoted_id, $QUERY_STATUS[$type]);

/* perform query */
if (!($st_rep = mysql_query($query, $dbconn)))
  die('query failed: ' . mysql_error($dbconn));

if (!($st_data = mysql_fetch_array($st_rep, MYSQL_ASSOC)))
  die('query returned no data');

switch($st_data['STATE']) {
  case 0: $STATUS = "OK";       $COLOR = $OK;        break;
  case 1: $STATUS = "WARNING";  $COLOR = $WARNING;   break;
  case 2: $STATUS = "CRITICAL"; $COLOR = $CRITICAL;  break;
  case 3: $STATUS = "UNKNOWN";  $COLOR = $UNKNOWN;   break;
}
switch ($st_data['CHKTYPE']) {
  case 0: $CHKTYPE = "Active";  break;
  case 1: $CHKTYPE = "Passive"; break;
}
switch ($st_data['FLAPPING']) {
  case 0: $FLAPPING = strtoupper(_('no'));  break;
  case 1: $FLAPPING = strtoupper(_('yes')); break;
  case 2: $FLAPPING = "N/A"; break;
}

$ADDRESS           = $st_data['ADDRESS'];
$HOSTNAME          = $st_data['HOSTNAME'];

if (empty($st_data['SERVICE']))
  $SERVICE         = $HOST_SERVICE;
else
  $SERVICE         = $st_data['SERVICE'];

$LASTCHANGEDIFF       = $st_data['LASTCHANGEDIFF'];
$LASTCHANGE           = $st_data['LASTCHANGE'];
$LASTCHECKTIMEDIFF    = $st_data['LASTCHECKTIMEDIFF'];
$LASTCHECKTIME        = $st_data['LASTCHECKTIME'];
$LASTTIMEOKDIFF       = $st_data['LASTTIMEOKDIFF'];
$LASTTIMEOK           = $st_data['LASTTIMEOK'];
$NEXTCHECKTIME        = $st_data['NEXTCHECKTIME'];
$NEXTCHECKTIMEDIFF    = $st_data['NEXTCHECKTIMEDIFF'];
$OUTPUT               = $st_data['OUTPUT'];
$LONG_OUTPUT          = $st_data['LONG_OUTPUT'];
$CURATTEMP            = $st_data['CURATTEMP'];
$MAXATTEMP            = $st_data['MAXATTEMP'];
$NORMALINTERVAL       = $st_data['NORMALINTERVAL'];
$RETRYINTERVAL        = $st_data['RETRYINTERVAL'];
$LATENCY              = $st_data['LATENCY'];
$EXEC_TIME            = $st_data['EXEC_TIME'];
$NOTIF                = $st_data['NOTIF'];
$PERCENT              = $st_data['PERCENT'];
$GROUPS               = $st_data['GROUPES'] ;
$CONTACTGROUP         = $st_data['CONTACTGROUP'] ;
$UPDATETIMEDIFF       = $st_data['UPDATETIMEDIFF'];
$UPDATETIME           = $st_data['UPDATETIME'];
$LASTNOTIFY           = $st_data['LASTNOTIFY'];
$COUNTNOTIFY          = $st_data['COUNTNOTIFY'];
$NEXTTIMENOTIFYDIFF   = $st_data['NEXTTIMENOTIFYDIFF'];
$NEXTTIMENOTIFY       = $st_data['NEXTTIMENOTIFY'];
$CHECKNAME            = $st_data['CHECKNAME'];
$ACK                  = $st_data['ACK'];
$DOWNTIME             = $st_data['DOWNTIME'];
$ACKCOMMENT           = explode(';', $st_data['ACKCOMMENT'], 2);
$DOWNCOMMENT          = explode(';', $st_data['DOWNCOMMENT'], 3);
$NOTIFCOMMENT         = explode(';', $st_data['NOTIFCOMMENT'], 2);
$COMMENT              = explode(';', $st_data['COMMENT'], 2);

if (isset($NOTIFCOMMENT[1]) && preg_match('/^~[^:]+:(.*)$/', $NOTIFCOMMENT[1], $cap)) {
  $NOTIFCOMMENT[1] = $cap[1];
}

if ( (! isset($ACKCOMMENT[0]) ) || (empty($ACKCOMMENT[0])) ) $ACKCOMMENT[0] = "N/A" ;
if (! isset($ACKCOMMENT[1]) ) $ACKCOMMENT[1] = "N/A" ;
if ( (! isset($DOWNCOMMENT[0]) ) || (empty($DOWNCOMMENT[0])) ) $DOWNCOMMENT[0] = "N/A" ;
if (! isset($DOWNCOMMENT[1]) ) $DOWNCOMMENT[1] = "N/A" ;
if (! isset($DOWNCOMMENT[2]) ) $DOWNCOMMENT[2] = "N/A" ;
if ( (! isset($NOTIFCOMMENT[0]) ) || (empty($NOTIFCOMMENT[0])) ) $NOTIFCOMMENT[0] = "N/A" ;
if (! isset($NOTIFCOMMENT[1]) ) $NOTIFCOMMENT[1] = "N/A" ;
if ( (! isset($COMMENT[0]) ) || (empty($COMMENT[0])) ) { $COMMENT[0] = "N/A" ; $NOCOMMENT = 1 ; }
else $NOCOMMENT = 0;
if (! isset($COMMENT[1]) ) $COMMENT[1] = "N/A" ;

$STATUSDATA = array (
'curstat'      => "<div class='".$COLOR."'>".$STATUS." (".printtime($LASTCHANGEDIFF).")"."</div>",
'outputstatus' => $OUTPUT,
'checkstatus'  => $CURATTEMP."/".$MAXATTEMP." | ".$NORMALINTERVAL."m/".$RETRYINTERVAL."m | ".printtime($LASTCHECKTIMEDIFF),
'lastok'       => ( (substr($LASTTIMEOK, 0, 4) == "1970") || ($STATUS == "OK") ) ? "N/A" : $LASTTIMEOK." (".printtime($LASTTIMEOKDIFF).")",
'nextcheck'    => ( printtime($NEXTCHECKTIMEDIFF) < 0) ? "N/A" : $NEXTCHECKTIME." (".printtime($NEXTCHECKTIMEDIFF).")",
'checkinfo'    => $CHKTYPE.", A=${st_data['ACTIVE']}, P=${st_data['PASSIVE']} | ".$CHECKNAME,
'checktime'    => $LATENCY." | ".$EXEC_TIME,
'laststatus'   => printtime($LASTCHANGEDIFF)." | ".printtime($UPDATETIMEDIFF),
'flapping'     => $FLAPPING." ".(($st_data['FLAPPING'] != 2) ? "(".$PERCENT."% "._('state_change').")" : ""),
'groupstatus'  => $GROUPS." | ".$CONTACTGROUP,
'notifystatus' => (($COUNTNOTIFY == 0) ? "N/A (0)" : $LASTNOTIFY." (".$COUNTNOTIFY.") ")." | ".(($NEXTTIMENOTIFYDIFF <= 0) ? "N/A" : printtime($NEXTTIMENOTIFYDIFF)),
/*ACKSTATUS*/
($ACK && count($ACKCOMMENT) > 1) ? 'ackcur' : 'noackcur'            => $ACKCOMMENT[1],
/*DOWNSTATUS*/
($DOWNTIME && count($DOWNCOMMENT) > 2) ? 'downcur' : 'nodowncur'    => $DOWNCOMMENT[2]." ("._('end')." ".$DOWNCOMMENT[1].")",
/*NOTIFYSTATUS*/
(!$NOTIF && count($NOTIFCOMMENT) > 1) ? 'notifycur' : 'nonotifycur' => $NOTIFCOMMENT[1],
/*COMMENT*/
($NOCOMMENT == 0) ? 'commentcur' : 'nocommentcur' => $COMMENT[1],
) ;

/* Open gitory popup */
if (count($_SESSION['HISTORY'])) {
    $STATUSDATA['linkhistory'] = "<a href=\"#\" onClick=\"return pop('history.php?id=$id&type=$type&host=$HOSTNAME&svc=$SERVICE', '$id', '$HISTORY_POPUP_WIDTH', '$HISTORY_POPUP_HEIGHT');\">".ucfirst(_('show_history'))."</a>";
}

/* Custom variables */

/* The $cvar array is only used to build the $variables array which
 * contains the final list of key/value custom variables. Precedence
 * is given to variables defined on the service if also present on
 * the host.
 *
 * Values of custom variables can make reference to other custom
 * variables using the syntax ${_OTHER_VARIABLE}. Values can also
 * reference special properties of the $other_variables array with
 * the same syntax.
 *
 * Obviously, a variable cannot reference itself.
 */
$cvar = array();
$variables = array(
    'host_name' => $st_data['HOSTNAME'],
    'host_address' => $st_data['ADDRESS'],
    'address' => $st_data['ADDRESS'],
    'service_description' => $SERVICE,
    'check_command' => $st_data['CHECKNAME'],
);

if (!is_null($st_data['CVAR_HOST']))
    $cvar = array_merge($cvar, explode(chr(0x16), $st_data['CVAR_HOST']));
if (!is_null($st_data['CVAR_SERVICE']))
    $cvar = array_merge($cvar, explode(chr(0x16), $st_data['CVAR_SERVICE']));

foreach ($cvar as $v) {
    if (strpos($v, 'BASH_') === 0)
        continue;
    if (($eq = strpos($v, '=')) > 0)
        $variables['_' . substr($v, 0, $eq)] = substr($v, $eq + 1);
}

function cvar_link_text($full)
{
    $l = strlen($full);

    if ($l > 30)
        return htmlspecialchars(substr($full, 0, 30)) . '...';
    else
        return htmlspecialchars($full);
}

/* This is a preg_replace callack function to build HTML links. It
 * expects to receive a 3 elements array as argument:
 *   - 0: the full matched string,
 *   - 1: the protocol part,
 *   - 2: the URL without the proto:// part
 *
 * FIXME: Might wan't to add rawurlencode() in the link value but
 * it should be configurable on a per-variable basis.
 */
function cvar_link_replace_cb($match)
{
    /* Check for user:password URL and if so, do not display
     * user:password in HTML link text.
     */
    if (preg_match('/^([^:]+):([^@]+)@(.*)/', $match[2], $cap))
        $text = cvar_link_text("${match[1]}://${cap[3]}");
    else
        $text = cvar_link_text($match[0]);

    if (strncasecmp($match[1], 'http', 4) == 0)
        return '<a href="' . $match[0] . '" target="_blank">' . $text . '</a>';
    else
        return '<a href="' . $match[0] . '">' . $text . '</a>';
}

/* This function formats a custom variable value in HTML, resolving
 * any potential reference to other variables.
 */
function cvar_format($cvar) {
    global $variables;

    if (!isset($variables[$cvar]))
        return '';

    if (preg_match_all("/\\\$\\{([^}]+)\\}/", $variables[$cvar], $cap, PREG_OFFSET_CAPTURE)) {
        $out = '';
        $pos = 0;

        foreach ($cap[1] as $c) {
            $out .= substr($variables[$cvar], $pos, ($c[1] - 2) - $pos);

            /* do not expand if the variable reference itself or if it
             * references a non existing variable */
            if ($c[0] == $cvar || !isset($variables[$c[0]]))
                $out .= '${' . $cvar . '}';
            else
                $out .= $variables[$c[0]];

            $pos = $c[1] + strlen($c[0]) + 1;
        }

        $out .= substr($variables[$cvar], $pos);
    }
    else
        $out = $variables[$cvar];

    /* build html links */
    return preg_replace_callback('@([a-z]+)://([^\s]+)@', 'cvar_link_replace_cb', $out);
}

/* This function returns a more human-readable string value
 * for a custom variable name.
 */
function cvar_alias($cvar)
{
    $out = strtolower($cvar);
    if ($out[0] == '_') $out = substr($out, 1);
    return str_replace('_', '-', $out);
}

$STATUSHEAD = array (
'ackcur'     => '<img class="inline-middle" src="img/flag_ack.gif" /><span class="inline-middle" >&nbsp;('.$ACKCOMMENT[0].')</span>',
'downcur'    => '<img class="inline-middle" src="img/flag_downtime.png" /><span class="inline-middle" >&nbsp;('.$DOWNCOMMENT[0].')</span>',
'notifycur'  => '<img class="inline-middle" src="img/flag_notify.png" /><span class="inline-middle" >&nbsp;('.$NOTIFCOMMENT[0].')</span>',
'commentcur' => '<img class="inline-middle" src="img/flag_comment.gif" /><span class="inline-middle">&nbsp;('.$COMMENT[0].')</span>',
) ;

if ( (isset($_GET['fix'])) || (isset($_SESSION['STATUS']['graph'])) )
  $g = get_graph('status', $HOSTNAME, $SERVICE);
else $g = "" ;

/* bottom section (override RRD graph) */
$bottom_fct = 'status_nagios__' . preg_replace('/[^a-z0-9]/i', '_', $CHECKNAME);
?>

<?php if (isset($_GET['fix'])) { ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
  <head>
    <title><?php echo "$SERVICE " . _('on') . " $HOSTNAME" ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $ENCODING ?>" />
    <meta http-equiv="CACHE-CONTROL" content="NO-CACHE" />
    <meta http-equiv="PRAGMA" content="NO-CACHE" />
    <link rel="stylesheet" type="text/css" href="style.css" />
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/lib.js"></script>
  </head>
  <body>
<?php } ?>

  <div class="box-content" id="box-popup">

    <?php if (isset($_GET['fix'])) { ?>
      <table id="popup">

    <?php } else { ?>
      <a  id="fix"
          href="#"
          onClick="return pop(
              '<?php echo $_SERVER['REQUEST_URI'] ?>&fix',
              'popup_nagios_<?php echo $type ?>_<?php echo $id ?>', <?php echo $STATUS_POPUP_WIDTH ?>, <?php echo $STATUS_POPUP_HEIGHT ?>);"><img src="img/popup.png"
             border="0"
             alt="<?php echo ucfirst(_('fixed')) ?>"
             title="<?php echo ucfirst(_('fixed')) ?>" /></a>

      <table id="popup" onmouseover="restart_popin_hide_timer();">
    <?php } ?>

      <tr>
        <th>
          <?php echo ucfirst(_('machine')) ?> |
          <?php echo ucfirst(_('service')) ?>
        </th>
        <td>
          <div <?php if (!isset($_GET['fix'])) { ?> style="padding-right: 20px;" <?php } ?>>
            <?php echo $SERVICE ?>
            <?php echo _('on') ?>
            <?php echo $HOSTNAME ?> (<?php echo $ADDRESS ?>)
          </div>
        </td>
      </tr>

      <?php $more = 0 ;
            if ( (is_array($STATUSPOPIN)) && (count($STATUSPOPIN > 0)) ) {
              $i = 1 ;
              foreach ($STATUSPOPIN AS $key => $val) {
                if ($val == 0) continue ;
                if ( (!isset($STATUSDATA[$key])) || (empty($STATUSDATA[$key])) )
                  continue ;
                if ( (!isset($_GET['fix'])) && ($val < 2) &&
                     (!isset($_SESSION['STATUS']['all'])) &&
                     ($i > $_SESSION['STATUS']['limit']) ) {
                  $more = 1 ;
                  continue ;
                }
      ?>
      <tr>
        <th><?php if (isset($STATUSHEAD[$key])) echo $STATUSHEAD[$key]; else echo ucfirst(_($key)); ?></th>
        <td><?php echo $STATUSDATA[$key] ; ?></td>
      </tr>
      <?php
                $i++;
              } //end foreach
            }
      ?>
      <?php if ( ($more) && (!isset($_GET['fix'])) ) { ?>
      <tr>
        <th>
          <a href="#"
            onClick="return pop(
                '<?php echo $_SERVER['REQUEST_URI'] ?>&fix',
                'popup_nagios_<?php echo $type ?>_<?php echo $id ?>', <?php echo $STATUS_POPUP_WIDTH ?>, <?php echo $STATUS_POPUP_HEIGHT ?>);" title="<?php echo ucfirst(_('titlemore'))?>"><?php echo ucfirst(_('more'))?></a>
        </th>
        <td></td>
      </tr>
      <?php } ?>

<?php
foreach ($SHOWSTATUSCVAR as $v) {
    if (!isset($variables[$v]))
        continue;
?>

      <tr>
        <th>
          <?php echo ucfirst(_('cvar')) ?>
          <?php echo cvar_alias($v) ?>
        </th>
        <td>
          <?php echo cvar_format($v); ?>
        </td>
      </tr>

<?php } ?>

      <?php if (!empty($g)) { ?>
        <tr>
          <th style="height: 6px; background: none; border: none; border-top: 1px solid #E0E5D3;"></th>
          <td></td>
        </tr>
        <tr>
          <td colspan="2" style="padding: 0; margin: 0; vertical-align: bottom; height: 100%;">
      <?php if (!function_exists($bottom_fct) || !$bottom_fct()) { ?>
            <img style="vertical-align: bottom; padding: 0; margin: 0;"
                 <?php if ($POPIN_FIT_TO_GRAPH_WIDTH && !isset($_GET['fix'])) { ?>
                 onload="resize_popin($(this).outerWidth() + 12);"
                 <?php } ?>
                 src="<?php echo $g ?>" />
      <?php } ?>
          </td>
        </tr>

      <?php } ?>

    </table>
  </div>

<?php if (isset($_GET['fix'])) { ?>
  </body>
</html>
<?php } ?>

<?php
/*  free resources */
mysql_free_result($st_rep);
mysql_close($dbconn);
?>
