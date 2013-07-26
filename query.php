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
  sub.*
  define_expr_cols

  -- COMMENT column:
  -- 0: no comment, no track
  -- 1: comment only
  -- 2: track only
  -- 3: comment and track

FROM (


-- BEGIN
-- NDO SERVICES

  SELECT
    GROUP_CONCAT(
      DISTINCT HGO.name1
      ORDER BY HGO.name1
      DESC SEPARATOR 'define_my_separator'
    )                                                   AS GROUPS,
    H.alias                                             AS HOSTALIAS,
    H.display_name                                      AS HOSTNAME,
    H.address                                           AS ADDRESS,
    S.display_name                                      AS SERVICE,
    NULL                                                AS SUBSERVICE,
    SS.current_state                                    AS STATUS,
    SS.servicestatus_id                                 AS STATUSID,
    SS.output                                           AS OUTPUT,
    SS.state_type                                       AS STATETYPE,
    SS.problem_has_been_acknowledged                    AS ACK,
    SS.check_type                                       AS CHECKTYPE,
    SS.active_checks_enabled                            AS ACTIVE,
    SS.passive_checks_enabled                           AS PASSIVE,
    UNIX_TIMESTAMP(SS.last_check)                       AS LASTCHECK,
    UNIX_TIMESTAMP(SS.last_state_change)                AS LASTCHANGE,
    'svc'                                               AS TYPE,
    SS.scheduled_downtime_depth                         AS DOWNTIME,
    SS.notifications_enabled                            AS NOTIF,
    ( SELECT BIT_OR(
        IF(comment_data LIKE '~track:%', 2,
        IF(comment_data LIKE '~%', 0, 1)))
      FROM ${BACKEND}_comments
      WHERE object_id = S.service_object_id
      AND entry_type = 1
      AND comment_source = 1
    )                                                   AS COMMENT,
    SUBSTRING_INDEX(SS.check_command, '!', 1)           AS CHECKNAME
    define_cvar_svc_cols

  FROM
         ${BACKEND}_hosts AS H
    JOIN ${BACKEND}_hoststatus AS HS                    ON H.host_object_id = HS.host_object_id
    JOIN ${BACKEND}_services AS S                       ON H.host_object_id = S.host_object_id
    JOIN ${BACKEND}_servicestatus AS SS                 ON S.service_object_id = SS.service_object_id
    JOIN ${BACKEND}_service_contactgroups AS SCG        ON SCG.service_id = S.service_id
    JOIN ${BACKEND}_contactgroups AS CG                 ON SCG.contactgroup_object_id = CG.contactgroup_object_id
    JOIN ${BACKEND}_contactgroup_members AS CGM         ON CGM.contactgroup_id = CG.contactgroup_id
    JOIN ${BACKEND}_contacts AS C                       ON C.contact_object_id = CGM.contact_object_id
    JOIN ${BACKEND}_objects AS CO                       ON CO.object_id = C.contact_object_id
    LEFT JOIN ${BACKEND}_hostgroup_members AS HGM       ON H.host_object_id = HGM.host_object_id
    LEFT JOIN ${BACKEND}_hostgroups AS HG               ON HGM.hostgroup_id = HG.hostgroup_id
    LEFT JOIN ${BACKEND}_objects AS HGO                 ON HG.hostgroup_object_id = HGO.object_id
    define_cvar_svc_joins

  WHERE
    ( CO.name1 = 'define_my_user' ) AND
    ( define_svc_search ) AND

    (
      ( SS.current_state IN (define_my_svcfilt) AND
        ( SS.problem_has_been_acknowledged IN (define_my_svcacklist) AND
          HS.problem_has_been_acknowledged define_my_acklistop define_my_acklistval
        ) AND

        ( SS.scheduled_downtime_depth define_my_svcdownop define_my_svcdownval AND
          HS.scheduled_downtime_depth define_my_acklistop define_my_acklistval
        ) AND

        (define_my_nosvc = 0 OR HS.current_state = 0) AND
        SS.notifications_enabled IN (define_my_disable) AND
        (define_my_soft = 0 OR SS.state_type = 1)

      ) OR
      ( SELECT count(*) > 0
        FROM ${BACKEND}_comments
        WHERE object_id = S.service_object_id
        AND entry_type = 1
        AND comment_source = 1
        AND define_track_anything = 1
        AND comment_data LIKE '~track:%'
      )
    )

  GROUP BY STATUSID

-- END
-- NDO SERVICES


UNION


-- BEGIN
-- NDO HOSTS

  SELECT
    GROUP_CONCAT(
      DISTINCT HGO.name1
      ORDER BY HGO.name1
      DESC SEPARATOR 'define_my_separator'
    )                                                   AS GROUPS,
    H.alias                                             AS HOSTALIAS,
    H.display_name                                      AS HOSTNAME,
    H.address                                           AS ADDRESS,
    'define_host_service'                               AS SERVICE,
    NULL                                                AS SUBSERVICE,
    ( CASE HS.current_state
      WHEN 2 THEN 3
      WHEN 1 THEN 2
      WHEN 0 THEN 0
      END
    )                                                   AS STATUS,
    HS.hoststatus_id                                    AS STATUSID,
    HS.output                                           AS OUTPUT,
    HS.state_type                                       AS STATETYPE,
    HS.problem_has_been_acknowledged                    AS ACK,
    HS.check_type                                       AS CHECKTYPE,
    HS.active_checks_enabled                            AS ACTIVE,
    HS.passive_checks_enabled                           AS PASSIVE,
    UNIX_TIMESTAMP(HS.last_check)                       AS LASTCHECK,
    UNIX_TIMESTAMP(HS.last_state_change)                AS LASTCHANGE,
    'host'                                              AS TYPE,
    HS.scheduled_downtime_depth                         AS DOWNTIME,
    HS.notifications_enabled                            AS NOTIF,
    ( SELECT BIT_OR(
        IF(comment_data LIKE '~track:%', 2,
        IF(comment_data LIKE '~%', 0, 1)))
      FROM ${BACKEND}_comments
      WHERE object_id = H.host_object_id
      AND entry_type = 1
      AND comment_source = 1
    )                                                   AS COMMENT,
    SUBSTRING_INDEX(HS.check_command, '!', 1)           AS CHECKNAME
    define_cvar_host_cols

  FROM
         ${BACKEND}_hosts AS H
    JOIN ${BACKEND}_hoststatus AS HS                    ON H.host_object_id = HS.host_object_id
    JOIN ${BACKEND}_host_contactgroups AS HCG           ON HCG.host_id = H.host_id
    JOIN ${BACKEND}_contactgroups AS CG                 ON HCG.contactgroup_object_id = CG.contactgroup_object_id
    JOIN ${BACKEND}_contactgroup_members AS CGM         ON CGM.contactgroup_id = CG.contactgroup_id
    JOIN ${BACKEND}_contacts AS C                       ON C.contact_object_id = CGM.contact_object_id
    JOIN ${BACKEND}_objects AS CO                       ON CO.object_id = C.contact_object_id
    LEFT JOIN ${BACKEND}_hostgroup_members AS HGM       ON H.host_object_id = HGM.host_object_id
    LEFT JOIN ${BACKEND}_hostgroups AS HG               ON HGM.hostgroup_id = HG.hostgroup_id
    LEFT JOIN ${BACKEND}_objects AS HGO                 ON HG.hostgroup_object_id = HGO.object_id
    define_cvar_host_joins

  WHERE
    ( CO.name1 = 'define_my_user' ) AND
    ( define_host_search ) AND

    (
      ( HS.current_state IN (define_my_hostfilt) AND
        HS.scheduled_downtime_depth define_my_hostdownop define_my_hostdownval AND
        HS.problem_has_been_acknowledged IN (define_my_hostacklist) AND
        HS.notifications_enabled IN (define_my_disable) AND
        (define_my_soft = 0 OR HS.state_type = 1)
      )
      OR
      ( SELECT count(*) > 0
        FROM ${BACKEND}_comments
        WHERE object_id = H.host_object_id
        AND entry_type = 1
        AND comment_source = 1
        AND define_track_anything = 1
        AND comment_data LIKE '~track:%'
      )
    )

  GROUP BY STATUSID

-- END
-- NDO HOSTS


) AS sub

ORDER BY define_orderby
LIMIT define_first, define_step
";



/******************************************************************************
 * Columns initialization
 * Columns-related pieces to subtitute in $QUERY
 *****************************************************************************/

/* Internal function called by init_enabled_columns() defined below.
 *
 * This function must be called a first time passing $col as null
 * in order to initialize its internal static variable and the
 * resulting $query_pieces array.
 *
 * $query_pieces: pieces to substitute in $QUERY (out)
 * $err: error message if false returned (out)
 * $col: name of the column to process (in)
 *
 * Return true on success.
 * Return false on failure and set an error message in $err.
 */
function init_column(&$query_pieces, &$err, $col = null)
{
    global $COLUMN_DEFINITION;
    global $BACKEND;
    static $processed;

    if (is_null($col)) {
        $processed = array();
        $query_pieces = array('define_expr_cols' => '',
                              'define_cvar_host_cols' => '',
                              'define_cvar_host_joins' => '',
                              'define_cvar_svc_cols' => '',
                              'define_cvar_svc_joins' => '');
        return true;
    }

    if (in_array($col, $processed))
        return true;

    if (!ctype_alnum($col)) {
        $err = "Only alphanumeric char allowed in column names: $col";
        return false;
    }

    if (!isset($COLUMN_DEFINITION[$col])) {
        $err = "Unknown column: $col";
        return false;
    }

    $def = &$COLUMN_DEFINITION[$col];

    /* special custom variable columns */
    if (isset($def['cvar'])) {

        /* sorting */
        if (!isset($def['sort']))
            $def['sort'] = array(
                array("ifnull(SCVAR_$col, HCVAR_$col)", 'asc')
            );

        /* data */
        if (!isset($def['data'])) {
            $def['data'] = array("SCVAR_$col", "HCVAR_$col");
            $def['opts'] = isset($def['opts']) 
                                ? $def['opts'] | COL_DATA_FIRST
                                : COL_DATA_FIRST;
        }

        /* filtering */
        if (!isset($def['filter']))
            $def['filter'] = array(
                'define_host_search'  => "HCVAR_$col.varvalue",
                'define_svc_search'   => "ifnull(SCVAR_$col.varvalue, HCVAR_$col.varvalue)"
            );

        /* query parts */
        $cvar = sqlquote($def['cvar']);

        $query_pieces['define_cvar_host_cols']  .= "
            , HCVAR_$col.varvalue AS HCVAR_$col
            , NULL AS SCVAR_$col ";
    
        $query_pieces['define_cvar_svc_cols']  .= "
            , HCVAR_$col.varvalue AS HCVAR_$col
            , SCVAR_$col.varvalue AS SCVAR_$col ";

        if (isset($def['is_ref']) && $def['is_ref']) {
            $query_pieces['define_cvar_host_joins'] .= "
                LEFT JOIN ${BACKEND}_customvariables as HCVAR_${col}_ref ON
                    HCVAR_${col}_ref.object_id = H.host_object_id AND
                    HCVAR_${col}_ref.varname = $cvar
                LEFT JOIN ${BACKEND}_customvariables as HCVAR_$col ON
                    HCVAR_$col.varname = HCVAR_${col}_ref.varvalue ";

            $query_pieces['define_cvar_svc_joins'] .= "
                LEFT JOIN ${BACKEND}_customvariables as HCVAR_${col}_ref ON
                    HCVAR_${col}_ref.object_id = H.host_object_id AND
                    HCVAR_${col}_ref.varname = $cvar
                LEFT JOIN ${BACKEND}_customvariables as HCVAR_$col ON
                    HCVAR_$col.varname = HCVAR_${col}_ref.varvalue 

                LEFT JOIN ${BACKEND}_customvariables as SCVAR_${col}_ref ON
                    SCVAR_${col}_ref.object_id = S.service_object_id AND
                    SCVAR_${col}_ref.varname = $cvar
                LEFT JOIN ${BACKEND}_customvariables as SCVAR_$col ON
                    SCVAR_$col.varname = SCVAR_${col}_ref.varvalue ";
        }
        else {
            $query_pieces['define_cvar_host_joins'] .= "
                LEFT JOIN ${BACKEND}_customvariables as HCVAR_$col ON
                    HCVAR_$col.object_id = H.host_object_id AND
                    HCVAR_$col.varname = $cvar ";

            $query_pieces['define_cvar_svc_joins'] .= "
                LEFT JOIN ${BACKEND}_customvariables as HCVAR_$col ON
                    HCVAR_$col.object_id = H.host_object_id AND
                    HCVAR_$col.varname = $cvar
                LEFT JOIN ${BACKEND}_customvariables as SCVAR_$col ON
                    SCVAR_$col.object_id = S.service_object_id AND
                    SCVAR_$col.varname = $cvar ";
        }
    }

    /* special expression columns */
    else if (isset($def['expr'])) {

        /* cannot have filter on expr columns */
        if (isset($def['filter'])) {
            $err = "Filter cannot be defined on 'expr' columns: $col";
            return false;
        }

        /* cannot have filter on expr columns */
        if (isset($def['key'])) {
            $err = "Key cannot be defined on 'expr' columns: $col";
            return false;
        }

        /* sorting */
        if (!isset($def['sort']))
            $def['sort'] = array(array("EXPR_$col", 'asc'));

        /* data */
        if (!isset($def['data']))
            $def['data'] = array("EXPR_$col");

        /* query part */
        $query_pieces['define_expr_cols'] .= "
            , ${def['expr']} as EXPR_$col ";
    }

    /* header cell format function */
    if (!isset($def['hfmt']) || !function_exists($def['hfmt'])) {
        if (function_exists("format_header_$col"))
            $def['hfmt'] = "format_header_$col";
        else
            $def['hfmt'] = 'format_header';
    }

    /* row cell format function */
    if (!isset($def['rfmt']) || !function_exists($def['rfmt'])) {
        if (function_exists("format_row_$col"))
            $def['rfmt'] = "format_row_$col";
        else
            $def['rfmt'] = 'format_row';
    }

    /* look for dependencies in expression columns */
    $deps = array();

    if (isset($def['expr']) &&
        preg_match_all('/(SCVAR|HCVAR|EXPR)_([[:alnum:]]+)/', $def['expr'], $cap))
        $deps = array_merge($deps, $cap[2]);

    if (isset($def['sort'])) {
        foreach ($def['sort'] as $sort) {
            if (preg_match_all('/(SCVAR|HCVAR|EXPR)_([[:alnum:]]+)/', $sort[0], $cap))
                $deps = array_merge($deps, $cap[2]);
        }
    }

    foreach ($deps as $depcol) {
        if ($depcol != $col && !init_column($query_pieces, $err, $depcol))
            return false;
    }

    $processed[] = $col;
    return true;
}

/* This function is called by index.php in order to initialize all
 * enabled columns.
 *
 * $cols: final list of columns used in alert.php (out)
 * $query_pieces: pieces to substitute in $QUERY (out)
 * $err: error message if false returned (out)
 * $monitor: boolean indicating if monitor mode is enabled (in)
 *
 * Return true on success.
 * Return false on failure and set an error message in $err.
 */
function init_enabled_columns(&$cols, &$query_pieces, &$err, $monitor)
{
    global $COLUMN_ENABLED;
    global $COLUMN_DEFINITION;

    $cols = array();
    init_column($query_pieces, $err);

    /* build the $cols array
     * - only add enabled columns set to be displayed
     * - do not add a column with COL_NO_MONITOR option if $monitor
     */
    foreach ($COLUMN_ENABLED as $col => $display) {
        if (!$display)
            continue;

        if (!init_column($query_pieces, $err, $col))
            return false;

        if ($monitor && isset($COLUMN_DEFINITION[$col]['opts']) &&
            ($COLUMN_DEFINITION[$col]['opts'] & COL_NO_MONITOR))
            continue;

        $cols[$col] = &$COLUMN_DEFINITION[$col];
    }

    /* check for mandatory columns */
    foreach ($COLUMN_DEFINITION as $col => $def) {
        if (isset($def['opts']) && ($def['opts'] & COL_MUST_DISPLAY) &&
            !isset($cols[$col])) {

            $err = "Mandatory column must be enabled: $col";
            return false;
        }
    }

    return true;
}

/******************************************************************************
 * Filter processing (user input for searching)
 * Filter-related pieces to subtitute in $QUERY
 *****************************************************************************/

function columns_with_filter_by_key()
{
    global $COLUMN_DEFINITION;

    $out = array();

    foreach ($COLUMN_DEFINITION as $col => &$def) {
        if (isset($def['filter']) && !empty($def['filter']) &&
            isset($def['key']) && !empty($def['key']))
            $out[$def['key']] = $def;
    }

    return $out;
}

function sqlquote($str, $surround = true)
{
    $str = str_replace("'", "''", $str);
    return $surround ? "'$str'" : $str;
}

/* This function is called from index.php, after columns initialization.
 * It used the $COLS global which is basically the result of the
 * init_enabled_columns() function.
 *
 * $query_pieces: pieces to substitute in $QUERY (out)
 * $err: error message if false returned (out)
 * $filter: user search (in)
 *
 * Return true on success.
 * Return false on failure and set an error message in $err.
 */
function init_filter(&$query_pieces, &$err, $filter)
{
    global $COLS;

    if (empty($filter)) {
        $query_pieces = array('define_host_search' => '1=1',
                              'define_svc_search' => '1=1');
        return true;
    }

    $query_pieces = array('define_host_search' => '',
                          'define_svc_search' => '');

    $column_keys = columns_with_filter_by_key();
    $re_keys = '[' . implode('', array_keys($column_keys)) . ']';
    $re_filter = "/(?'not'!)?\\s*((?'key'$re_keys):)?(?'val'[^\\s&|]+)\\s*(?'op'[&|])?\\s*/";

    function add(&$qp, $entry, $like = false)
    {
        if ($like === false) {
            foreach (array_keys($qp) as $type)
                $qp[$type] .= "$entry ";
        }
        else {
            $like = sqlquote(str_replace('*', '%', $like));
            foreach (array_keys($qp) as $type)
                $qp[$type] .= '(' . str_replace('%f', $like, $entry[$type]) . ')';
        }
    }

    if (!preg_match_all($re_filter, $filter, $parts, PREG_SET_ORDER)) {
        $err = "Invalid filter";
        return false;
    }

    for ($l = count($parts), $i = 0; $i < $l; $i++) {
        $more = $i < ($l - 1);
        $p = &$parts[$i];

        if (!empty($p['not']))
            add($query_pieces, 'NOT');

        add($query_pieces, '(');

        if (empty($p['key'])) {
            foreach ($COLS as $col => $def) {
                if (!isset($def['filter']))
                    continue;
                add($query_pieces, $def['filter'], "*${p['val']}*");
                add($query_pieces, 'OR');
            }
            add($query_pieces, '1=0');
        }
        else
            add($query_pieces, $column_keys[$p['key']]['filter'], $p['val']);

        add($query_pieces, ')');

        if (empty($p['op']) && $more)
            $p['op'] = '&';

        if (!empty($p['op'])) {
            if (!$more) {
                $err = "Invalid filter: trailing operator has no operand";
                return false;
            }
            add($query_pieces, $p['op'] == '&' ? 'AND' : 'OR');
        }
    }

    return true;
}

/* This function gets called from index.php. It makes use of the globals
 * $SORTCOL and $SORTDIR to construct a GROUP BY string.
 */
function get_orderby()
{
    global $SORTCOL;
    global $SORTDIR;
    global $COLUMN_DEFINITION;

    $out = '';

    if (isset($COLUMN_DEFINITION[$SORTCOL]) &&
        isset($COLUMN_DEFINITION[$SORTCOL]['sort'])) {

        foreach ($COLUMN_DEFINITION[$SORTCOL]['sort'] as $spec) {
            if ($SORTDIR) {
                $done = 0;
                $spec[1] = str_ireplace('asc', 'desc', $spec[1], $done);
                if (!$done)
                    $spec[1] = str_ireplace('desc', 'asc', $spec[1], $done);
            }

            if (!empty($out)) $out .= ', ';
            $out .= $spec[0] . ' ' . $spec[1];
        }
    }

    if (empty($out)) $out = '1';
    return $out;
}


/******************************************************************************
 * Columns data rendering, result of the query execution.
 * Column-specific function can be implemented:
 *
 * format_header()              default column header rendering function
 * format_row()                 default column row rendering function
 * format_header_<column>()     specific column header rendering function
 * format_row_<column>()        specific column row rendering function
 *
 * Header functions gets passed the following arguments:
 * - arg1, the column name
 * - arg2, the column definition (reference)
 *
 * Row functions gets passed the following arguments:
 * - arg1, the column name
 * - arg2, the column definition (reference)
 * - arg3, the SQL data row as associative array (reference)
 *****************************************************************************/

function format_header($col, &$def)
{
    global $SORTCOL;
    global $SORTDIR;
    global $MYLANG;
    global $MY_GET_NO_SORT;

    $text = ucfirst(lang($MYLANG, $col));

    if (isset($def['sort'])) {
        if ($SORTCOL == $col) {
            if ($SORTDIR)
                echo "<a class=\"col_sort_down\"
                         href=\"$MY_GET_NO_SORT&sort=$col&order=0\">$text</a>";
            else
                echo "<a class=\"col_sort_up\"
                         href=\"$MY_GET_NO_SORT&sort=$col&order=1\">$text</a>";
        }
        else
            echo "<a class=\"col_no_sort\"
                     href=\"$MY_GET_NO_SORT&sort=$col&order=0\">$text</a>";
    }
    else
        echo "<span class=\"col_no_sort\">$text</span>";

    if (isset($def['key']) && !empty($def['key']))
        echo '&thinsp;<span class="sub">('.$def['key'].')</span>';
}

function format_row($col, &$def, &$data)
{
    global $MY_GET_NO_FILT;
    global $QUICKSEARCH;

    $value = null;

    if (isset($def['data']) && !empty($def['data'])) {
        $datakey = is_array($def['data']) ? $def['data'] : array($def['data']);
        foreach ($datakey as $d) {
            if (!is_null($data[$d]) && !empty($data[$d])) {
                $value = (is_null($value) ? '' : "$value, ") . $data[$d];

                if (isset($def['opts']) && ($def['opts'] & COL_DATA_FIRST))
                    break;
            }
        }
    }

    if (is_null($value)) {
        echo '<span>&mdash;</span>';
        return;
    }

    if (isset($def['opts']) && ($def['opts'] & COL_MULTI)) {
        $value = array_map('trim', explode(',', $value));
        asort($value);
    }
    else
        $value = array($value);

    if (isset($def['opts']) && ($def['opts'] & COL_FMT_DURATION))
        $value = array_map('printtime', $value);

    $lmax = isset($def['lmax']) ? $def['lmax'] : null;
    $truncated = false;
    reset($value);
    $html = '';

    while (($e = current($value))) {
        next($value);
        $l = strlen($e);

        if (!is_null($lmax)) {
            if ($lmax <= 0) {
                $truncated = true;
                break;
            }

            if ($l > $lmax) {
                $l = $lmax;
                $truncated = true;
            }
            $lmax -= $l;
        }

        if (!empty($html)) $html .= ', ';

        if (isset($def['filter']) &&
            isset($def['opts']) && ($def['opts'] & COL_FILTER_LINK)) {

            if ($QUICKSEARCH) {
                $html .= "<a href=\"$MY_GET_NO_FILT&filtering=";
                if (isset($def['key']) && !empty($def['key']))
                    $html .= $def['key'].':';
                $html .= rawurlencode($e) . "\">";
            }
            else {
                $html .= "<a href=\"javascript:add_filter('";
                if (isset($def['key']) && !empty($def['key']))
                    $html .= $def['key'];
                $html .= "', '" . str_replace("'", "\\'", $e) . "')\">";
            }

            $html .= htmlspecialchars(substr($e, 0, $l)) . '</a>';
        }
        else
            $html .= htmlspecialchars(substr($e, 0, $l));
    }

    if ($truncated)
        $html .= '...';

    echo "<span>$html</span>";
}


function format_header_checkbox($col, &$def)
{
    ?>
    <span class="checkbox" onclick="selectall(this);">
        <span></span>
    </span>
    <?php
}

function format_row_checkbox($col, &$def, &$data)
{
    if (isset($data['__has_action']) && $data['__has_action']) {
        ?>
        <span class="checkbox">
            <input type="hidden"
                   class="data"
                   name="target[]"
                   value="<?php echo $data['__action_target'] ?>" />
            <span></span>
        </span>
        <?php
    }
}

function format_row_flags($col, &$def, &$data)
{
    global $MYLANG;
    global $LINK;
    global $GRAPH_POPUP_WIDTH;
    global $GRAPH_POPUP_HEIGHT;

    echo "<span>";

    if ($data['TYPE'] == "svc")
        echo "<a href=\"$LINK?type=2&host=${data['HOSTNAME']}&service=${data['SERVICE']}\" target=\"_blank\">".
             "<img src=\"img/flag_svc.png\" border=\"0\" alt=\"S\" title=\"".ucfirst(lang($MYLANG, 'service'))."\" />".
             "</a>";
    else if ($data['TYPE'] == "host")
        echo "<a href=\"$LINK?type=1&host=${data['HOSTNAME']}\" target=\"_blank\">".
             "<img src=\"img/flag_host.png\" border=\"0\" alt=\"H\" title=\"".ucfirst(lang($MYLANG, 'host'))."\" />".
             "</a>";

    $g = get_graph('popup', $data['HOSTNAME'], $data['SERVICE']);
    if (!empty($g))
        echo "<a href=\"#\" target=\"_blank\" ".
             "   onclick=\"return pop('$g', '${data['STATUSID']}', $GRAPH_POPUP_WIDTH, $GRAPH_POPUP_HEIGHT);\">".
             "<img src=\"img/flag_graph.png\" border=\"0\" alt=\"G\" title=\"".ucfirst(lang($MYLANG, 'graph_icon'))."\" />".
             "</a>";

    if ($data['ACK'] == '1')
        echo '<img src="img/flag_ack.gif" alt="A" title="'.ucfirst(lang($MYLANG, 'acknowledge')).'" />';

    if ($data['NOTIF'] == '0')
        echo '<img src="img/flag_notify.png" alt="N" title="'.ucfirst(lang($MYLANG, 'disable_title')).'" />';

    if ($data['DOWNTIME'] > 0)
        echo '<img src="img/flag_downtime.png" alt="D" title="'.ucfirst(lang($MYLANG, 'downtime')).'" />';

    if ($data['COMMENT'] & ENTRY_COMMENT_NORMAL)
        echo '<img src="img/flag_comment.gif" alt="C" title="'.ucfirst(lang($MYLANG, 'comment')).'" />';

    if (!$data['ACTIVE'] && !$data['PASSIVE'])
        echo '<img src="img/flag_no_active_passive.png" />';
    else if (!$data['ACTIVE'])
        echo '<img src="img/flag_no_active.png" />';
    else if (!$data['PASSIVE'])
        echo '<img src="img/flag_no_passive.png" />';

    echo "</span>";
}

function format_row_duration($col, &$def, &$data)
{
    if (isset($data['LASTCHANGE']) && $data['LASTCHANGE'] == 0) {
        echo '<span>&mdash;</span>';
        return;
    }

    return format_row($col, $def, $data);
}

?>
