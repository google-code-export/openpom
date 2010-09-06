<?php
/*
  OpenPom $Revision$
  $HeadURL$
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
 
  $Date$

  Sylvain Choisnard - schoisnard@exosec.fr                                                 
*/


if (!isset($_SESSION['USER'])) die();

/* supported lang are "en" "fr" */


/* ENGLISH */

$LANG['en'] = array (
'acknowledge'        => 'acknowledge',
'downtime'           => 'downtime',
'recheck'            => 'recheck',
'disable'            => 'disable',
'disable_title'      => 'disable notifications',
'reset'              => 'reset state',
'reset0'             => 'reset',
'reset_title'        => 'reset acknowledge, downtime, notification and comments',
'filter'             => 'filter',
'filtering'          => 'filtering',
'help'               => 'help',
'refresh'            => 'refresh',
'mode'               => 'mode_monitor',
'mode0'              => 'stop mode monitor',
'level1'             => 'criticals',
'level2'             => 'critical/warning',
'level3'             => 'critical/warning/ack',
'level4'             => 'critical/warning/ack/outage',
'level5'             => 'critical/warning/ack/outage/svc',
'level6'             => 'all',
'exclude'            => 'exclude ack/downtime',
'hide'               => 'hide svc for ack host',
'refreshing'         => 'refresh in',
'refreshing0'        => 'refreshing every',
'flag'               => 'flags',
'track'              => 'track', 
'machine'            => 'equipment',
'service'            => 'services',
'group'              => 'groups',
'stinfo'             => 'status information',
'last'               => 'last check',
'duration'           => 'duration',
'comment'            => 'comments',
'comment0'           => 'add comment',
'hour'               => 'hours',
'reload'             => 'nagios is reloading',
'host'               => 'host',
'curstat'            => 'current status',
'curat'              => 'current attempt',
'chktyp'             => 'check type',
'latency'            => 'check latency',
'lastchange'         => 'last state change',
'flapping'           => 'is this service flapping',
'lastup'             => 'last update',
'cancel'             => 'cancel',
'clear'              => 'clear',
'second'             => 'seconds',
'apply'              => 'apply',
'set'                => 'set',
'reverse'            => 'reverse filter',
'option'             => 'options',
'lang'               => 'language',
'column'             => 'displayed columns',
'step'               => 'number of displayed lines',
'level'              => 'default level',
'cols'               => 'check columns to hide',
'maxlentd'           => 'max characters per field',
'frame'              => 'don\'t print the frame around the page',
'meter'              => 'C=Critical W=Warning U=Unknown D=downtime A=Acknowledge T=Total',
'next'               => 'next_page',
'prev'               => 'previous_page',
'fontsize'           => 'Font size alert',
'search'             => 'search (keywords are : not something / = something)',
'querytime'          => 'query in',
'end_down'           => 'end time: ', 
) ;


/* FRENCH */

$LANG['fr'] = array (
'acknowledge'        => 'acquitter',
'downtime'           => 'arr&ecirc;t pr&eacute;vu',
'recheck'            => 'retester',
'disable'            => 'd&eacute;sactiver',
'disable_title'      => 'd&eacute;sactiver les notifications',
'reset'              => 'R&eacute;initialiser',
'reset0'             => 'R&eacute;initialiser',
'reset_title'        => 'R&eacute;initialiser les acquittements, arr&ecirc;t pr&eacute;vu, notifications et commentaires',
'filter'             => 'filtrer',
'filtering'          => 'filtrage',
'help'               => 'aide',
'refresh'            => 'rafra&icirc;chir',
'mode'               => 'mode_moniteur',
'mode0'              => 'arr&ecirc;t mode moniteur',
'level1'             => 'critique',
'level2'             => 'critique/alerte',
'level3'             => 'critique/alerte/connu',
'level4'             => 'critique/alerte/connu/parent',
'level5'             => 'critique/alerte/connu/parent/service',
'level6'             => 'tout',
'exclude'            => 'exclu acquitt&eacute;/arr&ecirc;t pr&eacute;vu',
'hide'               => 'masque service pour h&ocirc;te acquitt&eacute;',
'refreshing'         => 'rafra&icirc;chir dans',
'refreshing0'        => 'rafra&icirc;chissement toutes les',
'flag'               => 'statut',
'track'              => 'suivi',
'machine'            => 'equipement',
'service'            => 'services',
'group'              => 'groupes',
'stinfo'             => 'Information',
'last'               => 'dernier test',
'duration'           => 'dur&eacute;e',
'comment'            => 'commentaires',
'comment0'           => 'ajout commentaire',
'hour'               => 'heures',
'reload'             => 'nagios est en cours de red&eacute;marrage',
'host'               => 'h&ocirc;te',
'curstat'            => 'etat actuel',
'curat'              => 'nombre de test',
'chktyp'             => 'type de test',
'latency'            => 'latence',
'lastchange'         => 'dernier changement d\'&eacute;tat',
'flapping'           => 'le service change d\'&eacute;tat trop souvent',
'lastup'             => 'derni&egrave;re mise &agrave; jour',
'cancel'             => 'annuler',
'clear'              => 'effacer',
'second'             => 'secondes',
'apply'              => 'appliquer',
'set'                => 'fixer',
'reverse'            => 'inverser le filtre',
'option'             => 'param&egrave;tres',
'lang'               => 'langue',
'column'             => 'colonne &agrave; afficher',
'step'               => 'nombre de lignes affich&eacute;es',
'level'              => 'niveau par d&eacute;faut affich&eacute;',
'cols'               => 'cocher les colonnes &agrave; masquer',
'maxlentd'           => 'nombre de carat&egrave;res maximum par champ',
'frame'              => 'ne pas afficher le cadre autour de la page',
'meter'              => 'C=critique W=Alerte U=Inconnu D=Arr&ecirc;t&eacute; A=Acquitt&eacute; T=Total',
'next'               => 'page_suivante',
'prev'               => 'page_pr&eacute;c&eacute;dente',
'fontsize'           => 'Taille de la police des alertes',
'search'             => 'Recherche (mots cl&eacute;s : not ma_recherche / = ma_recherche)',
'querytime'          => 'requ&ecirc;te en',
'end_down'           => 'fin pr&eacute;vu: ',
) ;

/* GET/SET LANG */
if ( (isset($_GET['lang'])) && (isset($LANG[substr($_GET['lang'],0,2)])) ) {
  $MYLANG = substr($_GET['lang'],0,2) ;
  $_SESSION['LANG'] = $MYLANG ;
}
else if (isset($_SESSION['LANG'])) 
  $MYLANG = $_SESSION['LANG'] ;
else
  $_SESSION['LANG'] = $MYLANG ;

?>
