<?php

$QUERY_GLOBAL_COUNT = "
SELECT
  sub.type    AS TYPE,
  sub.state   AS STATE,
  sub.total   AS TOTAL

FROM (

  SELECT 
    'state_svc'                 AS type,
    _SS.current_state           AS state,
    COUNT(*)                    AS total
  FROM
         ${BACKEND}_services AS _S
    JOIN ${BACKEND}_servicestatus AS _SS ON _S.service_object_id = _SS.service_object_id
  WHERE
    _S.service_id IN (
        SELECT DISTINCT S.service_id
        FROM ${BACKEND}_services AS S
        JOIN ${BACKEND}_service_contactgroups AS SCG   ON S.service_id = SCG.service_id
        JOIN ${BACKEND}_contactgroups AS CG            ON SCG.contactgroup_object_id = CG.contactgroup_object_id
        JOIN ${BACKEND}_contactgroup_members AS CGM    ON CG.contactgroup_id = CGM.contactgroup_id
        JOIN ${BACKEND}_contacts AS C                  ON CGM.contact_object_id = C.contact_object_id
        JOIN ${BACKEND}_objects AS O                   ON C.contact_object_id = O.object_id
        WHERE O.name1 = 'define_my_user'
    )
  GROUP BY
    _SS.current_state

UNION

  SELECT
    'ack_svc'                   AS type,
    NULL                        AS state,
    COUNT(*)                    AS total
  FROM
         ${BACKEND}_services AS _S
    JOIN ${BACKEND}_servicestatus AS _SS ON _S.service_object_id = _SS.service_object_id
  WHERE
    _SS.problem_has_been_acknowledged = 1 AND
    _S.service_id IN (
        SELECT DISTINCT S.service_id
        FROM ${BACKEND}_services AS S
        JOIN ${BACKEND}_service_contactgroups AS SCG   ON S.service_id = SCG.service_id
        JOIN ${BACKEND}_contactgroups AS CG            ON SCG.contactgroup_object_id = CG.contactgroup_object_id
        JOIN ${BACKEND}_contactgroup_members AS CGM    ON CG.contactgroup_id = CGM.contactgroup_id
        JOIN ${BACKEND}_contacts AS C                  ON CGM.contact_object_id = C.contact_object_id
        JOIN ${BACKEND}_objects AS O                   ON C.contact_object_id = O.object_id
        WHERE O.name1 = 'define_my_user'
    )

UNION

  SELECT
    'dt_svc'                    AS type,
    NULL                        AS state,
    COUNT(*)                    AS total
  FROM
         ${BACKEND}_services AS _S
    JOIN ${BACKEND}_servicestatus AS _SS ON _S.service_object_id = _SS.service_object_id
  WHERE
    _SS.scheduled_downtime_depth > 0 AND
    _S.service_id IN (
        SELECT DISTINCT S.service_id
        FROM ${BACKEND}_services AS S
        JOIN ${BACKEND}_service_contactgroups AS SCG   ON S.service_id = SCG.service_id
        JOIN ${BACKEND}_contactgroups AS CG            ON SCG.contactgroup_object_id = CG.contactgroup_object_id
        JOIN ${BACKEND}_contactgroup_members AS CGM    ON CG.contactgroup_id = CGM.contactgroup_id
        JOIN ${BACKEND}_contacts AS C                  ON CGM.contact_object_id = C.contact_object_id
        JOIN ${BACKEND}_objects AS O                   ON C.contact_object_id = O.object_id
        WHERE O.name1 = 'define_my_user'
    )

UNION

  SELECT
    'disanotif_svc'             AS type,
    NULL                        AS state,
    COUNT(*)                    AS total
  FROM
         ${BACKEND}_services AS _S
    JOIN ${BACKEND}_servicestatus AS _SS ON _S.service_object_id = _SS.service_object_id
  WHERE
    _SS.notifications_enabled = 0 AND
    _S.service_id IN (
        SELECT DISTINCT S.service_id
        FROM ${BACKEND}_services AS S
        JOIN ${BACKEND}_service_contactgroups AS SCG   ON S.service_id = SCG.service_id
        JOIN ${BACKEND}_contactgroups AS CG            ON SCG.contactgroup_object_id = CG.contactgroup_object_id
        JOIN ${BACKEND}_contactgroup_members AS CGM    ON CG.contactgroup_id = CGM.contactgroup_id
        JOIN ${BACKEND}_contacts AS C                  ON CGM.contact_object_id = C.contact_object_id
        JOIN ${BACKEND}_objects AS O                   ON C.contact_object_id = O.object_id
        WHERE O.name1 = 'define_my_user'
    )

UNION

  SELECT
    'disacheck_svc'             AS type,
    NULL                        AS state,
    COUNT(*)                    AS total
  FROM
         ${BACKEND}_services AS _S
    JOIN ${BACKEND}_servicestatus AS _SS ON _S.service_object_id = _SS.service_object_id
  WHERE
    _SS.active_checks_enabled = 0 AND
    _SS.passive_checks_enabled = 0 AND
    _S.service_id IN (
        SELECT DISTINCT S.service_id
        FROM ${BACKEND}_services AS S
        JOIN ${BACKEND}_service_contactgroups AS SCG   ON S.service_id = SCG.service_id
        JOIN ${BACKEND}_contactgroups AS CG            ON SCG.contactgroup_object_id = CG.contactgroup_object_id
        JOIN ${BACKEND}_contactgroup_members AS CGM    ON CG.contactgroup_id = CGM.contactgroup_id
        JOIN ${BACKEND}_contacts AS C                  ON CGM.contact_object_id = C.contact_object_id
        JOIN ${BACKEND}_objects AS O                   ON C.contact_object_id = O.object_id
        WHERE O.name1 = 'define_my_user'
    )

UNION

  SELECT
    'state_host'                AS type,
    ( CASE _HS.current_state
        WHEN 2 THEN 3
        WHEN 1 THEN 2
        WHEN 0 THEN 0
      END )                     AS state,
    COUNT(*)                    AS total
  FROM
         ${BACKEND}_hosts AS _H
    JOIN ${BACKEND}_hoststatus AS _HS ON _H.host_object_id = _HS.host_object_id
  WHERE
    _H.host_id IN (
        SELECT DISTINCT H.host_id
        FROM ${BACKEND}_hosts AS H
        JOIN ${BACKEND}_host_contactgroups AS HCG      ON H.host_id = HCG.host_id
        JOIN ${BACKEND}_contactgroups AS CG            ON HCG.contactgroup_object_id = CG.contactgroup_object_id
        JOIN ${BACKEND}_contactgroup_members AS CGM    ON CG.contactgroup_id = CGM.contactgroup_id
        JOIN ${BACKEND}_contacts AS C                  ON CGM.contact_object_id = C.contact_object_id
        JOIN ${BACKEND}_objects AS O                   ON C.contact_object_id = O.object_id
        WHERE O.name1 = 'define_my_user'
    )
  GROUP BY 
    _HS.current_state

UNION

  SELECT
    'ack_host'                  AS type,
    NULL                        AS state,
    COUNT(*)                    AS total
  FROM
         ${BACKEND}_hosts AS _H
    JOIN ${BACKEND}_hoststatus AS _HS ON _H.host_object_id = _HS.host_object_id
  WHERE
    _HS.problem_has_been_acknowledged = 1 AND
    _H.host_id IN (
        SELECT DISTINCT H.host_id
        FROM ${BACKEND}_hosts AS H
        JOIN ${BACKEND}_host_contactgroups AS HCG      ON H.host_id = HCG.host_id
        JOIN ${BACKEND}_contactgroups AS CG            ON HCG.contactgroup_object_id = CG.contactgroup_object_id
        JOIN ${BACKEND}_contactgroup_members AS CGM    ON CG.contactgroup_id = CGM.contactgroup_id
        JOIN ${BACKEND}_contacts AS C                  ON CGM.contact_object_id = C.contact_object_id
        JOIN ${BACKEND}_objects AS O                   ON C.contact_object_id = O.object_id
        WHERE O.name1 = 'define_my_user'
    )

UNION

  SELECT
    'dt_host'                   AS type,
    NULL                        AS state,
    COUNT(*)                    AS total
  FROM
         ${BACKEND}_hosts AS _H
    JOIN ${BACKEND}_hoststatus AS _HS ON _H.host_object_id = _HS.host_object_id
  WHERE
    _HS.scheduled_downtime_depth > 0 AND
    _H.host_id IN (
        SELECT DISTINCT H.host_id
        FROM ${BACKEND}_hosts AS H
        JOIN ${BACKEND}_host_contactgroups AS HCG      ON H.host_id = HCG.host_id
        JOIN ${BACKEND}_contactgroups AS CG            ON HCG.contactgroup_object_id = CG.contactgroup_object_id
        JOIN ${BACKEND}_contactgroup_members AS CGM    ON CG.contactgroup_id = CGM.contactgroup_id
        JOIN ${BACKEND}_contacts AS C                  ON CGM.contact_object_id = C.contact_object_id
        JOIN ${BACKEND}_objects AS O                   ON C.contact_object_id = O.object_id
        WHERE O.name1 = 'define_my_user'
    )

UNION

  SELECT
    'disanotif_host'            AS type,
    NULL                        AS state,
    COUNT(*)                    AS total
  FROM
         ${BACKEND}_hosts AS _H
    JOIN ${BACKEND}_hoststatus AS _HS ON _H.host_object_id = _HS.host_object_id
  WHERE
    _HS.notifications_enabled = 0 AND
    _H.host_id IN (
        SELECT DISTINCT H.host_id
        FROM ${BACKEND}_hosts AS H
        JOIN ${BACKEND}_host_contactgroups AS HCG      ON H.host_id = HCG.host_id
        JOIN ${BACKEND}_contactgroups AS CG            ON HCG.contactgroup_object_id = CG.contactgroup_object_id
        JOIN ${BACKEND}_contactgroup_members AS CGM    ON CG.contactgroup_id = CGM.contactgroup_id
        JOIN ${BACKEND}_contacts AS C                  ON CGM.contact_object_id = C.contact_object_id
        JOIN ${BACKEND}_objects AS O                   ON C.contact_object_id = O.object_id
        WHERE O.name1 = 'define_my_user'
    )

UNION

  SELECT
    'disacheck_host'            AS type,
    NULL                        AS state,
    COUNT(*)                    AS total
  FROM
         ${BACKEND}_hosts AS _H
    JOIN ${BACKEND}_hoststatus AS _HS ON _H.host_object_id = _HS.host_object_id
  WHERE
    _HS.active_checks_enabled = 0 AND
    _HS.passive_checks_enabled = 0 AND
    _H.host_id IN (
        SELECT DISTINCT H.host_id
        FROM ${BACKEND}_hosts AS H
        JOIN ${BACKEND}_host_contactgroups AS HCG      ON H.host_id = HCG.host_id
        JOIN ${BACKEND}_contactgroups AS CG            ON HCG.contactgroup_object_id = CG.contactgroup_object_id
        JOIN ${BACKEND}_contactgroup_members AS CGM    ON CG.contactgroup_id = CGM.contactgroup_id
        JOIN ${BACKEND}_contacts AS C                  ON CGM.contact_object_id = C.contact_object_id
        JOIN ${BACKEND}_objects AS O                   ON C.contact_object_id = O.object_id
        WHERE O.name1 = 'define_my_user'
    )

) AS sub

" ;

?>
