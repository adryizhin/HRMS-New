<?php
$selectedEmployee=null;
foreach($staff as $person){if((string)$person['id']===$postText('employee_id')){$selectedEmployee=$person;break;}}
$employeeLabel=static fn($person)=>$person['full_name'].' · '.$person['employee_id'].' · '.($person['role']==='admin'?'IT Admin':'Employee').' · '.($person['job_title']?:($person['role']==='admin'?'IT Administrator':'Employee'));
?>
<label for="promotion-employee">Search employee</label>
<div class="promotion-employee-picker">
 <div class="promotion-employee-input"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><input type="search" id="promotion-employee" name="employee_search" placeholder="Search by name, ID, position or IT Admin" value="<?= $h($selectedEmployee?$employeeLabel($selectedEmployee):$postText('employee_search')) ?>" autocomplete="off" aria-describedby="promotion-employee-help promotion-employee-status" aria-controls="promotion-employee-results" aria-expanded="false" required></div>
 <input type="hidden" id="promotion-employee-id" name="employee_id" value="<?= $selectedEmployee?(int)$selectedEmployee['id']:'' ?>">
 <p id="promotion-employee-help" class="promotion-picker-help">Search employees or IT administrators, then choose a matching result.</p>
 <p id="promotion-employee-status" class="promotion-picker-help" role="status"><?= $selectedEmployee?'Selected: '.$h($employeeLabel($selectedEmployee)):'No employee selected.' ?></p>
 <ul id="promotion-employee-results" class="promotion-employee-results" aria-label="Matching employees" hidden>
 <?php foreach($staff as $person): ?><li hidden><button type="button" data-employee-id="<?= (int)$person['id'] ?>" data-employee-label="<?= $h($employeeLabel($person)) ?>"><span><?= $h($person['full_name']) ?></span><small><?= $h($person['employee_id'].' · '.($person['role']==='admin'?'IT Admin':'Employee').' · '.($person['job_title']?:($person['role']==='admin'?'IT Administrator':'Employee'))) ?></small></button></li><?php endforeach ?>
 </ul>
 <noscript><p>Enable JavaScript to search for an employee and send a promotion suggestion.</p></noscript>
</div>
