<?php if ($submissions && $submissions->num_rows > 0): ?>

<div class="submissions-grid">
    <?php while ($task = $submissions->fetch_assoc()): 
        $statusClass = $task['hr_score'] !== null ? 'scored' : ($task['status'] === 'reviewed' ? 'reviewed' : ($task['status'] === 'submitted' ? 'submitted' : 'pending'));
        $statusLabel = $task['hr_score'] !== null ? 'Scored' : ucfirst((string) $task['status']);
    ?>
        <div class="submission-card">
            <div class="card-header">
                <h3><?= htmlspecialchars($task['title']); ?></h3>
                <span class="status-badge <?= $statusClass ?>">
                    <?= htmlspecialchars($statusLabel); ?>
                </span>
            </div>

            <div class="employee-info">
                👤 <strong><?= htmlspecialchars($task['employee_name']); ?></strong>
                <?php if ($task['emp_id']): ?>
                    <br><span style="font-size:11px;"><?= htmlspecialchars($task['emp_id']); ?></span>
                <?php endif; ?>
            </div>

            <div class="task-meta">
                <div><i class="fa-solid fa-calendar"></i> Deadline: <?= $task['deadline'] ? date("M d, Y", strtotime($task['deadline'])) : 'No deadline' ?></div>
                <?php if ($task['submitted_at']): ?>
                    <div><i class="fa-solid fa-clock"></i> Submitted: <?= date("M d, Y h:i A", strtotime($task['submitted_at'])) ?></div>
                <?php endif; ?>
            </div>

            <?php if ($task['hr_score'] !== null): ?>
                <div class="score-display">
                    <div class="score-value" style="color: <?= $task['hr_score'] >= 70 ? 'var(--status-success)' : ($task['hr_score'] >= 50 ? 'var(--status-warning)' : 'var(--status-danger)') ?>">
                        <?= (int) $task['hr_score'] ?>%
                    </div>
                    <div class="score-label">HR Score</div>
                </div>
            <?php endif; ?>

            <div class="action-buttons">
                <a href="<?= htmlspecialchars(app_url('hr/view_submission.php')); ?>?id=<?= $task['id'] ?>" class="btn-view">
                    <i class="fa-solid fa-eye"></i> View
                </a>
                <a href="<?= htmlspecialchars(app_url('hr/score_submission.php')); ?>?id=<?= $task['id'] ?>" class="btn-score">
                    <i class="fa-solid fa-star"></i> <?= $task['hr_score'] !== null ? 'Edit' : 'Score' ?>
                </a>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<?php else: ?>

<div class="no-submissions">
    <div style="font-size: 48px; margin-bottom: 15px;">📭</div>
    <p>No task submissions to review yet.</p>
    <p style="font-size: 12px; margin-top: 10px;"><?= $_SESSION['role']==='admin'?'Human Resource staff':'Employees' ?> will submit their work here.</p>
</div>

<?php endif; ?>
