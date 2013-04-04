<?php 
/*
  OpenPOM
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
*/


$QUERY_DOWNTIME_MIXED_ID = "
  select
    d.internal_downtime_id as id
  from
    ".$BACKEND."_scheduleddowntime AS d
  inner join
    ".$BACKEND."_objects AS o on o.object_id = d.object_id
  where
    o.name1 = define_host and
    o.name2 = define_svc and
    d.downtime_type in (1, 2);
";

?>
