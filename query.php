<? 
/*
  OpenPom $Revision$
  $HeadURL$
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
 
  $Date$

  Sylvain Choisnard - schoisnard@exosec.fr                                                 
*/


$QUERY = "
SELECT
  SQL_CALC_FOUND_ROWS
  sub.GROUPES                      AS GROUPE,
  sub.MACHINES                     AS MACHINES,
  sub.MACHINE_NAME                 AS MACHINE_NAME,
  sub.SERVICES                     AS SERVICES,
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
  sub.COMMENT                      AS COMMENT

FROM (

  SELECT
    GROUP_CONCAT(
      DISTINCT OHG.name1
      ORDER BY OHG.name1
      DESC SEPARATOR 'define_my_separator'
    )                                    AS GROUPES,
    H.alias                              AS MACHINES,
    H.display_name                       AS MACHINE_NAME,
    S.display_name                       AS SERVICES,
    SS.current_state                     AS STATUS,
    SS.servicestatus_id                  AS SVCID,
    SS.output                            AS OUTPUT,
    SS.state_type                        AS SVCST,
    SS.problem_has_been_acknowledged     AS ACK,
    UNIX_TIMESTAMP(SS.last_check)        AS LASTCHECK,
    UNIX_TIMESTAMP(SS.last_state_change) AS DURATION,
    'svc'                                AS TYPE,
    SS.scheduled_downtime_depth          AS DOWNTIME,
    SS.notifications_enabled             AS NOTIF,
    ( SELECT count(*)
      FROM nagios_commenthistory AS CO
      WHERE CO.object_id = S.service_object_id
      AND entry_type = 1
      AND author_name != '(Nagios Process)'
      AND deletion_time = '000-00-00 00:00:00'
      ORDER BY CO.entry_time DESC
      LIMIT 1 )                          AS COMMENT

  FROM
         nagios_hosts AS H
    JOIN nagios_hoststatus AS HS             ON H.host_object_id = HS.host_object_id
    JOIN nagios_services AS S                ON H.host_object_id = S.host_object_id
    JOIN nagios_servicestatus AS SS          ON S.service_object_id = SS.service_object_id
    JOIN nagios_service_contactgroups AS SCG ON SCG.service_id = S.service_id
    JOIN nagios_contactgroups AS HCG         ON SCG.contactgroup_object_id = HCG.contactgroup_object_id
    JOIN nagios_contactgroup_members AS CGM  ON CGM.contactgroup_id = HCG.contactgroup_id
    JOIN nagios_contacts AS C                ON C.contact_object_id = CGM.contact_object_id
    JOIN nagios_objects AS O                 ON O.object_id = C.contact_object_id
    LEFT JOIN nagios_hostgroup_members AS HGM ON H.host_object_id = HGM.host_object_id
    LEFT JOIN nagios_hostgroups AS HG        ON HGM.hostgroup_id = HG.hostgroup_id
    LEFT JOIN nagios_objects AS OHG          ON HG.hostgroup_object_id = OHG.object_id

  WHERE
    (
		     H.alias        define_my_like 'define_my_filter'
      define_or_and  H.display_name define_my_like 'define_my_filter'
      define_or_and  S.display_name define_my_like 'define_my_filter'
      define_or_and  OHG.name1      define_my_like 'define_my_filter'
    )
    AND O.name1 = 'define_my_user'
    AND SS.current_state IN (define_my_svcfilt)
    AND ( HS.problem_has_been_acknowledged IN (define_my_hostacklist)
      OR  ( SELECT substring( comment_data, 1 ,1) 
            FROM nagios_commenthistory
            WHERE object_id = S.service_object_id
            AND entry_type = 4
            AND author_name != '(Nagios Process)'
            AND deletion_time = '000-00-00 00:00:00'
            ORDER BY entry_time DESC
            LIMIT 1
          ) = '!'
        )
    AND ( ( SS.problem_has_been_acknowledged IN (define_my_svcacklist) AND
          HS.problem_has_been_acknowledged IN (define_my_acklist) )
      OR  ( SELECT substring( comment_data, 1 ,1) 
            FROM nagios_commenthistory
            WHERE object_id = S.service_object_id
            AND entry_type = 4
            AND author_name != '(Nagios Process)'
            AND deletion_time = '000-00-00 00:00:00'
            ORDER BY entry_time DESC
            LIMIT 1
          ) = '!'
	) 
    AND HS.scheduled_downtime_depth IN (define_my_hostdownlist)
    AND ( SS.scheduled_downtime_depth IN (define_my_svcdownlist) AND
          HS.scheduled_downtime_depth IN (define_my_acklist) )
    AND ( define_my_nosvc = 0 OR HS.current_state = 0 )
    AND SS.notifications_enabled IN (define_my_disable)

  GROUP BY SVCID

UNION

  SELECT
    GROUP_CONCAT(
      DISTINCT OHG.name1
      ORDER BY OHG.name1
      DESC SEPARATOR 'define_my_separator'
    )                                    AS GROUPES,
    H.alias                              AS MACHINES,
    H.display_name                       AS MACHINE_NAME,
    '--host--'                           AS SERVICES,
    ( case HS.current_state 
      when 2 then 3
      when 1 then 2
      when 0 then 0
      end )                              AS STATUS,
    HS.hoststatus_id                     AS SVCID,
    HS.output                            AS OUTPUT,
    HS.state_type                        AS SVCST,
    HS.problem_has_been_acknowledged     AS ACK,
    UNIX_TIMESTAMP(HS.last_check)        AS LASTCHECK,
    UNIX_TIMESTAMP(HS.last_state_change) AS DURATION,
    'host'                               AS TYPE,
    HS.scheduled_downtime_depth          AS DOWNTIME,
    HS.notifications_enabled             AS NOTIF,
    ( SELECT count(*)
      FROM nagios_commenthistory AS CO
      WHERE CO.object_id = H.host_object_id
      AND entry_type = 1
      AND author_name != '(Nagios Process)'
      AND deletion_time = '000-00-00 00:00:00'
      ORDER BY CO.entry_time DESC
      LIMIT 1 )                          AS COMMENT

  FROM
         nagios_hosts AS H
    JOIN nagios_hoststatus AS HS            ON H.host_object_id = HS.host_object_id
    JOIN nagios_host_contactgroups AS HCG   ON HCG.host_id = H.host_id
    JOIN nagios_contactgroups As OCG        ON HCG.contactgroup_object_id = OCG.contactgroup_object_id
    JOIN nagios_contactgroup_members AS CGM ON CGM.contactgroup_id = OCG.contactgroup_id
    JOIN nagios_contacts AS C               ON C.contact_object_id = CGM.contact_object_id
    JOIN nagios_objects AS O                ON O.object_id = C.contact_object_id
    LEFT JOIN nagios_hostgroup_members AS HGM ON H.host_object_id = HGM.host_object_id
    LEFT JOIN nagios_hostgroups AS HG       ON HGM.hostgroup_id = HG.hostgroup_id
    LEFT JOIN nagios_objects AS OHG         ON HG.hostgroup_object_id = OHG.object_id

  WHERE
    (
		     H.alias        define_my_like 'define_my_filter'
      define_or_and  H.display_name define_my_like 'define_my_filter'
      define_or_and  '--host--'     define_my_like 'define_my_filter'
      define_or_and  OHG.name1      define_my_like 'define_my_filter'
    )
    AND O.name1 = 'define_my_user'
    AND HS.current_state IN (define_my_hostfilt)
    AND HS.scheduled_downtime_depth IN (define_my_hostdownlist)
    AND ( HS.problem_has_been_acknowledged IN (define_my_hostacklist)
      OR ( SELECT substring( comment_data, 1 ,1) 
            FROM nagios_commenthistory
            WHERE object_id = H.host_object_id
            AND entry_type = 4
            AND author_name != '(Nagios Process)'
            AND deletion_time = '000-00-00 00:00:00'
            ORDER BY entry_time DESC
            LIMIT 1
          ) = '!'
        )
    AND HS.notifications_enabled IN (define_my_disable)

  GROUP BY SVCID


) AS sub

ORDER BY define_sortfield define_sortsensfield

LIMIT define_first, define_step
";

?>
