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
    current_state 				AS state,
    count(current_state)        		AS c_state,
    problem_has_been_acknowledged		AS ack,
    count(problem_has_been_acknowledged)	AS c_ack,
    scheduled_downtime_depth			AS down,
    count(scheduled_downtime_depth)		AS c_down,
    notifications_enabled			AS notif,
    count(notifications_enabled)		AS c_notif,
    '1' 					AS check_ena,
    'O' 					AS c_check_ena

  FROM
    ".$BACKEND."_servicestatus

  GROUP BY 
    current_state, 
    problem_has_been_acknowledged, 
    scheduled_downtime_depth, 
    notifications_enabled,
    active_checks_enabled

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
    active_checks_enabled		AS check_ena,
    count(active_checks_enabled)	AS c_check_ena

  FROM 
    ".$BACKEND."_servicestatus

  WHERE 
    check_type = 0

  GROUP BY
    active_checks_enabled

UNION 

  SELECT 
    ( CASE current_state
        WHEN 2 THEN 3
        WHEN 1 THEN 2
        WHEN 0 THEN 0
        END )                   		AS state,
    count(current_state)               		AS c_state,
    problem_has_been_acknowledged		AS ack,
    count(problem_has_been_acknowledged)	AS c_ack,
    scheduled_downtime_depth			AS down,
    count(scheduled_downtime_depth)		AS c_down,
    notifications_enabled			AS notif,
    count(notifications_enabled)		AS c_notif,
    '1' 					AS check_ena,
    'O' 					AS c_check_ena

  FROM
    ".$BACKEND."_hoststatus

  WHERE 
    check_type = 0

  GROUP BY 
    current_state,
    problem_has_been_acknowledged, 
    scheduled_downtime_depth, 
    notifications_enabled

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
    active_checks_enabled		AS check_ena,
    count(active_checks_enabled)	AS c_check_ena

  FROM 
    ".$BACKEND."_hoststatus

  GROUP BY
    active_checks_enabled

) AS sub

ORDER BY state DESC
"

?>
