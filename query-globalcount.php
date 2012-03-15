<?php

$QUERY_GLOBAL_COUNT = "
SELECT
  sub.state		AS STATE,   
  sub.c_state		AS NSTATE,
  sub.ack		AS ACK,
  sub.c_ack		AS NACK,
  sub.down		AS DOWN,
  sub.c_down		AS NDOWN,
  sub.notif		AS NOTIF,
  sub.c_notif		AS NNOTIF,
  sub.check_ena		AS SCHECK,
  sub.c_check_ena	AS NCHECK

FROM (

  SELECT
    SS.current_state 				AS state,
    count(SS.current_state)        		AS c_state,
    SS.problem_has_been_acknowledged		AS ack,
    count(SS.problem_has_been_acknowledged)	AS c_ack,
    SS.scheduled_downtime_depth			AS down,
    count(SS.scheduled_downtime_depth)		AS c_down,
    SS.notifications_enabled			AS notif,
    count(SS.notifications_enabled)		AS c_notif,
    '1' 					AS check_ena,
    'O' 					AS c_check_ena

  FROM
         ".$BACKEND."_services AS S
    JOIN ".$BACKEND."_servicestatus AS SS          ON S.service_object_id = SS.service_object_id
    JOIN ".$BACKEND."_service_contactgroups AS SCG ON SCG.service_id = S.service_id
    JOIN ".$BACKEND."_contactgroups AS HCG         ON SCG.contactgroup_object_id = HCG.contactgroup_object_id
    JOIN ".$BACKEND."_contactgroup_members AS CGM  ON CGM.contactgroup_id = HCG.contactgroup_id
    JOIN ".$BACKEND."_contacts AS C                ON C.contact_object_id = CGM.contact_object_id
    JOIN ".$BACKEND."_objects AS O                 ON O.object_id = C.contact_object_id

  WHERE
    O.name1 = 'define_my_user'

  GROUP BY 
    SS.current_state, 
    SS.problem_has_been_acknowledged, 
    SS.scheduled_downtime_depth, 
    SS.notifications_enabled,
    SS.active_checks_enabled

UNION 
  
  SELECT
    '10'		AS state,
    '0'			AS c_state,
    '10'		AS ack,
    '0'			AS c_ack,
    '10'		AS down,
    '0'			AS c_down,
    '10'		AS notif,
    '0'					AS c_notif,
    SS.active_checks_enabled		AS check_ena,
    count(SS.active_checks_enabled)	AS c_check_ena

  FROM 
         ".$BACKEND."_services AS S
    JOIN ".$BACKEND."_servicestatus AS SS          ON S.service_object_id = SS.service_object_id
    JOIN ".$BACKEND."_service_contactgroups AS SCG ON SCG.service_id = S.service_id
    JOIN ".$BACKEND."_contactgroups AS HCG         ON SCG.contactgroup_object_id = HCG.contactgroup_object_id
    JOIN ".$BACKEND."_contactgroup_members AS CGM  ON CGM.contactgroup_id = HCG.contactgroup_id
    JOIN ".$BACKEND."_contacts AS C                ON C.contact_object_id = CGM.contact_object_id
    JOIN ".$BACKEND."_objects AS O                 ON O.object_id = C.contact_object_id

  WHERE
    O.name1 = 'define_my_user'
    AND SS.check_type = 0

  GROUP BY
    SS.active_checks_enabled

UNION 

  SELECT 
    ( CASE HS.current_state
        WHEN 2 THEN 3
        WHEN 1 THEN 2
        WHEN 0 THEN 0
        END )                   		AS state,
    count(HS.current_state)               	AS c_state,
    HS.problem_has_been_acknowledged		AS ack,
    count(HS.problem_has_been_acknowledged)	AS c_ack,
    HS.scheduled_downtime_depth			AS down,
    count(HS.scheduled_downtime_depth)		AS c_down,
    HS.notifications_enabled			AS notif,
    count(HS.notifications_enabled)		AS c_notif,
    '1' 					AS check_ena,
    'O' 					AS c_check_ena

  FROM
         ".$BACKEND."_hosts AS H
    JOIN ".$BACKEND."_hoststatus AS HS            ON H.host_object_id = HS.host_object_id
    JOIN ".$BACKEND."_host_contactgroups AS HCG   ON HCG.host_id = H.host_id
    JOIN ".$BACKEND."_contactgroups As OCG        ON HCG.contactgroup_object_id = OCG.contactgroup_object_id
    JOIN ".$BACKEND."_contactgroup_members AS CGM ON CGM.contactgroup_id = OCG.contactgroup_id
    JOIN ".$BACKEND."_contacts AS C               ON C.contact_object_id = CGM.contact_object_id
    JOIN ".$BACKEND."_objects AS O                ON O.object_id = C.contact_object_id

  WHERE
    O.name1 = 'define_my_user'

  GROUP BY 
    HS.current_state,
    HS.problem_has_been_acknowledged, 
    HS.scheduled_downtime_depth, 
    HS.notifications_enabled

UNION 
  
  SELECT
    '10'		AS state,
    '0'			AS c_state,
    '10'		AS ack,
    '0'			AS c_ack,
    '10'		AS down,
    '0'			AS c_down,
    '10'		AS notif,
    '0'					AS c_notif,
    HS.active_checks_enabled		AS check_ena,
    count(HS.active_checks_enabled)	AS c_check_ena

  FROM 
         ".$BACKEND."_hosts AS H
    JOIN ".$BACKEND."_hoststatus AS HS            ON H.host_object_id = HS.host_object_id
    JOIN ".$BACKEND."_host_contactgroups AS HCG   ON HCG.host_id = H.host_id
    JOIN ".$BACKEND."_contactgroups As OCG        ON HCG.contactgroup_object_id = OCG.contactgroup_object_id
    JOIN ".$BACKEND."_contactgroup_members AS CGM ON CGM.contactgroup_id = OCG.contactgroup_id
    JOIN ".$BACKEND."_contacts AS C               ON C.contact_object_id = CGM.contact_object_id
    JOIN ".$BACKEND."_objects AS O                ON O.object_id = C.contact_object_id

  WHERE
    O.name1 = 'define_my_user'
    AND HS.check_type = 0

  GROUP BY
    HS.active_checks_enabled

) AS sub

ORDER BY state DESC
"

?>
