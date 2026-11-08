<?php
$h=static fn($v)=>htmlspecialchars(is_scalar($v)?(string)$v:'',ENT_QUOTES,'UTF-8');
$inputField=static function($key,$label,$type='text',$required=false)use($h,$data){echo '<div class="appraisal-field"><label for="'.$key.'">'.$h($label).'</label><input type="'.$type.'" id="'.$key.'" name="'.$key.'" value="'.$h($data[$key]??'').'"'.($required?' required':'').' maxlength="160"></div>';};
$data['ratings']=is_array($data['ratings']??null)?$data['ratings']:[];
$data['comments']=is_array($data['comments']??null)?$data['comments']:[];
$scale=[4=>'Outstanding',3=>'Above Average',2=>'Average',1=>'Below Average'];
?>
<section class="appraisal-sheet">
<h1>Employee Performance Appraisal</h1>
<p class="appraisal-context"><?= $h($task['title']) ?> · <?= $h($task['emp_id']?:'No employee ID') ?></p>
<?php if(!$readOnly && $task['submission_file']): ?><a class="appraisal-submission" href="<?= $h(app_url('uploads/submissions/'.basename($task['submission_file']))) ?>" target="_blank" rel="noopener"><i class="fa-solid fa-file-lines" aria-hidden="true"></i> Open submitted work</a><?php endif ?>
<form method="post" id="appraisal-form">
<?php if(!$readOnly): ?><input type="hidden" name="csrf_token" value="<?= $h($_SESSION['appraisal_csrf']) ?>"><?php endif ?>
<fieldset class="appraisal-fields" <?= $readOnly?'disabled':'' ?>><legend>Employee and appraisal details</legend>
<div class="appraisal-field"><label for="employee_name">Name of employee</label><input id="employee_name" value="<?= $h($task['employee_name']) ?>" readonly></div>
<?php $inputField('date_hired','Date hired','date');$inputField('position','Position','text',true);$inputField('department','Department','text',true);$inputField('period_start','Appraised period - from','date',true);$inputField('period_end','Appraised period - to','date',true);$inputField('accomplished_date','Date accomplished','date',true);$inputField('regularization_date','Date of regularization','date'); ?>
</fieldset>
<p class="appraisal-scale">Rating scale: Outstanding = 4 (100%), Above Average = 3 (75%), Average = 2 (50%), Below Average = 1 (25%). Each weighted grade = rating ÷ 4 × section weight. All six ratings are required.</p>
<?php foreach(appraisalCriteria() as $key=>$criterion): ?>
<fieldset class="appraisal-criterion" data-criterion="<?= $key ?>" data-weight="<?= $criterion['weight'] ?>" <?= $readOnly?'disabled':'' ?>><legend><?= $h($criterion['title']) ?> <span><?= $criterion['weight'] ?>%</span></legend><p><?= $h($criterion['prompt']) ?></p>
<div class="appraisal-options"><?php foreach($scale as $value=>$label): ?><label class="appraisal-option"><input type="radio" name="ratings[<?= $key ?>]" value="<?= $value ?>" <?= (string)($data['ratings'][$key]??'')===(string)$value?'checked':'' ?> required><span><strong><?= $label ?></strong><small><?= $h($criterion['levels'][$value]) ?></small></span></label><?php endforeach ?></div>
<label for="comments-<?= $key ?>">Comments</label><textarea id="comments-<?= $key ?>" name="comments[<?= $key ?>]" maxlength="2000" rows="3"><?= $h($data['comments'][$key]??'') ?></textarea>
</fieldset><?php endforeach ?>
<fieldset class="appraisal-narrative" <?= $readOnly?'disabled':'' ?>><legend>Summary and recommendations</legend>
<?php foreach(['strengths'=>'VII. Strength','weaknesses'=>'VIII. Weakness','recommendations'=>'IX. Recommendations to Employee to Improve Performance','potential'=>'X. Comments and Estimate of Potential'] as $key=>$label): ?><label for="<?= $key ?>"><?= $label ?></label><textarea id="<?= $key ?>" name="<?= $key ?>" rows="4" maxlength="4000"><?= $h($data[$key]??'') ?></textarea><?php endforeach ?>
</fieldset>
<section class="appraisal-grade" aria-labelledby="grade-heading"><h2 id="grade-heading">Grade</h2><table><thead><tr><th>Section</th><th>Weight</th><th>Rating / 4</th><th>Weighted grade</th></tr></thead><tbody><?php $previewTotal=0;foreach(appraisalCriteria() as $key=>$criterion): $rating=(int)($data['ratings'][$key]??0);$points=in_array($rating,[1,2,3,4],true)?$rating/4*$criterion['weight']:0;$previewTotal+=$points; ?><tr><td><?= $h($criterion['title']) ?></td><td><?= $criterion['weight'] ?>%</td><td data-rating="<?= $key ?>"><?= $rating?:'—' ?></td><td data-points="<?= $key ?>"><?= number_format($points,2) ?></td></tr><?php endforeach ?></tbody><tfoot><tr><th colspan="3">Average rating / weighted total</th><td><output id="appraisal-total"><?= number_format($previewTotal,2) ?></output>%</td></tr></tfoot></table><p id="appraisal-completeness" role="status"><?= $readOnly?'Saved appraisal': 'Select all six ratings before saving. The server verifies the total.' ?></p></section>
<fieldset class="appraisal-development" <?= $readOnly?'disabled':'' ?>><legend>Employee’s Future Development</legend><p>Recommendations</p>
<?php foreach(['regularization'=>'For regularization','promotion'=>'Should be promoted','experience'=>'Promotional after additional experience','meets_requirements'=>'Meets all requirements of present position','needs_improvement'=>'Needs improvements','reappraisal'=>'Needs further trial and re-appraisal'] as $key=>$label): ?><label class="appraisal-check"><input type="checkbox" name="development[]" value="<?= $key ?>" <?= in_array($key,is_array($data['development']??null)?$data['development']:[],true)?'checked':'' ?>><?= $label ?></label><?php endforeach ?>
<div class="appraisal-fields"><?php $inputField('regularization_position','Position for regularization');$inputField('promotion_position','Proposed promotion position');$inputField('promotion_readiness','Promotion readiness');$inputField('reappraisal_date','Re-appraisal date','date'); ?></div></fieldset>
<fieldset class="appraisal-signoffs" <?= $readOnly?'disabled':'' ?>><legend>Evaluation and sign-off details</legend>
<div class="appraisal-fields"><div class="appraisal-field"><label for="evaluator_name">Evaluated by</label><input id="evaluator_name" value="<?= $h($task['evaluator_name']) ?>" readonly></div><?php $inputField('evaluator_position','Evaluator position');$inputField('evaluator_date','Evaluation date','date'); ?></div>
<div class="appraisal-fields"><?php $inputField('reviewer_name','Reviewed by - name');$inputField('reviewer_position','Reviewer position');$inputField('reviewer_date','Review date','date');$inputField('approver_name','Approved by - name');$inputField('approver_position','Approver position');$inputField('approver_date','Approval date','date'); ?></div><p>Review and approval names are recorded form entries. Saving records the signed-in evaluator’s appraisal; it does not perform a separate approval action.</p></fieldset>
<?php if(!$readOnly): ?><div class="appraisal-actions"><button type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save appraisal</button><p>The weighted total becomes the evaluation score used in performance reports.</p></div><?php endif ?>
</form></section>
