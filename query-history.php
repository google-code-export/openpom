<?php
/*
  OpenPOM
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
*/


$QUERY_HISTORY['ack']['svc'] = "
  SELECT 
    ACO.entry_time,
    ACO.author_name,
    ACO.comment_data
  FROM ".$BACKEND."_acknowledgements AS ACO
    JOIN ".$BACKEND."_servicestatus AS SS ON ACO.object_id = SS.service_object_id
  WHERE SS.servicestatus_id = define_my_id
  ORDER BY ACO.entry_time DESC
  LIMIT 100
  ;
" ;

$QUERY_HISTORY['ack']['host'] = "
  SELECT
    ACO.entry_time,
    ACO.author_name,
    ACO.comment_data
  FROM ".$BACKEND."_acknowledgements AS ACO
    JOIN ".$BACKEND."_hoststatus AS HS ON ACO.object_id = HS.host_object_id
  WHERE HS.hoststatus_id = define_my_id
  ORDER BY ACO.entry_time DESC
  LIMIT 100
  ;
" ;

$QUERY_HISTORY['down']['svc'] = "
  SELECT 
    DOW.entry_time,
    DOW.author_name,
    DOW.comment_data,
    DOW.scheduled_start_time,
    DOW.scheduled_end_time
  FROM ".$BACKEND."_downtimehistory AS DOW
    JOIN ".$BACKEND."_servicestatus AS SS ON DOW.object_id = SS.service_object_id
  WHERE SS.servicestatus_id = define_my_id
  ORDER BY DOW.entry_time DESC
  LIMIT 100
  ;
" ;

$QUERY_HISTORY['down']['host'] = "
  SELECT 
    DOW.entry_time,
    DOW.author_name,
    DOW.comment_data,
    DOW.scheduled_start_time,
    DOW.scheduled_end_time
  FROM ".$BACKEND."_downtimehistory AS DOW
    JOIN ".$BACKEND."_hoststatus AS HS ON DOW.object_id = HS.host_object_id
  WHERE HS.hoststatus_id = define_my_id
  ORDER BY DOW.entry_time DESC
  LIMIT 100
  ;
" ;

$QUERY_HISTORY['com']['svc'] = "
  SELECT 
    COM.entry_time,
    COM.author_name,
    COM.comment_data
  FROM ".$BACKEND."_commenthistory AS COM
    JOIN ".$BACKEND."_servicestatus AS SS ON COM.object_id = SS.service_object_id
  WHERE SS.servicestatus_id = define_my_id
    AND COM.entry_type = 1
  ORDER BY COM.entry_time DESC
  LIMIT 100
  ;
" ;

$QUERY_HISTORY['com']['host'] = "
  SELECT 
    COM.entry_time,
    COM.author_name,
    COM.comment_data,
    COM.entry_type
  FROM ".$BACKEND."_commenthistory AS COM
    JOIN ".$BACKEND."_hoststatus AS HS ON COM.object_id = HS.host_object_id
  WHERE HS.hoststatus_id = define_my_id
    AND COM.entry_type = 1
  ORDER BY COM.entry_time DESC
  LIMIT 100
  ;
" ;

$QUERY_HISTORY['notify']['svc'] = "
  SELECT
    NOF.start_time AS entry_time,
    CO.alias AS contact,
    NOF.output   
  FROM ".$BACKEND."_notifications AS NOF
    JOIN ".$BACKEND."_servicestatus AS SS ON NOF.object_id = SS.service_object_id
    JOIN ".$BACKEND."_contactnotifications CNO ON NOF.object_id = CNO.notification_id
    JOIN ".$BACKEND."_contacts CO ON CNO.contact_object_id = CO.contact_object_id
  WHERE SS.servicestatus_id = define_my_id
  ORDER BY NOF.start_time DESC
  LIMIT 100;
" ;

$QUERY_HISTORY['notify']['host'] = "
  SELECT
    NOF.start_time AS entry_time,
    CO.alias AS contact,
    NOF.output   
  FROM ".$BACKEND."_notifications AS NOF
    JOIN ".$BACKEND."_hoststatus AS HS ON NOF.object_id = HS.host_object_id
    JOIN ".$BACKEND."_contactnotifications CNO ON NOF.object_id = CNO.notification_id
    JOIN ".$BACKEND."_contacts CO ON CNO.contact_object_id = CO.contact_object_id
  WHERE HS.hoststatus_id = NOF.object_id
  ORDER BY NOF.start_time DESC
  LIMIT 100;
" ;

$QUERY_HISTORY['state']['svc'] = "
  SELECT
    STH.state_time AS entry_time,
    ( CASE STH.state when 3 then 'unknown' when 2 then 'critical' when 1 then 'warning' else 'ok' end ) AS state,
    STH.output
  FROM ".$BACKEND."_servicestatus AS SS 
    JOIN ".$BACKEND."_statehistory AS STH ON STH.object_id = SS.service_object_id
  WHERE SS.servicestatus_id = define_my_id
  ORDER BY STH.state_time DESC
  LIMIT 100;
" ;

$QUERY_HISTORY['state']['host'] = "
  SELECT
    STH.state_time AS entry_time,
    ( CASE STH.state when 3 then 'unknown' when 2 then 'critical' when 1 then 'warning' else 'ok' end ) AS state,
    STH.output
  FROM ".$BACKEND."_hoststatus AS HS
    JOIN ".$BACKEND."_statehistory AS STH ON STH.object_id = HS.host_object_id
  WHERE HS.hoststatus_id = define_my_id
  ORDER BY STH.state_time DESC
  LIMIT 100;
" ;

?>
