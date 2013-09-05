<?php
/*
  OpenPOM

  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
*/

$QUERY_HISTORY['svc'] = "
  SELECT
    sub.state                AS color,
    sub.state_type           AS state_type,
    sub.type                 AS type,
    sub.entry_time           AS entry_time,
    sub.author_name          AS author_name,
    sub.output               AS outputstatus

  FROM ((

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
    ORDER BY ACO.acknowledgement_id DESC
    LIMIT define_my_submax

  ) UNION (

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
      CONCAT_WS('', DOW.comment_data, ' ("._('scheduled_start_time').": ', DOW.scheduled_start_time, ' "._('scheduled_end_time').": ', DOW.scheduled_end_time, ')') AS output
    FROM ".$BACKEND."_downtimehistory AS DOW
      JOIN ".$BACKEND."_servicestatus AS SS ON DOW.object_id = SS.service_object_id
    WHERE SS.servicestatus_id = define_my_id
      AND define_my_down = 1
    ORDER BY DOW.downtimehistory_id DESC
    LIMIT define_my_submax

  ) UNION (

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
      AND COM.entry_type != 4
      AND define_my_com = 1
    ORDER BY COM.commenthistory_id DESC
    LIMIT define_my_submax

  ) UNION (

    -- NOTIFICATION
    SELECT
      CO.name1 AS author_name,
      N.state,
      '1' AS state_type,
      'notify' AS type,
      N.end_time AS entry_time,
      N.output AS comment_data
    FROM ".$BACKEND."_notifications AS N
      JOIN ".$BACKEND."_servicestatus AS SS ON N.object_id = SS.service_object_id
      JOIN ".$BACKEND."_contactnotifications AS CN ON CN.notification_id = N.notification_id
      JOIN ".$BACKEND."_objects AS CO ON CO.object_id = CN.contact_object_id
    WHERE SS.servicestatus_id = define_my_id
      AND define_my_notify = 1
    ORDER BY N.notification_id DESC
    LIMIT define_my_submax

  ) UNION (

    -- STATEHISTORY
    SELECT
      'Nagios Process' AS author_name,
      STH.state,
      STH.state_type,
      'statehistory' AS type,
      STH.state_time AS entry_time,
      STH.output
    FROM ".$BACKEND."_servicestatus AS SS
      JOIN ".$BACKEND."_statehistory AS STH ON STH.object_id = SS.service_object_id
    WHERE SS.servicestatus_id = define_my_id
      AND define_my_state = 1
    ORDER BY STH.statehistory_id DESC
    LIMIT define_my_submax

  ) UNION (

    -- FLAPPING
    SELECT
      'Nagios Process' AS author_name,
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
      ( CASE F.event_type WHEN 1000 then 'start flapping' else 'stop flapping' end ) AS output
    FROM ".$BACKEND."_flappinghistory AS F
      JOIN ".$BACKEND."_servicestatus AS SS ON SS.service_object_id = F.object_id
    WHERE SS.servicestatus_id = define_my_id
      AND define_my_flap = 1
    ORDER BY F.flappinghistory_id DESC
    LIMIT define_my_submax

  )) AS sub

  ORDER BY sub.entry_time DESC

  LIMIT define_my_first, define_my_step

" ;

$QUERY_HISTORY['host'] = "
  SELECT
    sub.state                AS color,
    sub.state_type           AS state_type,
    sub.type                 AS type,
    sub.entry_time           AS entry_time,
    sub.author_name          AS author_name,
    sub.output               AS outputstatus

  FROM ((

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
    ORDER BY ACO.acknowledgement_id DESC
    LIMIT define_my_submax

  ) UNION (

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
      CONCAT_WS('', DOW.comment_data, ' ("._('scheduled_start_time').": ', DOW.scheduled_start_time, ' "._('scheduled_end_time').": ', DOW.scheduled_end_time, ')') AS output
    FROM ".$BACKEND."_downtimehistory AS DOW
      JOIN ".$BACKEND."_hoststatus AS HS ON DOW.object_id = HS.host_object_id
    WHERE HS.hoststatus_id = define_my_id
      AND define_my_down = 1
    ORDER BY DOW.downtimehistory_id DESC
    LIMIT define_my_submax

  ) UNION (

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
      AND COM.entry_type != 4
      AND define_my_com = 1
    ORDER BY COM.commenthistory_id DESC
    LIMIT define_my_submax

  ) UNION (

    -- NOTIFICATION
    SELECT
      CO.name1 AS author_name,
      N.state,
      '1' AS state_type,
      'notify' AS type,
      N.end_time AS entry_time,
      N.output AS comment_data
    FROM ".$BACKEND."_notifications AS N
      JOIN ".$BACKEND."_hoststatus AS HS ON N.object_id = HS.host_object_id
      JOIN ".$BACKEND."_contactnotifications AS CN ON CN.notification_id = N.notification_id
      JOIN ".$BACKEND."_objects AS CO ON CO.object_id = CN.contact_object_id
    WHERE HS.hoststatus_id = define_my_id
      AND define_my_notify = 1
    ORDER BY N.notification_id DESC
    LIMIT define_my_submax

  ) UNION (

    -- STATEHISTORY
    SELECT
      'Nagios Process' AS author_name,
      STH.state,
      STH.state_type,
      'statehistory' AS type,
      STH.state_time AS entry_time,
      STH.output
    FROM ".$BACKEND."_hoststatus AS HS
      JOIN ".$BACKEND."_statehistory AS STH ON STH.object_id = HS.host_object_id
    WHERE HS.hoststatus_id = define_my_id
      AND define_my_state = 1
    ORDER BY STH.statehistory_id DESC
    LIMIT define_my_submax

  ) UNION (

    -- FLAPPING
    SELECT
      'Nagios Process' AS author_name,
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
      ( CASE F.event_type WHEN 1000 then 'start flapping' else 'stop flapping' end ) AS output
    FROM ".$BACKEND."_flappinghistory AS F
      JOIN ".$BACKEND."_hoststatus AS HS ON HS.host_object_id = F.object_id
    WHERE HS.hoststatus_id = define_my_id
      AND define_my_flap = 1
    ORDER BY F.flappinghistory_id DESC
    LIMIT define_my_submax

  )) AS sub

  ORDER BY sub.entry_time DESC

  LIMIT define_my_first, define_my_step

" ;

?>
