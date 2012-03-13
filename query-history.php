<?php
/*
  OpenPOM
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
*/

$QUERY_HISTORY['svc'] = "
  SELECT
    SQL_CALC_FOUND_ROWS
    sub.author_name          AS author_name,
    sub.state                AS color,
    sub.state_type           AS state_type,
    sub.type		     AS type,
    sub.entry_time           AS entry_time,
    sub.output               AS outputstatus
    
  FROM (

    -- ACKNOWLEDGEMENT 
    SELECT 
      ACO.author_name,
      ACO.state,
      '1' AS state_type,
      'ack' AS type,
      ACO.entry_time,
      ACO.comment_data AS output
    FROM ".$BACKEND."_acknowledgements AS ACO
      JOIN ".$BACKEND."_servicestatus AS SS ON ACO.object_id = SS.service_object_id
    WHERE SS.servicestatus_id = define_my_id
      AND define_my_ack = 1
    LIMIT 1000

  UNION

    -- DOWNTIME
    SELECT 
      DOW.author_name,
      ( SELECT STH.state
        FROM ".$BACKEND."_statehistory AS STH
          JOIN ".$BACKEND."_servicestatus AS SS2 ON STH.object_id = SS2.service_object_id
        WHERE SS2.servicestatus_id = define_my_id 
          AND STH.state_time <= DOW.entry_time 
        ORDER BY STH.state_time DESC 
        LIMIT 1 
      ) AS state,
      '1' AS state_type,
      'downtime' AS type,
      DOW.entry_time,
      CONCAT_WS('', DOW.comment_data, ' (".lang($MYLANG, 'scheduled_start_time').": ', DOW.scheduled_start_time, ' ".lang($MYLANG, 'scheduled_end_time').": ', DOW.scheduled_end_time, ')') AS output
    FROM ".$BACKEND."_downtimehistory AS DOW
      JOIN ".$BACKEND."_servicestatus AS SS ON DOW.object_id = SS.service_object_id
    WHERE SS.servicestatus_id = define_my_id
      AND define_my_down = 1
    LIMIT 1000

  UNION

    -- COMMENT
    SELECT 
      COM.author_name,
      ( SELECT STH.state
        FROM ".$BACKEND."_statehistory AS STH
          JOIN ".$BACKEND."_servicestatus AS SS2 ON STH.object_id = SS2.service_object_id
        WHERE SS2.servicestatus_id = define_my_id 
          AND STH.state_time <= COM.entry_time 
        ORDER BY STH.state_time DESC 
        LIMIT 1 
      ) AS state,
      '1' AS state_type,
      'comment' AS type,
      COM.entry_time,
      COM.comment_data AS output
    FROM ".$BACKEND."_commenthistory AS COM
      JOIN ".$BACKEND."_servicestatus AS SS ON COM.object_id = SS.service_object_id
    WHERE SS.servicestatus_id = define_my_id
      AND COM.entry_type = 1
      AND COM.comment_data != '~track:This service is beeing tracked'
      AND define_my_com = 1
    LIMIT 1000

  UNION

    -- NOTIFICATION
    SELECT
      CONCAT_WS('', C.alias, ' (', C.email_address, ')' ) AS author_name,
      N.state,
      '1' AS state_type,
      'notify' AS type,
      N.end_time AS entry_time,
      N.output AS comment_data
    FROM ".$BACKEND."_notifications AS N
      JOIN ".$BACKEND."_servicestatus AS SS ON N.object_id = SS.service_object_id
      JOIN ".$BACKEND."_contactnotifications AS CN ON CN.notification_id = N.notification_id
      JOIN ".$BACKEND."_contacts AS C ON C.contact_object_id = CN.contact_object_id
    WHERE SS.servicestatus_id = define_my_id
      AND define_my_notify = 1
    LIMIT 1000

  UNION

    -- STATEHISTORY
    SELECT
      '(nagios)' AS author_name,
      STH.state,
      STH.state_type,
      'statehistory' AS type,
      STH.state_time AS entry_time,
      STH.output
    FROM ".$BACKEND."_servicestatus AS SS 
      JOIN ".$BACKEND."_statehistory AS STH ON STH.object_id = SS.service_object_id
    WHERE SS.servicestatus_id = define_my_id
      AND define_my_state = 1
    LIMIT 1000

  UNION 

    -- FLAPPING
    SELECT
      '(nagios)' AS author_name,
      ( SELECT STH.state
        FROM ".$BACKEND."_statehistory AS STH
          JOIN ".$BACKEND."_servicestatus AS SS2 ON STH.object_id = SS2.service_object_id
        WHERE SS2.servicestatus_id = define_my_id 
          AND STH.state_time <= F.event_time 
        ORDER BY STH.state_time DESC 
        LIMIT 1 
      ) AS state,
      '0' AS state_type,
      'flapping' AS type,
      F.event_time AS entry_time,
      ( CASE F.event_type WHEN 1000 then 'start flapping' when 1001 then 'stop flapping' end ) AS output
    FROM ".$BACKEND."_flappinghistory AS F
      JOIN ".$BACKEND."_servicestatus AS SS ON SS.service_object_id = F.object_id
    WHERE SS.servicestatus_id = define_my_id
      AND define_my_flap = 1
    LIMIT 1000

  ) AS sub

  ORDER BY define_my_sort define_my_order

  LIMIT define_my_first, define_my_step

" ;

$QUERY_HISTORY['host'] = "
  SELECT
    SQL_CALC_FOUND_ROWS
    sub.author_name          AS author_name,
    sub.state                AS color,
    sub.state_type	     AS state_type,
    sub.type		     AS type,
    sub.entry_time           AS entry_time,
    sub.output               AS outputstatus
    
  FROM (

    -- ACKNOWLEDGEMENT 
    SELECT 
      ACO.author_name,
      ACO.state,
      '1' AS state_type,
      'ack' AS type,
      ACO.entry_time,
      ACO.comment_data AS output
    FROM ".$BACKEND."_acknowledgements AS ACO
      JOIN ".$BACKEND."_hoststatus AS HS ON ACO.object_id = HS.host_object_id
    WHERE HS.hoststatus_id = define_my_id
      AND define_my_ack = 1
    LIMIT 1000

  UNION

    -- DOWNTIME
    SELECT 
      DOW.author_name,
      ( SELECT STH.state
        FROM ".$BACKEND."_statehistory AS STH
          JOIN ".$BACKEND."_hoststatus AS HS2 ON STH.object_id = HS2.host_object_id
        WHERE HS2.hoststatus_id = define_my_id 
          AND STH.state_time <= DOW.entry_time 
        ORDER BY STH.state_time DESC 
        LIMIT 1 
      ) AS state,
      '1' AS state_type,
      'downtime' AS type,
      DOW.entry_time,
      CONCAT_WS('', DOW.comment_data, ' (".lang($MYLANG, 'scheduled_start_time').": ', DOW.scheduled_start_time, ' ".lang($MYLANG, 'scheduled_end_time').": ', DOW.scheduled_end_time, ')') AS output
    FROM ".$BACKEND."_downtimehistory AS DOW
      JOIN ".$BACKEND."_hoststatus AS HS ON DOW.object_id = HS.host_object_id
    WHERE HS.hoststatus_id = define_my_id
      AND define_my_down = 1
    LIMIT 1000

  UNION

    -- COMMENT
    SELECT 
      COM.author_name,
      ( SELECT STH.state
        FROM ".$BACKEND."_statehistory AS STH
          JOIN ".$BACKEND."_hoststatus AS HS2 ON STH.object_id = HS2.host_object_id
        WHERE HS2.hoststatus_id = define_my_id 
          AND STH.state_time <= COM.entry_time 
        ORDER BY STH.state_time DESC 
        LIMIT 1 
      ) AS state,
      '1' AS state_type,
      'comment' AS type,
      COM.entry_time,
      COM.comment_data AS output
    FROM ".$BACKEND."_commenthistory AS COM
      JOIN ".$BACKEND."_hoststatus AS HS ON COM.object_id = HS.host_object_id
    WHERE HS.hoststatus_id = define_my_id
      AND COM.entry_type = 1
      AND COM.comment_data != '~track:This service is beeing tracked'
      AND define_my_com = 1
    LIMIT 1000

  UNION

    -- NOTIFICATION
    SELECT
      CONCAT_WS('', C.alias, ' (', C.email_address, ')' ) AS author_name,
      N.state,
      '1' AS state_type,
      'notify' AS type,
      N.end_time AS entry_time,
      N.output AS comment_data
    FROM ".$BACKEND."_notifications AS N
      JOIN ".$BACKEND."_hoststatus AS HS ON N.object_id = HS.host_object_id
      JOIN ".$BACKEND."_contactnotifications AS CN ON CN.notification_id = N.notification_id
      JOIN ".$BACKEND."_contacts AS C ON C.contact_object_id = CN.contact_object_id
    WHERE HS.hoststatus_id = define_my_id
      AND define_my_notify = 1
    LIMIT 1000

  UNION

    -- STATEHISTORY
    SELECT
      '(nagios)' AS author_name,
      STH.state,
      STH.state_type,
      'statehistory' AS type,
      STH.state_time AS entry_time,
      STH.output
    FROM ".$BACKEND."_hoststatus AS HS 
      JOIN ".$BACKEND."_statehistory AS STH ON STH.object_id = HS.host_object_id
    WHERE HS.hoststatus_id = define_my_id
      AND define_my_state = 1
    LIMIT 1000

  UNION

    -- FLAPPING
    SELECT
      '(nagios)' AS author_name,
      ( SELECT STH.state
        FROM ".$BACKEND."_statehistory AS STH
          JOIN ".$BACKEND."_hoststatus AS HS2 ON STH.object_id = HS2.host_object_id
        WHERE HS2.hoststatus_id = define_my_id 
          AND STH.state_time <= F.event_time 
        ORDER BY STH.state_time DESC 
        LIMIT 1 
      ) AS state,
      '0' AS state_type,
      'flapping' AS type,
      F.event_time AS entry_time,
      ( CASE F.event_type WHEN 1000 then 'start flapping' when 1001 then 'stop flapping' end ) AS output
    FROM ".$BACKEND."_flappinghistory AS F
      JOIN ".$BACKEND."_hoststatus AS HS ON HS.host_object_id = F.object_id
    WHERE HS.hoststatus_id = define_my_id
      AND define_my_flap = 1
    LIMIT 1000

  ) AS sub

  ORDER BY define_my_sort define_my_order

  LIMIT define_my_first, define_my_step

" ;

?>
