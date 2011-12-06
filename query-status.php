<?php
/*
  OpenPOM
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
*/


$QUERY_STATUS['svc'] = "
  SELECT
    unix_timestamp() - unix_timestamp(SS.last_state_change)  
                                           AS LASTCHANGEDIFF,
    SS.last_state_change                   AS LASTCHANGE,
    SS.current_state                       AS STATE,
    unix_timestamp() - unix_timestamp(SS.last_check)
                                           AS LASTCHECKTIMEDIFF,
    SS.last_check                          AS LASTCHECKTIME,
    SS.output                              AS OUTPUT,
    SS.current_check_attempt               AS CURATTEMP,
    SS.max_check_attempts                  AS MAXATTEMP,
    SS.check_type                          AS CHKTYPE,
    SS.perfdata                            AS PERFDATA,
    SS.latency                             AS LATENCY,
    SS.execution_time                      AS EXEC_TIME,
    SS.problem_has_been_acknowledged       AS ACK,
    SS.scheduled_downtime_depth            AS DOWNTIME,
    SS.notifications_enabled               AS NOTIF,
    IF (
      SS.flap_detection_enabled = 1,
      is_flapping,
      2
    )                                      AS FLAPPING,
    SS.percent_state_change                AS PERCENT,
    unix_timestamp() - unix_timestamp(SS.status_update_time) 
                                           AS UPDATETIMEDIFF,
    SS.status_update_time                  AS UPDATETIME,
    H.address                              AS ADDRESS,
    H.display_name                         AS HOSTNAME,
    S.display_name                         AS SERVICE,
    ( SELECT 
      concat_ws(';', ACO.author_name, ACO.comment_data)
      FROM ".$BACKEND."_commenthistory AS ACO
      WHERE ACO.object_id = SS.service_object_id
      AND ACO.entry_type = 4
      AND ACO.author_name != '(Nagios Process)'
      AND ACO.deletion_time = '0000-00-00 00:00:00' 
      ORDER BY ACO.entry_time DESC
      LIMIT 1 )                            AS ACKCOMMENT,
    ( SELECT 
      concat_ws(';', DCO.author_name, DCO.scheduled_end_time, DCO.comment_data)
      FROM ".$BACKEND."_downtimehistory AS DCO
      WHERE DCO.object_id = SS.service_object_id
      AND DCO.actual_end_time = '0000-00-00 00:00:00' 
      AND DCO.scheduled_end_time >= NOW()
      ORDER BY DCO.entry_time DESC
      LIMIT 1 )                            AS DOWNCOMMENT,
    ( SELECT 
      concat_ws(';', NCO.author_name, NCO.comment_data)
      FROM ".$BACKEND."_commenthistory AS NCO
      WHERE NCO.object_id = SS.service_object_id
      AND NCO.entry_type = 1
      AND NCO.comment_source = 1
      AND NCO.deletion_time = '0000-00-00 00:00:00'
      AND substring_index(NCO.comment_data, ':', 1) = '~disable'
      ORDER BY NCO.entry_time DESC
      LIMIT 1 )                            AS NOTIFCOMMENT, 
    ( SELECT 
      concat_ws(';', CO.author_name, CO.comment_data)
      FROM ".$BACKEND."_commenthistory AS CO
      WHERE CO.object_id = SS.service_object_id
      AND CO.entry_type = 1
      AND CO.comment_source = 1
      AND CO.deletion_time = '0000-00-00 00:00:00'
      AND substring(CO.comment_data, 1, 1) != '~'
      ORDER BY CO.entry_time DESC
      LIMIT 1 )                            AS COMMENT

  FROM
    ".$BACKEND."_servicestatus AS SS
    INNER JOIN ".$BACKEND."_services AS S      ON S.service_object_id = SS.service_object_id
    INNER JOIN ".$BACKEND."_hosts AS H         ON H.host_object_id = S.host_object_id
  WHERE
    SS.servicestatus_id = define_my_id
" ;

$QUERY_STATUS['host'] = "
  SELECT
    unix_timestamp() - unix_timestamp(HS.last_state_change)  
                                           AS LASTCHANGEDIFF,
    HS.last_state_change                   AS LASTCHANGE,
    ( case HS.current_state
        when 2 then 3
        when 1 then 2
        when 0 then 0
        end )                              AS STATE,
    unix_timestamp() - unix_timestamp(HS.last_check)
                                           AS LASTCHECKTIMEDIFF,
    HS.last_check                          AS LASTCHECKTIME,
    HS.output                              AS OUTPUT,
    HS.current_check_attempt               AS CURATTEMP,
    HS.max_check_attempts                  AS MAXATTEMP,
    HS.check_type                          AS CHKTYPE,
    HS.perfdata                            AS PERFDATA,
    HS.latency                             AS LATENCY,
    HS.execution_time                      AS EXEC_TIME,
    HS.problem_has_been_acknowledged       AS ACK,
    HS.scheduled_downtime_depth            AS DOWNTIME,
    HS.notifications_enabled               AS NOTIF,
    IF (
      HS.flap_detection_enabled = 1,
      is_flapping,
      'N/A'
    )                                      AS FLAPPING,
    HS.percent_state_change                AS PERCENT,
    unix_timestamp() - unix_timestamp(HS.status_update_time)
                                           AS UPDATETIMEDIFF,
    HS.status_update_time                  AS UPDATETIME,
    H.address                              AS ADDRESS,
    H.display_name                         AS HOSTNAME,
    ( SELECT 
      concat_ws(';', ACO.author_name, ACO.comment_data)
      FROM ".$BACKEND."_commenthistory AS ACO
      WHERE ACO.object_id = HS.host_object_id
      AND ACO.entry_type = 4
      AND ACO.author_name != '(Nagios Process)'
      AND ACO.deletion_time = '0000-00-00 00:00:00' 
      ORDER BY ACO.entry_time DESC
      LIMIT 1 )                            AS ACKCOMMENT,
    ( SELECT 
      concat_ws(';', DCO.author_name, DCO.scheduled_end_time, DCO.comment_data)
      FROM ".$BACKEND."_downtimehistory AS DCO
      WHERE DCO.object_id = HS.host_object_id
      AND DCO.actual_end_time = '0000-00-00 00:00:00' 
      AND DCO.scheduled_end_time >= NOW()
      ORDER BY DCO.entry_time DESC
      LIMIT 1 )                            AS DOWNCOMMENT,
    ( SELECT 
      concat_ws(';', NCO.author_name, NCO.comment_data)
      FROM ".$BACKEND."_commenthistory AS NCO
      WHERE NCO.object_id = HS.host_object_id
      AND NCO.entry_type = 1
      AND NCO.comment_source = 1
      AND NCO.deletion_time = '0000-00-00 00:00:00'
      AND substring_index(NCO.comment_data, ':', 1) = '~disable'
      ORDER BY NCO.entry_time DESC
      LIMIT 1 )                            AS NOTIFCOMMENT, 
    ( SELECT 
      concat_ws(';', CO.author_name, CO.comment_data)
      FROM ".$BACKEND."_commenthistory AS CO
      WHERE CO.object_id = HS.host_object_id
      AND CO.entry_type = 1
      AND CO.comment_source = 1
      AND CO.deletion_time = '0000-00-00 00:00:00'
      AND substring(CO.comment_data, 1, 1) != '~'
      ORDER BY CO.entry_time DESC
      LIMIT 1 )                            AS COMMENT

  FROM
    ".$BACKEND."_hoststatus AS HS
    INNER JOIN ".$BACKEND."_hosts AS H      ON H.host_object_id = HS.host_object_id
  WHERE
    HS.hoststatus_id = define_my_id
" ;

?>
