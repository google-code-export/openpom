<?php
/*
  OpenPOM

  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
*/

$QUERY = "
SELECT
  SQL_CALC_FOUND_ROWS
  sub.GROUPES                      AS GROUPE,
  sub.MACHINE_ALIAS                AS MACHINE_ALIAS,
  sub.MACHINE_NAME                 AS MACHINE_NAME,
  sub.ADDRESS                      AS ADDRESS,
  sub.SERVICE                      AS SERVICE,
  sub.SUBSERVICE                   AS SUBSERVICE,
  sub.STATUS                       AS STATUS,
  ( case sub.STATUS
      when 3 then 1
      when 2 then -1
      when 1 then 0
      else 10 end
  )                                AS COEF,
  sub.SVCID                        AS SVCID,
  sub.OUTPUT                       AS OUTPUT,
  sub.SVCST                        AS SVCST,
  UNIX_TIMESTAMP() - sub.DURATION  AS DURATION,
  UNIX_TIMESTAMP() - sub.LASTCHECK AS LASTCHECK,
  sub.TYPE                         AS TYPE,
  sub.ACK                          AS ACK,
  sub.DOWNTIME                     AS DOWNTIME,
  sub.NOTIF                        AS NOTIF,
  sub.COMMENT                      AS COMMENT,
  sub.DISABLECHECK                 AS DISABLECHECK,
  sub.CHECKTYPE                    AS CHECKTYPE,
  sub.CHECKNAME                    AS CHECKNAME

FROM (


-- BEGIN
-- NDO SERVICES

  SELECT
    GROUP_CONCAT(
      DISTINCT OHG.name1
      ORDER BY OHG.name1
      DESC SEPARATOR 'define_my_separator'
    )                                    AS GROUPES,
    H.alias                              AS MACHINE_ALIAS,
    H.display_name                       AS MACHINE_NAME,
    H.address                            AS ADDRESS,
    S.display_name                       AS SERVICE,
    null                                 AS SUBSERVICE,
    SS.current_state                     AS STATUS,
    SS.servicestatus_id                  AS SVCID,
    SS.output                            AS OUTPUT,
    SS.state_type                        AS SVCST,
    SS.problem_has_been_acknowledged     AS ACK,
    SS.check_type                        AS CHECKTYPE,
    ( CASE SS.check_type
        WHEN 0 THEN SS.active_checks_enabled
        WHEN 1 THEN SS.passive_checks_enabled
      END )                              AS DISABLECHECK,
    UNIX_TIMESTAMP(SS.last_check)        AS LASTCHECK,
    UNIX_TIMESTAMP(SS.last_state_change) AS DURATION,
    'svc'                                AS TYPE,
    SS.scheduled_downtime_depth          AS DOWNTIME,
    SS.notifications_enabled             AS NOTIF,
    ( SELECT BIT_OR(
        IF(substring_index(comment_data, ':', 1) = '~track', 2,
          IF(substring(comment_data, 1, 1) = '~', 0, 1) ))
      FROM ".$BACKEND."_comments AS CO
      WHERE CO.object_id = S.service_object_id
      AND CO.entry_type = 1
      AND CO.comment_source = 1
    )                                    AS COMMENT,  -- comment is 0, 1, 2 or 3
                                                      -- bit 0 is comment
                                                      -- bit 1 is track
                                                      -- 0: no comment, no track
                                                      -- 1: comment only
                                                      -- 2: track only
                                                      -- 3: comment and track
    SUBSTRING_INDEX(SS.check_command,'!',1)
                                         AS CHECKNAME

  FROM
         ".$BACKEND."_hosts AS H
    JOIN ".$BACKEND."_hoststatus AS HS             ON H.host_object_id = HS.host_object_id
    JOIN ".$BACKEND."_services AS S                ON H.host_object_id = S.host_object_id
    JOIN ".$BACKEND."_servicestatus AS SS          ON S.service_object_id = SS.service_object_id
    JOIN ".$BACKEND."_service_contactgroups AS SCG ON SCG.service_id = S.service_id
    JOIN ".$BACKEND."_contactgroups AS HCG         ON SCG.contactgroup_object_id = HCG.contactgroup_object_id
    JOIN ".$BACKEND."_contactgroup_members AS CGM  ON CGM.contactgroup_id = HCG.contactgroup_id
    JOIN ".$BACKEND."_contacts AS C                ON C.contact_object_id = CGM.contact_object_id
    JOIN ".$BACKEND."_objects AS O                 ON O.object_id = C.contact_object_id
    LEFT JOIN ".$BACKEND."_hostgroup_members AS HGM ON H.host_object_id = HGM.host_object_id
    LEFT JOIN ".$BACKEND."_hostgroups AS HG        ON HGM.hostgroup_id = HG.hostgroup_id
    LEFT JOIN ".$BACKEND."_objects AS OHG          ON HG.hostgroup_object_id = OHG.object_id

  WHERE
    define_my_svc_search
    O.name1 = 'define_my_user'
    AND ( (
            SS.current_state IN (define_my_svcfilt)
        AND (
              SS.problem_has_been_acknowledged IN (define_my_svcacklist)
          AND HS.problem_has_been_acknowledged define_my_acklistop define_my_acklistval
        )
        AND (
              SS.scheduled_downtime_depth define_my_svcdownop define_my_svcdownval
          AND HS.scheduled_downtime_depth define_my_acklistop define_my_acklistval
        )
        AND (define_my_nosvc = 0 OR HS.current_state = 0)
        AND SS.notifications_enabled IN (define_my_disable)
        AND (define_my_soft = 0 OR SS.state_type = 1)
        AND (
              ( SS.check_type = 0 AND SS.active_checks_enabled IN (define_my_check_disable) ) OR
              ( SS.check_type = 1 AND SS.passive_checks_enabled IN (define_my_check_disable) )
            )
      )
      OR (
        SELECT count(*) > 0
        FROM ".$BACKEND."_comments
        WHERE object_id = S.service_object_id
        AND entry_type = 1
        AND comment_source = 1
        AND substring_index(comment_data, ':', 1) = '~track'
        AND ( define_track_anything = 0 )
      )
    )

  GROUP BY SVCID

-- END
-- NDO SERVICES


UNION


-- BEGIN
-- NDO HOSTS

  SELECT
    GROUP_CONCAT(
      DISTINCT OHG.name1
      ORDER BY OHG.name1
      DESC SEPARATOR 'define_my_separator'
    )                                    AS GROUPES,
    H.alias                              AS MACHINE_ALIAS,
    H.display_name                       AS MACHINE_NAME,
    H.address                            AS ADDRESS,
    '--host--'                           AS SERVICE,
    null                                 AS SUBSERVICE,
    ( case HS.current_state
      when 2 then 3
      when 1 then 2
      when 0 then 0
      end )                              AS STATUS,
    HS.hoststatus_id                     AS SVCID,
    HS.output                            AS OUTPUT,
    HS.state_type                        AS SVCST,
    HS.problem_has_been_acknowledged     AS ACK,
    HS.check_type                        AS CHECKTYPE,
    ( CASE HS.check_type
        WHEN 0 THEN HS.active_checks_enabled
        WHEN 1 THEN HS.passive_checks_enabled
      END )                              AS DISABLECHECK,
    UNIX_TIMESTAMP(HS.last_check)        AS LASTCHECK,
    UNIX_TIMESTAMP(HS.last_state_change) AS DURATION,
    'host'                               AS TYPE,
    HS.scheduled_downtime_depth          AS DOWNTIME,
    HS.notifications_enabled             AS NOTIF,
    ( SELECT BIT_OR(
        IF(substring_index(comment_data, ':', 1) = '~track', 2,
          IF(substring(comment_data, 1, 1) = '~', 0, 1) ))
      FROM ".$BACKEND."_comments AS CO
      WHERE CO.object_id = H.host_object_id
      AND CO.entry_type = 1
      AND CO.comment_source = 1
    )                                    AS COMMENT,  -- comment is 0, 1, 2 or 3
                                                      -- bit 0 is comment
                                                      -- bit 1 is track
                                                      -- 0: no comment, no track
                                                      -- 1: comment only
                                                      -- 2: track only
                                                      -- 3: comment and track
    SUBSTRING_INDEX(HS.check_command,'!',1)
                                         AS CHECKNAME

  FROM
         ".$BACKEND."_hosts AS H
    JOIN ".$BACKEND."_hoststatus AS HS            ON H.host_object_id = HS.host_object_id
    JOIN ".$BACKEND."_host_contactgroups AS HCG   ON HCG.host_id = H.host_id
    JOIN ".$BACKEND."_contactgroups As OCG        ON HCG.contactgroup_object_id = OCG.contactgroup_object_id
    JOIN ".$BACKEND."_contactgroup_members AS CGM ON CGM.contactgroup_id = OCG.contactgroup_id
    JOIN ".$BACKEND."_contacts AS C               ON C.contact_object_id = CGM.contact_object_id
    JOIN ".$BACKEND."_objects AS O                ON O.object_id = C.contact_object_id
    LEFT JOIN ".$BACKEND."_hostgroup_members AS HGM ON H.host_object_id = HGM.host_object_id
    LEFT JOIN ".$BACKEND."_hostgroups AS HG       ON HGM.hostgroup_id = HG.hostgroup_id
    LEFT JOIN ".$BACKEND."_objects AS OHG         ON HG.hostgroup_object_id = OHG.object_id

  WHERE
    define_my_host_search
    O.name1 = 'define_my_user'
    AND ( (
            HS.current_state IN (define_my_hostfilt)
        AND HS.scheduled_downtime_depth define_my_hostdownop define_my_hostdownval
        AND HS.problem_has_been_acknowledged IN (define_my_hostacklist)
        AND HS.notifications_enabled IN (define_my_disable)
        AND (define_my_soft = 0 OR HS.state_type = 1)
        AND (
              HS.active_checks_enabled IN (define_my_check_disable) OR
              HS.passive_checks_enabled IN (define_my_check_disable)
        )
        AND (
              ( HS.check_type = 0 AND HS.active_checks_enabled IN (define_my_check_disable) ) OR
              ( HS.check_type = 1 AND HS.passive_checks_enabled IN (define_my_check_disable) )
            )
      )
      OR (
        SELECT count(*) > 0
        FROM ".$BACKEND."_comments
        WHERE object_id = H.host_object_id
        AND entry_type = 1
        AND comment_source = 1
        AND substring_index(comment_data, ':', 1) = '~track'
        AND ( define_track_anything = 0 )
      )
    )


  GROUP BY SVCID

-- END
-- NDO HOSTS


) AS sub

ORDER BY define_sortfield define_sortsensfield

LIMIT define_first, define_step
";

?>
