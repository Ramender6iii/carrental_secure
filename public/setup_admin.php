<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';

$accounts = [
 ['admin','admin@example.test','Admin@123','ADMIN'],
 ['manager','manager@example.test','Manager@123','MANAGER'],
 ['staff','staff@example.test','Staff@123','RENTAL_STAFF'],
];
$stmt=$pdo->prepare("INSERT INTO users(username,email,password_hash,role,status) VALUES(:u,:e,:p,:r,'ACTIVE') ON DUPLICATE KEY UPDATE email=VALUES(email),password_hash=VALUES(password_hash),role=VALUES(role),status='ACTIVE',failed_attempts=0");
foreach($accounts as $a){$stmt->execute([':u'=>$a[0],':e'=>$a[1],':p'=>password_hash($a[2],PASSWORD_DEFAULT),':r'=>$a[3]]);}

$staffId=(int)$pdo->query("SELECT id FROM users WHERE username='staff'")->fetchColumn();
$managerId=(int)$pdo->query("SELECT id FROM users WHERE username='manager'")->fetchColumn();
$seeded=false;

/* Seed exactly 10 completed coursework rental transactions only when the rental table is empty. */
if((int)$pdo->query("SELECT COUNT(*) FROM rentals")->fetchColumn()===0){
  $pdo->beginTransaction();
  try{
    $customers=[
      ['Aisha Rahman','8123 4501','aisha.rahman@example.test','12 Orchard View','A101','2029-05-31'],
      ['Daniel Tan','8234 5612','daniel.tan@example.test','8 Marina Walk','B202','2028-11-30'],
      ['Mei Lin','8345 6723','mei.lin@example.test','21 River Road','C303','2030-02-28'],
      ['Arun Kumar','8456 7834','arun.kumar@example.test','5 Garden Avenue','D404','2029-09-30'],
      ['Nur Izzati','8567 8945','nur.izzati@example.test','16 Hill Street','E505','2028-07-31'],
      ['Jason Lim','8678 9056','jason.lim@example.test','30 East Coast Way','F606','2030-01-31'],
      ['Siti Aminah','8789 0167','siti.aminah@example.test','9 Lake Drive','G707','2029-12-31'],
      ['Marcus Lee','8890 1278','marcus.lee@example.test','42 Central Park','H808','2028-10-31'],
      ['Priya Nair','8901 2389','priya.nair@example.test','7 Sunrise Lane','J909','2029-08-31'],
      ['Kelvin Wong','9012 3490','kelvin.wong@example.test','18 West Avenue','K010','2030-06-30'],
    ];
    $rentals=[
      [1,'2026-06-01','2026-06-03',450.00,450.00,0.00,'NOT_REQUIRED'],
      [2,'2026-06-05','2026-06-07',480.00,480.00,0.00,'NOT_REQUIRED'],
      [3,'2026-06-10','2026-06-12',330.00,330.00,0.00,'NOT_REQUIRED'],
      [4,'2026-06-14','2026-06-16',300.00,300.00,0.00,'NOT_REQUIRED'],
      [5,'2026-06-20','2026-06-22',660.00,594.00,10.00,'NOT_REQUIRED'],
      [6,'2026-07-01','2026-07-03',780.00,780.00,0.00,'NOT_REQUIRED'],
      [7,'2026-07-06','2026-07-08',840.00,714.00,15.00,'APPROVED'],
      [8,'2026-07-12','2026-07-14',540.00,540.00,0.00,'NOT_REQUIRED'],
      [9,'2026-07-20','2026-07-22',720.00,720.00,0.00,'NOT_REQUIRED'],
      [10,'2026-08-01','2026-08-03',285.00,285.00,0.00,'NOT_REQUIRED'],
    ];
    $cs=$pdo->prepare("INSERT INTO customers(full_name,mobile,email,address,licence_last4,licence_expiry,licence_verified) VALUES(:n,:m,:e,:a,:l,:x,1)");
    $rs=$pdo->prepare("INSERT INTO rentals(car_id,customer_id,rental_date,return_date,actual_return_date,standard_amount,amount,discount_percent,approval_status,requested_by,approved_by,approval_note,approved_at,status) VALUES(:car,:customer,:rd,:ret,:actual,:std,:amt,:disc,:approval,:requester,:approver,:note,:approved_at,'COMPLETED')");
    foreach($customers as $i=>$c){
      $cs->execute([':n'=>$c[0],':m'=>$c[1],':e'=>$c[2],':a'=>$c[3],':l'=>$c[4],':x'=>$c[5]]);
      $cid=(int)$pdo->lastInsertId(); $r=$rentals[$i]; $approved=$r[6]==='APPROVED';
      $rs->execute([':car'=>$r[0],':customer'=>$cid,':rd'=>$r[1],':ret'=>$r[2],':actual'=>$r[2],':std'=>$r[3],':amt'=>$r[4],':disc'=>$r[5],':approval'=>$r[6],':requester'=>$staffId,':approver'=>$approved?$managerId:null,':note'=>$approved?'Manager approved 15% coursework demo discount.':null,':approved_at'=>$approved?'2026-07-06 10:30:00':null]);
    }
    $pdo->commit(); $seeded=true;
  }catch(Throwable $e){ if($pdo->inTransaction())$pdo->rollBack(); throw $e; }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Coursework Setup</title><link rel="stylesheet" href="assets/style.css"></head><body class="login-only-page"><div class="login-only-card"><h1>Coursework Setup Complete</h1><div class="info-box"><strong>Demo staff accounts</strong><br>admin / Admin@123 — admin@example.test<br>manager / Manager@123 — manager@example.test<br>staff / Staff@123 — staff@example.test</div><div class="info-box"><strong>Password recovery demo</strong><br>Use Forgot Password with a demo username and email. The request appears under System Administrator → User Accounts → Password Reset Requests.</div><div class="info-box"><strong>Demo transactions</strong><br><?= $seeded ? '10 completed rental transactions and 10 synthetic customers were created.' : 'Existing rental data was detected, so demo transactions were not duplicated.' ?></div><p class="muted">All names, addresses, phone numbers and email addresses are synthetic coursework data. The <code>example.test</code> domain does not deliver real email.</p><p><strong>Security:</strong> delete <code>setup_admin.php</code> after setup.</p><a class="login-button button-link" href="login.php">Open Login</a></div></body></html>
