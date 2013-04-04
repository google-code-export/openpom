<?php

$QUERY_COMMENT_MIXED_ID = "
    select
        c.internal_comment_id as id
    from
        ".$BACKEND."_commenthistory as c
    inner join
        ".$BACKEND."_objects as o on c.object_id=o.object_id
    where
        c.deletion_time = '0000-00-00 00:00:00' and
        o.name1 = define_host and
        o.name2 = define_svc
    order by
        c.internal_comment_id asc;
";

?>
