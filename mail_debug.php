<?php
// backup_notification.php - メール送信失敗時のバックアップ

// entry_process.php に追加するバックアップシステム

function saveEntryToFile($formData, $uploadedFiles) {
    $timestamp = date('Y-m-d_H-i-s');
    $filename = "entries/entry_" . $timestamp . ".txt";
    
    // ディレクトリ作成
    if (!is_dir('entries')) {
        mkdir('entries', 0755, true);
    }
    
    $content = "=== エントリー情報 ===\n";
    $content .= "受信日時: " . date('Y-m-d H:i:s') . "\n";
    $content .= "氏名: " . $formData['name'] . "\n";
    $content .= "メール: " . $formData['email'] . "\n";
    $content .= "電話: " . $formData['phone'] . "\n";
    $content .= "希望職種: " . $formData['position'] . "\n";
    $content .= "\n添付ファイル:\n";
    
    foreach ($uploadedFiles as $type => $filename) {
        $content .= "- " . $type . ": " . $filename . "\n";
    }
    
    file_put_contents($filename, $content);
    return $filename;
}

function sendSlackNotification($message) {
    // Slack Webhook URL （設定が必要）
    $webhookUrl = "https://hooks.slack.com/services/YOUR/SLACK/WEBHOOK";
    
    if (empty($webhookUrl) || $webhookUrl === "https://hooks.slack.com/services/YOUR/SLACK/WEBHOOK") {
        return false; // 設定されていない場合はスキップ
    }
    
    $payload = json_encode([
        'text' => "🚨 新しいエントリーがありました！\n" . $message,
        'channel' => '#採用',
        'username' => 'エントリーBot',
        'icon_emoji' => ':briefcase:'
    ]);
    
    $ch = curl_init($webhookUrl);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result !== false;
}

function sendLineNotify($message) {
    // LINE Notify Token （設定が必要）
    $token = "YOUR_LINE_NOTIFY_TOKEN";
    
    if (empty($token) || $token === "YOUR_LINE_NOTIFY_TOKEN") {
        return false; // 設定されていない場合はスキップ
    }
    
    $api = "https://notify-api.line.me/api/notify";
    
    $headers = [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/x-www-form-urlencoded'
    ];
    
    $data = ['message' => "\n🔔 新しいエントリー\n" . $message];
    
    $ch = curl_init($api);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result !== false;
}

// === entry_process.php のメール送信部分に追加 ===

if (empty($errors)) {
    $formData = [
        'name' => $lastName . ' ' . $firstName,
        'email' => $email,
        'phone' => $phone,
        'position' => $desiredPosition
    ];
    
    // 📧 メール送信試行
    $mailSent = mail($to, $mailSubject, $mailBody, $headers);
    
    if ($mailSent) {
        error_log("✅ メール送信成功");
    } else {
        error_log("❌ メール送信失敗 - バックアップ通知開始");
        
        // 🔄 バックアップ通知システム
        $backupMessage = "氏名: " . $formData['name'] . "\n";
        $backupMessage .= "メール: " . $formData['email'] . "\n"; 
        $backupMessage .= "電話: " . $formData['phone'] . "\n";
        $backupMessage .= "希望職種: " . $formData['position'];
        
        // ファイルに保存
        $savedFile = saveEntryToFile($formData, $uploadedFiles);
        error_log("📁 エントリーファイル保存: " . $savedFile);
        
        // Slack通知（設定済みの場合）
        if (sendSlackNotification($backupMessage)) {
            error_log("📱 Slack通知送信成功");
        }
        
        // LINE通知（設定済みの場合）
        if (sendLineNotify($backupMessage)) {
            error_log("📲 LINE通知送信成功");
        }
        
        // 管理者に緊急ファイル作成
        file_put_contents('URGENT_CHECK_ENTRIES.txt', 
            "メール送信エラーが発生しています。\n" .
            "entriesフォルダを確認してください。\n" .
            "最新エントリー: " . $savedFile . "\n" .
            "発生日時: " . date('Y-m-d H:i:s')
        );
    }
    
    // 自動返信メール（応募者へ）
    mail($email, $autoReplySubject, $autoReplyBody, $autoReplyHeaders);
    
    // 成功ページへリダイレクト
    header("Location: entry_success.html");
    exit();
}
?>