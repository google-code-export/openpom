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
    unix_timestamp() - unix_timestamp(SS.last_time_ok)
                                           AS LASTTIMEOKDIFF,
    SS.last_time_ok                        AS LASTTIMEOK,
    SS.output                              AS OUTPUT,
    SS.current_check_attempt               AS CURATTEMP,
    SS.max_check_attempts                  AS MAXATTEMP,
    SS.normal_check_interval               AS NORMALINTERVAL,
    SS.retry_check_interval                AS RETRYINTERVAL,
    SS.check_type                          AS CHKTYPE,
    ( CASE SS.check_type 
        WHEN 0 THEN SS.active_checks_enabled
        WHEN 1 THEN SS.passive_checks_enabled
      END )                                AS CHECKENABLE,
    SS.perfdata                            AS PERFDATA,
    SS.latency                             AS LATENCY,
    SS.execution_time                      AS EXEC_TIME,
    SS.problem_has_been_acknowledged       AS ACK,
    SS.scheduled_downtime_depth            AS DOWNTIME,
    SS.notifications_enabled               AS NOTIF,
    SS.check_type                          AS CHECKTYPE,
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
    SS.next_check                          AS NEXTCHECKTIME,
    unix_timestamp(SS.next_check) - unix_timestamp()
                                           AS NEXTCHECKTIMEDIFF,
    GROUP_CONCAT(
      DISTINCT OHG.name1
      ORDER BY OHG.name1
      DESC SEPARATOR ', ')
                                           AS GROUPES,
    GROUP_CONCAT(
      DISTINCT OCG.name1
      ORDER BY OCG.name1
      ASC SEPARATOR ', ')
                                           AS CONTACTGROUP,
    ( SELECT N.start_time 
      FROM ".$BACKEND."_notifications AS N
        WHERE N.object_id = SS.service_object_id
      GROUP BY N.start_time DESC
      LIMIT 1 )                            AS LASTNOTIFY,
    unix_timestamp(SS.next_notification) - unix_timestamp()
                                           AS NEXTTIMENOTIFYDIFF,
    SS.next_notification                   AS NEXTTIMENOTIFY,
    SS.current_notification_number         AS COUNTNOTIFY,
    SUBSTRING_INDEX(SS.check_command,'!',1)
                                           AS CHECKNAME,
    ( SELECT 
      concat_ws(';', ACO.author_name, ACO.comment_data)
      FROM ".$BACKEND."_comments AS ACO
      WHERE ACO.object_id = SS.service_object_id
      AND ACO.entry_type = 4
      AND ACO.author_name != '(Nagios Process)'
      ORDER BY ACO.comment_id DESC
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
      FROM ".$BACKEND."_comments AS NCO
      WHERE NCO.object_id = SS.service_object_id
      AND NCO.entry_type = 1
      AND NCO.comment_source = 1
      AND substring_index(NCO.comment_data, ':', 1) = '~disable'
      ORDER BY NCO.comment_id DESC
      LIMIT 1 )                            AS NOTIFCOMMENT, 
    ( SELECT 
      concat_ws(';', CO.author_name, CO.comment_data)
      FROM ".$BACKEND."_comments AS CO
      WHERE CO.object_id = SS.service_object_id
      AND CO.entry_type = 1
      AND CO.comment_source = 1
      AND substring(CO.comment_data, 1, 1) != '~'
      ORDER BY CO.comment_id DESC
      LIMIT 1 )                            AS COMMENT

  FROM
    ".$BACKEND."_servicestatus AS SS
    INNER JOIN ".$BACKEND."_services AS S                 ON S.service_object_id = SS.service_object_id
    INNER JOIN ".$BACKEND."_hosts AS H                    ON H.host_object_id = S.host_object_id
    LEFT  JOIN ".$BACKEND."_hostgroup_members AS HGM      ON H.host_object_id = HGM.host_object_id
    LEFT  JOIN ".$BACKEND."_hostgroups AS HG              ON HGM.hostgroup_id = HG.hostgroup_id
    LEFT  JOIN ".$BACKEND."_objects AS OHG                ON HG.hostgroup_object_id = OHG.object_id
    LEFT  JOIN ".$BACKEND."_service_contactgroups AS SCG  ON SS.servicestatus_id = SCG.service_id
    LEFT  JOIN ".$BACKEND."_objects AS OCG                ON SCG.contactgroup_object_id = OCG.object_id
  WHERE
    SS.servicestatus_id = define_my_id
  GROUP BY 
    SS.servicestatus_id
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
    unix_timestamp() - unix_timestamp(HS.last_time_up)
                                           AS LASTTIMEOKDIFF,
    HS.last_time_up                        AS LASTTIMEOK,
    HS.output                              AS OUTPUT,
    HS.current_check_attempt               AS CURATTEMP,
    HS.max_check_attempts                  AS MAXATTEMP,
    HS.normal_check_interval               AS NORMALINTERVAL,
    HS.retry_check_interval                AS RETRYINTERVAL,
    HS.check_type                          AS CHKTYPE,
    ( CASE HS.check_type 
        WHEN 0 THEN HS.active_checks_enabled
        WHEN 1 THEN HS.passive_checks_enabled
      END )                                AS CHECKENABLE,
    HS.perfdata                            AS PERFDATA,
    HS.latency                             AS LATENCY,
    HS.execution_time                      AS EXEC_TIME,
    HS.problem_has_been_acknowledged       AS ACK,
    HS.scheduled_downtime_depth            AS DOWNTIME,
    HS.notifications_enabled               AS NOTIF,
    HS.check_type                          AS CHECKTYPE,
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
    HS.next_check                          AS NEXTCHECKTIME,
    unix_timestamp(HS.next_check) - unix_timestamp()
                                           AS NEXTCHECKTIMEDIFF,
    GROUP_CONCAT(
      DISTINCT OHG.name1
      ORDER BY OHG.name1
      DESC SEPARATOR ', ')
                                           AS GROUPES,
    GROUP_CONCAT(
      DISTINCT OCG.name1
      ORDER BY OCG.name1
      ASC SEPARATOR ', ')
                                           AS CONTACTGROUP,
    ( SELECT N.start_time 
      FROM ".$BACKEND."_notifications AS N
        WHERE N.object_id = HS.host_object_id
      GROUP BY N.start_time DESC
      LIMIT 1 )                            AS LASTNOTIFY,
    unix_timestamp(HS.next_notification) - unix_timestamp()
                                           AS NEXTTIMENOTIFYDIFF,
    HS.next_notification                   AS NEXTTIMENOTIFY,
    HS.current_notification_number         AS COUNTNOTIFY,
    SUBSTRING_INDEX(HS.check_command,'!',1)
                                           AS CHECKNAME,
    ( SELECT 
      concat_ws(';', ACO.author_name, ACO.comment_data)
      FROM ".$BACKEND."_comments AS ACO
      WHERE ACO.object_id = HS.host_object_id
      AND ACO.entry_type = 4
      AND ACO.author_name != '(Nagios Process)'
      ORDER BY ACO.comment_id DESC
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
      FROM ".$BACKEND."_comments AS NCO
      WHERE NCO.object_id = HS.host_object_id
      AND NCO.entry_type = 1
      AND NCO.comment_source = 1
      AND substring_index(NCO.comment_data, ':', 1) = '~disable'
      ORDER BY NCO.comment_id DESC
      LIMIT 1 )                            AS NOTIFCOMMENT, 
    ( SELECT 
      concat_ws(';', CO.author_name, CO.comment_data)
      FROM ".$BACKEND."_comments AS CO
      WHERE CO.object_id = HS.host_object_id
      AND CO.entry_type = 1
      AND CO.comment_source = 1
      AND substring(CO.comment_data, 1, 1) != '~'
      ORDER BY CO.comment_id DESC
      LIMIT 1 )                            AS COMMENT

  FROM
    ".$BACKEND."_hoststatus AS HS
    INNER JOIN ".$BACKEND."_hosts AS H                    ON H.host_object_id = HS.host_object_id
    LEFT  JOIN ".$BACKEND."_hostgroup_members AS HGM      ON H.host_object_id = HGM.host_object_id
    LEFT  JOIN ".$BACKEND."_hostgroups AS HG              ON HGM.hostgroup_id = HG.hostgroup_id
    LEFT  JOIN ".$BACKEND."_objects AS OHG                ON HG.hostgroup_object_id = OHG.object_id
    LEFT  JOIN ".$BACKEND."_host_contactgroups AS HCG     ON HS.hoststatus_id = HCG.host_id
    LEFT  JOIN ".$BACKEND."_objects AS OCG                ON HCG.contactgroup_object_id = OCG.object_id
  WHERE
    HS.hoststatus_id = define_my_id
  GROUP BY 
    HS.hoststatus_id
" ;

?>
