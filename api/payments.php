<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/paystack.php';

header('Content-Type: application/json');
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    $db = getDB();

    if ($action === 'initialize') {
        requireLogin();
        if (!verifyCSRF($_POST['csrf'] ?? '')) { http_response_code(403); echo json_encode(['error'=>'Invalid token']); exit; }

        $userId  = $_SESSION['user_id'];
        $amount  = (float)($_POST['amount'] ?? 0);
        $purpose = sanitize($_POST['purpose'] ?? 'deposit');

        if ($amount < 1) { echo json_encode(['error'=>'Invalid amount']); exit; }

        $stUser = $db->prepare("SELECT email, first_name, last_name FROM users WHERE id=? LIMIT 1");
        $stUser->execute([$userId]);
        $user = $stUser->fetch();

        $ref  = generatePaymentRef();
        $result = initializePayment($user['email'], $amount, $ref, [
            'user_id'   => $userId,
            'purpose'   => $purpose,
            'user_name' => $user['first_name'] . ' ' . $user['last_name']
        ]);

        if ($result['status']) {
            // Log pending transaction
            $db->prepare(
                "INSERT INTO transactions (uuid,user_id,reference,type,amount,fee,net_amount,currency,payment_gateway,status,description)
                 VALUES (?,?,?,?,?,0,?,?,?,?,?)"
            )->execute([
                generateUUID(), $userId, $ref, 'deposit',
                $amount, $amount, 'GHS', 'paystack', 'pending',
                "Wallet deposit via Paystack"
            ]);

            echo json_encode(['success'=>true, 'url'=>$result['data']['authorization_url'], 'reference'=>$ref]);
        } else {
            echo json_encode(['error' => $result['message'] ?? 'Payment init failed']);
        }
        exit;
    }

    if ($action === 'verify') {
        $ref = sanitize($_GET['ref'] ?? $_GET['reference'] ?? '');
        if (!$ref) { echo json_encode(['error'=>'Missing reference']); exit; }

        $result = verifyPayment($ref);

        if ($result['status'] && $result['data']['status'] === 'success') {
            $amount = $result['data']['amount'] / 100;

            // Get pending transaction
            $stTx = $db->prepare("SELECT * FROM transactions WHERE reference=? LIMIT 1");
            $stTx->execute([$ref]);
            $tx = $stTx->fetch();

            if ($tx && $tx['status'] === 'pending') {
                $db->beginTransaction();

                // Update transaction
                $db->prepare("UPDATE transactions SET status='completed', gateway_reference=? WHERE reference=?")
                   ->execute([$result['data']['id'], $ref]);

                // Update wallet
                $db->prepare("UPDATE wallets SET available_balance=available_balance+? WHERE user_id=?")
                   ->execute([$amount, $tx['user_id']]);

                $db->commit();

                // If redirect from browser
                if (isset($_GET['redirect'])) {
                    header("Location: " . APP_URL . "/client/dashboard.php?success=Payment+of+".formatCurrency($amount)."+added+to+wallet");
                    exit;
                }

                echo json_encode(['success'=>true, 'amount'=>$amount]);
            } else {
                echo json_encode(['success'=>false, 'message'=>'Already processed or not found']);
            }
        } else {
            echo json_encode(['success'=>false, 'message'=>'Payment not successful']);
            if (isset($_GET['redirect'])) {
                header("Location: " . APP_URL . "/client/dashboard.php?error=Payment+failed");
                exit;
            }
        }
        exit;
    }

    if ($action === 'escrow_lock') {
        requireLogin();
        if (!verifyCSRF($_POST['csrf'] ?? '')) { echo json_encode(['error'=>'Invalid token']); exit; }

        $userId     = $_SESSION['user_id'];
        $jobId      = (int)($_POST['job_id']      ?? 0);
        $providerId = (int)($_POST['provider_id'] ?? 0);
        $amount     = (float)($_POST['amount']    ?? 0);

        if (!$jobId || !$providerId || $amount <= 0) { echo json_encode(['error'=>'Invalid data']); exit; }

        // Check wallet balance
        $stWallet = $db->prepare("SELECT available_balance FROM wallets WHERE user_id=? LIMIT 1");
        $stWallet->execute([$userId]);
        $wallet = $stWallet->fetch();

        if (!$wallet || $wallet['available_balance'] < $amount) {
            echo json_encode(['error'=>'Insufficient wallet balance']); exit;
        }

        $fee            = $amount * (PLATFORM_FEE_PERCENT / 100);
        $providerAmount = $amount - $fee;

        $db->beginTransaction();
        // Deduct from client wallet
        $db->prepare("UPDATE wallets SET available_balance=available_balance-?, pending_balance=pending_balance+? WHERE user_id=?")
           ->execute([$amount, $amount, $userId]);

        // Create escrow record
        $db->prepare("INSERT INTO escrow (job_id,client_id,provider_id,amount,platform_fee,provider_amount) VALUES (?,?,?,?,?,?)")
           ->execute([$jobId, $userId, $providerId, $amount, $fee, $providerAmount]);

        // Update job status
        $db->prepare("UPDATE jobs SET status='in_progress', hired_provider_id=? WHERE id=? AND client_id=?")
           ->execute([$providerId, $jobId, $userId]);

        // Log transaction
        $db->prepare(
            "INSERT INTO transactions (uuid,user_id,job_id,reference,type,amount,fee,net_amount,status,description)
             VALUES (?,?,?,?,?,?,?,?,?,?)"
        )->execute([generateUUID(),$userId,$jobId,generatePaymentRef(),'escrow_lock',$amount,$fee,$amount,'completed','Escrow locked for job '.$jobId]);

        $db->commit();

        echo json_encode(['success'=>true, 'message'=>'Escrow locked successfully']);
        exit;
    }

    if ($action === 'escrow_release') {
        requireLogin();
        if (!verifyCSRF($_POST['csrf'] ?? '')) { echo json_encode(['error'=>'Invalid token']); exit; }

        $userId = $_SESSION['user_id'];
        $jobId  = (int)($_POST['job_id'] ?? 0);

        $stEscrow = $db->prepare("SELECT * FROM escrow WHERE job_id=? AND client_id=? AND status='held' LIMIT 1");
        $stEscrow->execute([$jobId, $userId]);
        $escrow = $stEscrow->fetch();

        if (!$escrow) { echo json_encode(['error'=>'Escrow not found']); exit; }

        $db->beginTransaction();
        // Release to provider
        $stProv = $db->prepare("SELECT user_id FROM providers WHERE id=? LIMIT 1");
        $stProv->execute([$escrow['provider_id']]);
        $provRow = $stProv->fetch();

        $db->prepare("UPDATE wallets SET available_balance=available_balance+?, total_earned=total_earned+? WHERE user_id=?")
           ->execute([$escrow['provider_amount'], $escrow['provider_amount'], $provRow['user_id']]);

        // Remove from client pending
        $db->prepare("UPDATE wallets SET pending_balance=pending_balance-? WHERE user_id=?")
           ->execute([$escrow['amount'], $userId]);

        // Update escrow
        $db->prepare("UPDATE escrow SET status='released', released_at=NOW() WHERE job_id=?")->execute([$jobId]);
        $db->prepare("UPDATE jobs SET status='completed' WHERE id=?")->execute([$jobId]);

        // Log release transaction
        $db->prepare(
            "INSERT INTO transactions (uuid,user_id,job_id,reference,type,amount,fee,net_amount,status,description)
             VALUES (?,?,?,?,?,?,?,?,?,?)"
        )->execute([generateUUID(),$provRow['user_id'],$jobId,generatePaymentRef(),'escrow_release',
                    $escrow['provider_amount'],0,$escrow['provider_amount'],'completed','Payment for job '.$jobId]);

        $db->commit();
        echo json_encode(['success'=>true, 'message'=>'Payment released to provider!']);
        exit;
    }

    echo json_encode(['error'=>'Unknown action']);

} catch(Exception $e) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    error_log($e->getMessage());
    echo json_encode(['error'=>'Server error: ' . $e->getMessage()]);
}