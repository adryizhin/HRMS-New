<?php
require_once __DIR__ . '/../includes/auth_check.php';
checkRole(['employee', 'admin']);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/kpi.php';

$user_id = (int) $_SESSION['user_id'];
$kpi = calculateEmployeeKpi($conn, $user_id);
$factors = $kpi['factors'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Key Performance Indicators</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="<?= htmlspecialchars(app_url('employee/dashboard.css')); ?>">
<style>
:root {
  --kpi-panel: #ffffff;
  --kpi-panel-alt: #f8fafc;
  --kpi-border: #e2e8f0;
  --kpi-text: #0f172a;
  --kpi-muted: #475569;
  --kpi-score-bg: #f8fafc;
  --kpi-chip: #e2e8f0;
  --kpi-progress: #e2e8f0;
  --kpi-accent: #159fe8;
  --kpi-pie-rest: #f15b5b;
}

[data-theme="dark"] {
  --kpi-panel: #101826;
  --kpi-panel-alt: #0b1220;
  --kpi-border: #263244;
  --kpi-text: #f8fafc;
  --kpi-muted: #94a3b8;
  --kpi-score-bg: #0b1220;
  --kpi-chip: #263244;
  --kpi-progress: #0b1220;
  --kpi-accent: #159fe8;
  --kpi-pie-rest: #f15b5b;
}

.kpi-hero {
  align-items: center;
  background: var(--kpi-panel);
  border: 1px solid var(--kpi-border);
  border-radius: 8px;
  display: grid;
  gap: 22px;
  grid-template-columns: minmax(0, 1fr) 220px;
  margin-bottom: 22px;
  padding: 22px;
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.05);
}
.kpi-hero h1 { color: var(--kpi-text); font-size: 28px; margin-bottom: 8px; }
.kpi-hero p { color: var(--kpi-muted); font-size: 14px; line-height: 1.5; }
.kpi-score {
  background: var(--kpi-score-bg);
  border: 1px solid var(--kpi-border);
  border-radius: 8px;
  padding: 20px;
  text-align: center;
}
.kpi-score strong { display: block; font-size: 48px; line-height: 1; }
.kpi-score span { color: var(--kpi-muted); display: block; font-size: 13px; margin-top: 8px; text-transform: uppercase; }
.excellent { color: var(--status-success); }
.good { color: var(--kpi-accent); }
.watch { color: var(--status-warning); }
.risk { color: var(--status-danger); }
.muted { color: var(--kpi-muted); }
.factor-grid {
  display: grid;
  gap: 16px;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
}
.factor-card {
  background: var(--kpi-panel);
  border: 1px solid var(--kpi-border);
  border-radius: 8px;
  padding: 16px;
}
.factor-head {
  align-items: center;
  display: flex;
  justify-content: space-between;
  gap: 12px;
}
.factor-card h3 { color: var(--kpi-text); font-size: 15px; }
.factor-weight {
  background: var(--kpi-panel-alt);
  border: 1px solid var(--kpi-border);
  border-radius: 999px;
  color: var(--kpi-muted);
  font-size: 12px;
  padding: 5px 9px;
}
.factor-value { font-size: 30px; font-weight: 800; margin: 14px 0 8px; }
.factor-card p { color: var(--kpi-muted); font-size: 13px; line-height: 1.45; }
.factor-card { display:grid; grid-template-columns:minmax(0,1fr) 150px; column-gap:16px; align-items:center; }
.factor-head,.factor-value,.factor-card p { grid-column:1; }
.factor-pie { grid-column:2; grid-row:1 / span 4; width:132px; aspect-ratio:1; border-radius:50%; background:conic-gradient(var(--kpi-accent) 0 calc(var(--score) * 1%), var(--kpi-pie-rest) calc(var(--score) * 1%) 100%); justify-self:center; }
.progress {
  background: var(--kpi-progress);
  border-radius: 999px;
  height: 9px;
  margin-top: 13px;
  overflow: hidden;
}
.progress span { background: var(--kpi-accent); display: block; height: 100%; }
.kpi-method { margin-bottom:22px; padding:18px; border:1px solid var(--kpi-border); border-radius:8px; background:var(--kpi-panel); }
.kpi-method h2 { font-size:17px; color:var(--kpi-text); margin-bottom:8px; }
.kpi-method p { font-size:13px; color:var(--kpi-muted); line-height:1.7; margin-top:8px; }
.factor-card .factor-definition { margin-top:10px; font-size:12px; }
.kpi-breakdown-open { margin:0 0 18px; padding:11px 16px; border:0; border-radius:8px; background:var(--kpi-accent); color:#fff; font-weight:700; cursor:pointer; }
.kpi-breakdown-dialog { position:fixed; inset:0; margin:auto; width:min(1080px,calc(100vw - 32px)); max-width:none; max-height:90vh; padding:20px; overflow:auto; border:1px solid var(--kpi-border); border-radius:14px; background:var(--kpi-panel-alt); color:var(--kpi-text); }
.kpi-breakdown-dialog::backdrop { background:rgba(15,23,42,.58); }
.kpi-breakdown-dialog-head { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:18px; }
.kpi-breakdown-dialog-head h2 { font-size:20px; }
.kpi-breakdown-close { border:1px solid var(--kpi-border); border-radius:8px; padding:8px 12px; background:var(--kpi-panel); color:var(--kpi-text); cursor:pointer; }
.kpi-breakdown-dialog .factor-grid { grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
.kpi-breakdown-dialog .factor-card { grid-template-columns:minmax(0,1fr) 132px; gap:8px 14px; padding:14px; }
.kpi-breakdown-dialog .factor-head { grid-column:1/-1; min-height:34px; }
.kpi-breakdown-dialog .factor-head h3 { font-size:14px; }
.kpi-breakdown-dialog .factor-value { margin:0; font-size:26px; }
.kpi-breakdown-dialog .factor-pie { grid-column:2; grid-row:2/4; width:120px; }
.kpi-breakdown-dialog .factor-card p { font-size:12px; line-height:1.4; margin:0; }
.kpi-breakdown-dialog .factor-card .factor-definition { grid-column:1/-1; margin-top:2px; font-size:11px; }
@media (max-width: 780px) {
  .kpi-hero { grid-template-columns: 1fr; }
  .kpi-breakdown-dialog { padding:14px; }
  .kpi-breakdown-dialog .factor-grid { grid-template-columns:1fr; }
}
</style>
</head>
<body>
<?php renderSidebar($_SESSION['role'], $_SESSION['role'] . '.kpi'); ?>

<div class="main">
  <div class="kpi-hero">
    <div>
      <p class="eyebrow">My performance</p>
      <h1>Key Performance Indicators</h1>
      <p>Key Performance Indicators (KPIs) are measurable values used to track work performance. These indicators measure your attendance reliability, reviewed work quality, task completion, and on-time delivery.</p>
    </div>
    <div class="kpi-score">
      <strong class="<?= htmlspecialchars($kpi['color_class']); ?>"><?= number_format((float)$kpi['overall'],1); ?>%</strong>
      <span>Overall performance</span>
      <span><?= (int)$kpi['available_weight']; ?>% coverage</span>
      <span><?= htmlspecialchars($kpi['label']); ?></span>
    </div>
  </div>

  <div class="cards">
    <div class="card">
      <h3>Assigned Tasks</h3>
      <h1><?= (int) $kpi['stats']['assigned']; ?></h1>
      <i class="fa-solid fa-list-check"></i>
    </div>
    <div class="card">
      <h3>Submitted Work</h3>
      <h1><?= (int) $kpi['stats']['submitted']; ?></h1>
      <i class="fa-solid fa-paper-plane"></i>
    </div>
    <div class="card">
      <h3>HR Ratings</h3>
      <h1><?= (int) $kpi['stats']['scored']; ?></h1>
      <i class="fa-solid fa-star"></i>
    </div>
    <div class="card">
      <h3>Attendance Records</h3>
      <h1><?= (int) $kpi['stats']['attendance_total']; ?></h1>
      <i class="fa-solid fa-clock"></i>
    </div>
  </div>

  <section class="kpi-method" aria-label="How performance is measured"><h2>How your score is calculated</h2><p>Reviewed work quality: 40%; task completion: 25%; attendance reliability: 20%; on-time delivery: 15%. The overall score starts at zero and adds each measure multiplied by its fixed weight. Missing measures contribute zero points; available weights never expand. For example, 100% attendance with no work data earns 20% overall. Coverage shows how much of the assessment has recorded data.</p><p>Ratings: Excellent 85–100%; On Track 70–84.9%; Needs Support 50–69.9%; At Risk below 50%. Missing measures count as zero; no records produce 0% overall. Future pending tasks are excluded until due or submitted. Overdue unsubmitted tasks count against completion and timeliness.</p></section>
  <button type="button" class="kpi-breakdown-open" data-open-kpi-breakdown>View KPI breakdown</button>
  <dialog class="kpi-breakdown-dialog" data-kpi-breakdown aria-labelledby="kpi-breakdown-title">
    <div class="kpi-breakdown-dialog-head"><h2 id="kpi-breakdown-title">Performance breakdown</h2><button type="button" class="kpi-breakdown-close" data-close-kpi-breakdown>Close</button></div>
  <div class="factor-grid">
    <?php foreach ($factors as $factor): ?>
      <?php
        $score = $factor['score'];
        $displayScore = $score === null ? null : clampScore((float) $score);
      ?>
      <article class="factor-card">
        <div class="factor-head">
          <h3><?= htmlspecialchars($factor['label']); ?></h3>
          <span class="factor-weight"><?= (int) $factor['weight']; ?>% weight</span>
        </div>
        <div class="factor-value <?= $displayScore === null ? 'muted' : htmlspecialchars(scoreColorClass($displayScore)); ?>">
          <?= number_format($displayScore ?? 0.0, 1); ?>%<?php if($displayScore===null): ?> <small>(no data)</small><?php endif; ?>
        </div>
        <div class="factor-pie" style="--score: <?= $displayScore ?? 0 ?>" data-score="<?= $displayScore === null ? '—' : number_format($displayScore,1).'%' ?>" aria-label="<?= $displayScore === null ? 'No data' : number_format($displayScore,1).' percent' ?>"></div>
        <p><?= htmlspecialchars($factor['detail']); ?></p>
        <p class="factor-definition"><?= htmlspecialchars($factor['definition']); ?></p>
      </article>
    <?php endforeach; ?>
  </div>
  </dialog>
</div>
<script>
const kpiBreakdown = document.querySelector('[data-kpi-breakdown]');
document.querySelector('[data-open-kpi-breakdown]')?.addEventListener('click', () => kpiBreakdown.showModal());
document.querySelector('[data-close-kpi-breakdown]')?.addEventListener('click', () => kpiBreakdown.close());
kpiBreakdown?.addEventListener('click', event => { if (event.target === kpiBreakdown) kpiBreakdown.close(); });
</script>
</body>
</html>
