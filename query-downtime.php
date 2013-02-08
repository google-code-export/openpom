<?php 
/*
  OpenPOM
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
*/


$QUERY_DOWNTIME_MIXED_ID = "
  SELECT 
    d.internal_downtime_id as id, 
    if(d.downtime_type = 1, 'svc', 'host') as type
  FROM ".$BACKEND."_scheduleddowntime AS d
  JOIN ".$BACKEND."_objects AS o ON o.object_id = d.object_id
  WHERE o.name1 = define_host
  AND o.name2 = define_svc
  AND d.downtime_type IN (1, 2)
";

?>
