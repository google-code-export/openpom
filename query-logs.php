<?php

$QUERY_LOGS = "
SELECT 
  SQL_CALC_FOUND_ROWS
  logentry_type AS type,
  entry_time,
  logentry_data AS outputstatus
FROM
  ".$BACKEND."_logentries
ORDER BY 
  define_my_sort define_my_order
LIMIT 
  define_my_first, define_my_step
" ;

?>
