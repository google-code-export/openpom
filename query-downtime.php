<?php 
/*
  OpenPOM
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
*/


$QUERY_DOWNTIME_HOST_ID = "
  SELECT o.name1,o.name2,d.internal_downtime_id
  FROM ".$BACKEND."_scheduleddowntime AS d
  JOIN ".$BACKEND."_objects AS o
  ON o.object_id = d.object_id WHERE downtime_type = 2
  AND o.name1 = 'define_mhost'
";

$QUERY_DOWNTIME_SVC_ID = "
  SELECT o.name1,o.name2,d.internal_downtime_id
  FROM ".$BACKEND."_scheduleddowntime AS d
  JOIN ".$BACKEND."_objects AS o
  ON o.object_id = d.object_id WHERE downtime_type = 1
  AND o.name1 = 'define_mhost'
  AND o.name2 = 'define_msvc'
";

?>
