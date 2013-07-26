<?php

$QUERY_GLOBAL_COUNT = "

SELECT
  SS.current_state,
  SS.problem_has_been_acknowledged,
  SS.scheduled_downtime_depth,
  SS.notifications_enabled,
  SS.active_checks_enabled,
  SS.passive_checks_enabled
FROM
       ${BACKEND}_hosts AS H
  JOIN ${BACKEND}_services AS S ON H.host_object_id = S.host_object_id
  JOIN ${BACKEND}_servicestatus AS SS ON S.service_object_id = SS.service_object_id
  JOIN ${BACKEND}_host_contactgroups AS HCG ON H.host_id = HCG.host_id
  JOIN ${BACKEND}_contactgroups AS CG ON HCG.contactgroup_object_id = CG.contactgroup_object_id
  JOIN ${BACKEND}_contactgroup_members AS CGM ON CG.contactgroup_id = CGM.contactgroup_id
  JOIN ${BACKEND}_contacts AS C ON CGM.contact_object_id = C.contact_object_id
  JOIN ${BACKEND}_objects AS CO ON C.contact_object_id = CO.object_id
WHERE
  CO.name1 = 'define_my_user'

UNION ALL

SELECT
  ( CASE HS.current_state
      WHEN 2 THEN 3
      WHEN 1 THEN 2
      ELSE HS.current_state
    END
  ) current_state,
  HS.problem_has_been_acknowledged,
  HS.scheduled_downtime_depth,
  HS.notifications_enabled,
  HS.active_checks_enabled,
  HS.passive_checks_enabled
FROM
       ${BACKEND}_hosts H
  JOIN ${BACKEND}_hoststatus HS ON H.host_object_id = HS.host_object_id
  JOIN ${BACKEND}_host_contactgroups AS HCG ON H.host_id = HCG.host_id
  JOIN ${BACKEND}_contactgroups AS CG ON HCG.contactgroup_object_id = CG.contactgroup_object_id
  JOIN ${BACKEND}_contactgroup_members AS CGM ON CG.contactgroup_id = CGM.contactgroup_id
  JOIN ${BACKEND}_contacts AS C ON CGM.contact_object_id = C.contact_object_id
  JOIN ${BACKEND}_objects AS CO ON C.contact_object_id = CO.object_id
WHERE
  CO.name1 = 'define_my_user'

";

?>
