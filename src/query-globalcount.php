<?php

$QUERY_GLOBAL_COUNT = "
SELECT
  sub.type    AS TYPE,
  sub.state   AS STATE,
  sub.total   AS TOTAL

FROM (

  SELECT 
    'current_state_svc'       AS type,
    SS.current_state          AS state,
    COUNT( SS.current_state ) AS total
  FROM
         ".$BACKEND."_services AS S
    JOIN ".$BACKEND."_servicestatus AS SS ON S.service_object_id = SS.service_object_id
  WHERE
    'define_my_user' IN (
	    SELECT DISTINCT _O.name1
        FROM ".$BACKEND."_services AS _S
        JOIN ".$BACKEND."_service_contactgroups AS _SCG ON _S.service_id = _SCG.service_id
        JOIN ".$BACKEND."_contactgroups AS _CG          ON _SCG.contactgroup_object_id = _CG.contactgroup_object_id
        JOIN ".$BACKEND."_contactgroup_members AS _CGM  ON _CG.contactgroup_id = _CGM.contactgroup_id
        JOIN ".$BACKEND."_contacts AS _C                ON _CGM.contact_object_id = _C.contact_object_id
        JOIN ".$BACKEND."_objects AS _O                 ON _C.contact_object_id = _O.object_id
        WHERE _S.service_id = S.service_id
    )
  GROUP BY
    SS.current_state

UNION

  SELECT
    'acknowledgesvc'                          AS type,
    SS.problem_has_been_acknowledged          AS state,
    COUNT( SS.problem_has_been_acknowledged ) AS total
  FROM
         ".$BACKEND."_services AS S
    JOIN ".$BACKEND."_servicestatus AS SS ON S.service_object_id = SS.service_object_id
  WHERE
    'define_my_user' IN (
	    SELECT DISTINCT _O.name1
        FROM ".$BACKEND."_services AS _S
        JOIN ".$BACKEND."_service_contactgroups AS _SCG ON _S.service_id = _SCG.service_id
        JOIN ".$BACKEND."_contactgroups AS _CG          ON _SCG.contactgroup_object_id = _CG.contactgroup_object_id
        JOIN ".$BACKEND."_contactgroup_members AS _CGM  ON _CG.contactgroup_id = _CGM.contactgroup_id
        JOIN ".$BACKEND."_contacts AS _C                ON _CGM.contact_object_id = _C.contact_object_id
        JOIN ".$BACKEND."_objects AS _O                 ON _C.contact_object_id = _O.object_id
        WHERE _S.service_id = S.service_id
    )
  GROUP BY
    SS.problem_has_been_acknowledged

UNION

  SELECT
    'downtimesvc'                        AS type,
    SS.scheduled_downtime_depth          AS state,
    COUNT( SS.scheduled_downtime_depth ) AS total
  FROM
         ".$BACKEND."_services AS S
    JOIN ".$BACKEND."_servicestatus AS SS ON S.service_object_id = SS.service_object_id
  WHERE
    'define_my_user' IN (
	    SELECT DISTINCT _O.name1
        FROM ".$BACKEND."_services AS _S
        JOIN ".$BACKEND."_service_contactgroups AS _SCG ON _S.service_id = _SCG.service_id
        JOIN ".$BACKEND."_contactgroups AS _CG          ON _SCG.contactgroup_object_id = _CG.contactgroup_object_id
        JOIN ".$BACKEND."_contactgroup_members AS _CGM  ON _CG.contactgroup_id = _CGM.contactgroup_id
        JOIN ".$BACKEND."_contacts AS _C                ON _CGM.contact_object_id = _C.contact_object_id
        JOIN ".$BACKEND."_objects AS _O                 ON _C.contact_object_id = _O.object_id
        WHERE _S.service_id = S.service_id
    )
  GROUP BY
    SS.scheduled_downtime_depth

UNION

  SELECT
    'disanotifsvc'                    AS type,
    SS.notifications_enabled          AS state,
    COUNT( SS.notifications_enabled ) AS total
  FROM
         ".$BACKEND."_services AS S
    JOIN ".$BACKEND."_servicestatus AS SS ON S.service_object_id = SS.service_object_id
  WHERE
    'define_my_user' IN (
	    SELECT DISTINCT _O.name1
        FROM ".$BACKEND."_services AS _S
        JOIN ".$BACKEND."_service_contactgroups AS _SCG ON _S.service_id = _SCG.service_id
        JOIN ".$BACKEND."_contactgroups AS _CG          ON _SCG.contactgroup_object_id = _CG.contactgroup_object_id
        JOIN ".$BACKEND."_contactgroup_members AS _CGM  ON _CG.contactgroup_id = _CGM.contactgroup_id
        JOIN ".$BACKEND."_contacts AS _C                ON _CGM.contact_object_id = _C.contact_object_id
        JOIN ".$BACKEND."_objects AS _O                 ON _C.contact_object_id = _O.object_id
        WHERE _S.service_id = S.service_id
    )
  GROUP BY
    SS.notifications_enabled

UNION

  SELECT
    'disachecksvc'                    AS type,
    SS.active_checks_enabled          AS state,
    COUNT( SS.active_checks_enabled ) AS total
  FROM
         ".$BACKEND."_services AS S
    JOIN ".$BACKEND."_servicestatus AS SS ON S.service_object_id = SS.service_object_id
  WHERE
    'define_my_user' IN (
	    SELECT DISTINCT _O.name1
        FROM ".$BACKEND."_services AS _S
        JOIN ".$BACKEND."_service_contactgroups AS _SCG ON _S.service_id = _SCG.service_id
        JOIN ".$BACKEND."_contactgroups AS _CG          ON _SCG.contactgroup_object_id = _CG.contactgroup_object_id
        JOIN ".$BACKEND."_contactgroup_members AS _CGM  ON _CG.contactgroup_id = _CGM.contactgroup_id
        JOIN ".$BACKEND."_contacts AS _C                ON _CGM.contact_object_id = _C.contact_object_id
        JOIN ".$BACKEND."_objects AS _O                 ON _C.contact_object_id = _O.object_id
        WHERE _S.service_id = S.service_id
    )
    AND SS.check_type = 0
  GROUP BY
    SS.active_checks_enabled

UNION

  SELECT
    'current_state_host'      AS type,
    ( CASE HS.current_state
        WHEN 2 THEN 3
        WHEN 1 THEN 2
        WHEN 0 THEN 0
      END )                   AS state,
    COUNT( HS.current_state ) AS total
  FROM
         ".$BACKEND."_hosts AS H
    JOIN ".$BACKEND."_hoststatus AS HS ON H.host_object_id = HS.host_object_id
  WHERE
    'define_my_user' IN (
	    SELECT DISTINCT _O.name1
        FROM ".$BACKEND."_hosts AS _H
        JOIN ".$BACKEND."_host_contactgroups AS _HCG   ON _H.host_id = _HCG.host_id
        JOIN ".$BACKEND."_contactgroups AS _CG         ON _HCG.contactgroup_object_id = _CG.contactgroup_object_id
        JOIN ".$BACKEND."_contactgroup_members AS _CGM ON _CG.contactgroup_id = _CGM.contactgroup_id
        JOIN ".$BACKEND."_contacts AS _C               ON _CGM.contact_object_id = _C.contact_object_id
        JOIN ".$BACKEND."_objects AS _O                ON _C.contact_object_id = _O.object_id
        WHERE _H.host_id = H.host_id
    )
  GROUP BY 
    HS.current_state

UNION

  SELECT
    'acknowledgehost'                         AS type,
    HS.problem_has_been_acknowledged          AS state,
    COUNT( HS.problem_has_been_acknowledged ) AS total
  FROM
         ".$BACKEND."_hosts AS H
    JOIN ".$BACKEND."_hoststatus AS HS ON H.host_object_id = HS.host_object_id
  WHERE
    'define_my_user' IN (
	    SELECT DISTINCT _O.name1
        FROM ".$BACKEND."_hosts AS _H
        JOIN ".$BACKEND."_host_contactgroups AS _HCG   ON _H.host_id = _HCG.host_id
        JOIN ".$BACKEND."_contactgroups AS _CG         ON _HCG.contactgroup_object_id = _CG.contactgroup_object_id
        JOIN ".$BACKEND."_contactgroup_members AS _CGM ON _CG.contactgroup_id = _CGM.contactgroup_id
        JOIN ".$BACKEND."_contacts AS _C               ON _CGM.contact_object_id = _C.contact_object_id
        JOIN ".$BACKEND."_objects AS _O                ON _C.contact_object_id = _O.object_id
        WHERE _H.host_id = H.host_id
    )
  GROUP BY 
    HS.problem_has_been_acknowledged

UNION

  SELECT
    'downtimehost'                       AS type,
    HS.scheduled_downtime_depth          AS state,
    COUNT( HS.scheduled_downtime_depth ) AS total
  FROM
         ".$BACKEND."_hosts AS H
    JOIN ".$BACKEND."_hoststatus AS HS ON H.host_object_id = HS.host_object_id
  WHERE
    'define_my_user' IN (
	    SELECT DISTINCT _O.name1
        FROM ".$BACKEND."_hosts AS _H
        JOIN ".$BACKEND."_host_contactgroups AS _HCG   ON _H.host_id = _HCG.host_id
        JOIN ".$BACKEND."_contactgroups AS _CG         ON _HCG.contactgroup_object_id = _CG.contactgroup_object_id
        JOIN ".$BACKEND."_contactgroup_members AS _CGM ON _CG.contactgroup_id = _CGM.contactgroup_id
        JOIN ".$BACKEND."_contacts AS _C               ON _CGM.contact_object_id = _C.contact_object_id
        JOIN ".$BACKEND."_objects AS _O                ON _C.contact_object_id = _O.object_id
        WHERE _H.host_id = H.host_id
    )
  GROUP BY 
    HS.scheduled_downtime_depth

UNION

  SELECT
    'disanotifhost'                   AS type,
    HS.notifications_enabled          AS state,
    COUNT( HS.notifications_enabled ) AS total
  FROM
         ".$BACKEND."_hosts AS H
    JOIN ".$BACKEND."_hoststatus AS HS ON H.host_object_id = HS.host_object_id
  WHERE
    'define_my_user' IN (
	    SELECT DISTINCT _O.name1
        FROM ".$BACKEND."_hosts AS _H
        JOIN ".$BACKEND."_host_contactgroups AS _HCG   ON _H.host_id = _HCG.host_id
        JOIN ".$BACKEND."_contactgroups AS _CG         ON _HCG.contactgroup_object_id = _CG.contactgroup_object_id
        JOIN ".$BACKEND."_contactgroup_members AS _CGM ON _CG.contactgroup_id = _CGM.contactgroup_id
        JOIN ".$BACKEND."_contacts AS _C               ON _CGM.contact_object_id = _C.contact_object_id
        JOIN ".$BACKEND."_objects AS _O                ON _C.contact_object_id = _O.object_id
        WHERE _H.host_id = H.host_id
    )
  GROUP BY 
    HS.notifications_enabled

UNION

  SELECT
    'disacheckhost'                   AS type,
    HS.active_checks_enabled          AS state,
    COUNT( HS.active_checks_enabled ) AS total
  FROM
         ".$BACKEND."_hosts AS H
    JOIN ".$BACKEND."_hoststatus AS HS ON H.host_object_id = HS.host_object_id
  WHERE
    'define_my_user' IN (
	    SELECT DISTINCT _O.name1
        FROM ".$BACKEND."_hosts AS _H
        JOIN ".$BACKEND."_host_contactgroups AS _HCG   ON _H.host_id = _HCG.host_id
        JOIN ".$BACKEND."_contactgroups AS _CG         ON _HCG.contactgroup_object_id = _CG.contactgroup_object_id
        JOIN ".$BACKEND."_contactgroup_members AS _CGM ON _CG.contactgroup_id = _CGM.contactgroup_id
        JOIN ".$BACKEND."_contacts AS _C               ON _CGM.contact_object_id = _C.contact_object_id
        JOIN ".$BACKEND."_objects AS _O                ON _C.contact_object_id = _O.object_id
        WHERE _H.host_id = H.host_id
    )
    AND HS.check_type = 0
  GROUP BY 
    HS.active_checks_enabled

) AS sub

" ;

?>
