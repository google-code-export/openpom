<?php
/*
  OpenPOM

  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
*/

if (!isset($_SESSION['USER'])) die();

/* supported lang are "en_EN" "fr_FR" "de_DE" */

$LANG = array (	'fr_FR' => 'Fran&ccedil;ais',
		'en_US' => 'English',
		'de_DE' => 'Deutsch');
$shortlang = array ('fr' => 'fr_FR',
			'en' => 'en_US',
			'us' => 'en_US',
			'de' => 'de_DE');


if ( (isset($_GET['i18n'])) && (isset($shortlang[substr($_GET['i18n'],0,2)])) ) {
  if(isset($LANG[substr($_GET['i18n'],0,5)])){
    $_SESSION['LANG'] = substr($_GET['i18n'],0,5) ;
  }
  else {
    $_SESSION['LANG'] = $shortlang[substr($_GET['i18n'],0,2)] ;
  }
}
else if (!isset($_SESSION['LANG']))
  $_SESSION['LANG'] = $MYLANG;

putenv("LC_TIME=".$_SESSION['LANG'].".UTF-8");
putenv("LC_MESSAGES=".$_SESSION['LANG'].".UTF-8");
setlocale(LC_TIME, $_SESSION['LANG'].".UTF-8");
setlocale(LC_MESSAGES, $_SESSION['LANG'].".UTF-8");

bindtextdomain('messages', 'i18n');
bind_textdomain_codeset('messages','UTF-8');
textdomain('messages');

/* handle column names not translated */
function _col($column_name) {
    if (($tr = ucfirst(_("col_$column_name"))) == "Col_$column_name")
        return ucfirst($column_name);
    return $tr;
}

?>
