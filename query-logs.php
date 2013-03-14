<?php

$QUERY_LOGS = "
SELECT
  sub.type         AS type,
  sub.entry_time   AS entry_time,
  sub.outputstatus AS outputstatus
  
FROM ((

  SELECT 
    logentry_type AS type,
    entry_time,
    logentry_data AS outputstatus
  FROM
    ".$BACKEND."_logentries
  ORDER BY
    entry_time DESC
  LIMIT
    define_my_submax

) UNION (

  SELECT
    event_type AS type,
    event_time AS entry_time,
    CONCAT_WS( '', program_name, ' ',
               ( CASE event_type  
                   WHEN 100 THEN 'Starting'
                   WHEN 101 THEN 'Daemonized'
                   WHEN 102 THEN 'Restart'
                   WHEN 103 THEN 'Shutdown'
                   WHEN 104 THEN 'Prelaunch'
                   WHEN 105 THEN 'Event loop start'
                   WHEN 106 THEN 'Event loop end'
                 END), 
               ' (', program_version, ' PID: ', process_id, ')' ) 
               AS outpustatus
  FROM
    ".$BACKEND."_processevents
  ORDER BY
    event_time DESC
  LIMIT
    define_my_submax

)) AS sub

  ORDER BY 
    sub.entry_time DESC

  LIMIT 
    define_my_first, define_my_step
" ;

$QUERY_LOGS_TOTAL = "
select 
  (select count(*) from ".$BACKEND."_logentries) + 
  (select count(*) from ".$BACKEND."_processevents)
";

?>
