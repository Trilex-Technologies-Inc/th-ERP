<?php
function getWorkshifts($scheduleid, $start, $end)
{
    // echo "getWorkshifts($scheduleid, " . formatDate($start) . ", " . formatDate($end) . ")<br>";
    $sql = <<<SQL
    select w.shiftid,
        s.recur_type,
        s.recur_interval,
        unix_timestamp(starttime) as starttime,
        unix_timestamp(endtime) as endtime
    from workshift w, schedule_shift ss, schedule s
    where w.shiftid=ss.shiftid
    	and ss.scheduleid=$scheduleid
        and ss.scheduleid=s.scheduleid
        and (((unix_timestamp(starttime) between $start and $end or
             unix_timestamp(endtime) between $start and $end))
        or (s.recur_type is not null and unix_timestamp(starttime) < $end))
SQL;
    $q = query($sql);
    $shiftMap = array();
    while ($rec = fetch_object($q)) {
        if ($rec->recur_type == null) {
            $tuple = array($rec->shiftid, $rec->starttime, $rec->endtime);
            $key = $rec->starttime;
            $shiftMap[$key] = $tuple;
        } else {
            $starttime = $rec->starttime;
            $endtime = $rec->endtime;
			$i = 0;
            while ($starttime < $end) {
                if ($starttime >= $start) {
                    $tuple = array($rec->shiftid, $starttime, $endtime, $i);
                    $shiftMap[$starttime] = $tuple;
                }
                $starttime = addDay($starttime, $rec->recur_interval);
                $endtime = addDay($endtime, $rec->recur_interval);
				$i++;
            }
        }
    }
    ksort($shiftMap);
    return $shiftMap;
}

function getEmployeeWorkshifts($employeeid, $start, $end)
{
    //echo "getEmployeeWorkshifts($employeeid, " . formatDate($start) . ", " . formatDate($end) . ")<br>";
    $result = array();
    $sql = <<<SQL
    select
        scheduleid,
        unix_timestamp(valid_from) as valid_from,
        unix_timestamp(valid_to) as valid_to
    from emp_schedule
    where (
          unix_timestamp(valid_from) between $start and $end or
          unix_timestamp(valid_to) between $start and $end or
          (unix_timestamp(valid_from) < $start and valid_to is null)
          )
          and employeeid=$employeeid
SQL;
    $q = query($sql);
    while ($row = fetch($q)) {
        $starttime = $start;
        if ($row->valid_from > $start)
            $starttime = $row->valid_from;
        $endtime = $end;
        if ($row->valid_to < $end && $row->valid_to != null)
            $endtime = $row->valid_to;
        $shifts = getWorkshifts($row->scheduleid, $starttime, $endtime);
        foreach ($shifts as $shift) {
            $result[] = $shift;
        }
    }
    return $result;
}

?>