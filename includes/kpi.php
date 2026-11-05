<?php
declare(strict_types=1);

require_once __DIR__ . '/attendance.php';
require_once __DIR__ . '/task_schema.php';

function scoreColorClass(float $score): string
{
    if ($score >= 85) {
        return 'excellent';
    }

    if ($score >= 70) {
        return 'good';
    }

    if ($score >= 50) {
        return 'watch';
    }

    return 'risk';
}

function performanceLabel(float $score): string
{
    if ($score >= 85) {
        return 'Excellent';
    }

    if ($score >= 70) {
        return 'On Track';
    }

    if ($score >= 50) {
        return 'Needs Support';
    }

    return 'At Risk';
}

function clampScore(float $score): float
{
    return max(0.0, min(100.0, $score));
}

function calculateEmployeeKpi(mysqli $conn, int $employeeId, ?string $startDate = null, ?string $endDate = null): array
{
    ensureAttendanceTable($conn);
    ensureTaskSchema($conn);
    $now = new DateTimeImmutable('now', new DateTimeZone('Asia/Manila'));
    $asOf = $now;
    if ($endDate !== null) {
        $periodEnd = new DateTimeImmutable($endDate . ' 23:59:59', new DateTimeZone('Asia/Manila'));
        if ($periodEnd < $asOf) $asOf = $periodEnd;
    }
    $asOfDate = $asOf->format('Y-m-d');
    $asOfTime = $asOf->format('Y-m-d H:i:s');
    $attendanceStmt = $conn->prepare("SELECT status FROM attendance WHERE user_id=?
        AND (? IS NULL OR attendance_date>=?) AND attendance_date<=?");
    $attendanceStmt->bind_param('isss', $employeeId, $startDate, $startDate, $asOfDate);
    $attendanceStmt->execute();
    $present=0; $late=0; $absent=0;
    foreach ($attendanceStmt->get_result() as $row) {
        if ($row['status']==='present') $present++;
        elseif ($row['status']==='late') $late++;
        elseif ($row['status']==='absent') $absent++;
    }
    $attendanceTotal=$present+$late+$absent;
    $attendanceScore=$attendanceTotal ? ($present+0.75*$late)/$attendanceTotal*100 : null;

    $taskStmt=$conn->prepare("SELECT submission_file,submitted_at,deadline,status,hr_score FROM tasks
        WHERE employee_id=? AND (? IS NULL OR created_at>=?) AND created_at<=?");
    $taskStmt->bind_param('isss', $employeeId, $startDate, $startDate, $asOfTime);
    $taskStmt->execute();
    $assigned=0; $submitted=0; $reviewed=0; $scored=0; $scoreTotal=0.0;
    $eligible=0; $completed=0; $onTime=0; $deadlineEligible=0; $overduePending=0; $unknownSubmissionTime=0;
    foreach ($taskStmt->get_result() as $task) {
        $assigned++;
        $hasFile=trim((string)$task['submission_file'])!=='';
        $recordedTime=$task['submitted_at'];
        // A later submission cannot make a past-period task appear complete or on time.
        $isSubmitted=$hasFile && (!$recordedTime || $recordedTime<=$asOfTime);
        $deadline=$task['deadline'] ? $task['deadline'].' 23:59:59' : null;
        $due=$deadline!==null && $deadline<=$asOfTime;
        if ($isSubmitted) $submitted++;
        if ($isSubmitted && $task['status']==='reviewed') {
            $reviewed++;
            if ($task['hr_score']!==null) { $scored++; $scoreTotal+=clampScore((float)$task['hr_score']); }
        }
        // Future pending assignments and undated pending work have no due outcome yet.
        if ($due || $isSubmitted) {
            $eligible++;
            if ($isSubmitted) $completed++;
        }
        if ($deadline!==null && ($due || $isSubmitted)) {
            $deadlineEligible++;
            if ($isSubmitted && $recordedTime && $recordedTime<=$deadline) $onTime++;
            if ($isSubmitted && !$recordedTime) $unknownSubmissionTime++;
        }
        if ($due && !$isSubmitted) $overduePending++;
    }
    $factors=[
        'attendance'=>[
            'label'=>'Attendance Reliability','weight'=>20,'score'=>$attendanceScore,
            'definition'=>'(Present days + 75% of late days) / all recorded attendance days. Absences contribute zero. Future-dated records are excluded.',
            'detail'=>"$present present, $late late, $absent absent",
        ],
        'hr_rating'=>[
            'label'=>'Reviewed Work Quality','weight'=>40,'score'=>$scored ? $scoreTotal/$scored : null,
            'definition'=>'Average final reviewer score on submitted work marked Reviewed. Unreviewed, unrated work is excluded; each score is limited to 0–100.',
            'detail'=>"$scored reviewed and rated submission(s)",
        ],
        'completion'=>[
            'label'=>'Task Completion','weight'=>25,'score'=>$eligible ? $completed/$eligible*100 : null,
            'definition'=>'Submitted tasks / tasks already due or submitted. Overdue unsubmitted work counts as incomplete; future and undated pending tasks are excluded until an outcome is measurable.',
            'detail'=>"$completed completed out of $eligible measurable assignments; $overduePending overdue pending",
        ],
        'deadline'=>[
            'label'=>'On-time Delivery','weight'=>15,'score'=>$deadlineEligible ? $onTime/$deadlineEligible*100 : null,
            'definition'=>'On-time submissions / deadline-tracked tasks already due or submitted. Late and overdue unsubmitted work count as missed deadlines. A missing submission timestamp cannot earn on-time credit.',
            'detail'=>"$onTime on time out of $deadlineEligible measurable deadlines; $unknownSubmissionTime submission(s) missing timestamps",
        ],
    ];
    $availableWeight=0; $weightedScore=0.0;
    foreach ($factors as $factor) if ($factor['score']!==null) {
        $availableWeight+=$factor['weight'];
        $weightedScore+=clampScore((float)$factor['score'])*$factor['weight'];
    }
    // Fixed 100-point scale: missing measures contribute zero; weights never expand.
    $overall=round($weightedScore/100,1);
    $assessment=$availableWeight===100?'complete':($availableWeight>0?'partial':'no_data');
    return [
        'overall'=>$overall,
        'label'=>performanceLabel($overall),
        'color_class'=>scoreColorClass($overall),
        'assessment_status'=>$assessment,'available_weight'=>$availableWeight,'factors'=>$factors,
        'stats'=>[
            'present'=>$present,'late'=>$late,'absent'=>$absent,'attendance_total'=>$attendanceTotal,
            'assigned'=>$assigned,'submitted'=>$submitted,'reviewed'=>$reviewed,'scored'=>$scored,
            'overdue_pending'=>$overduePending,'eligible_tasks'=>$eligible,'completed_tasks'=>$completed,
            'measurable_deadlines'=>$deadlineEligible,'on_time'=>$onTime,
        ],
    ];
}
