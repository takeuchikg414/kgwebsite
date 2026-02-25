<?php
// エラー表示設定（本番環境では削除）
error_reporting(E_ALL);
ini_set('display_errors', 1);

// POSTデータの取得と検証
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 入力データの取得
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $company = trim($_POST['company']);
    $phone = trim($_POST['phone']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    // バリデーション
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "お名前は必須です。";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "有効なメールアドレスを入力してください。";
    }
    
    if (empty($subject)) {
        $errors[] = "件名は必須です。";
    }
    
    if (empty($message)) {
        $errors[] = "お問い合わせ内容は必須です。";
    }
    
    // エラーがない場合はメール送信
    if (empty($errors)) {
        
        // メール設定
        $to = "contact@kg-p.jp";
        $mail_subject = "【お問い合わせ】" . $subject;
        
        // メール本文
        $mail_body = "お問い合わせフォームからメッセージが届きました。\n\n";
        $mail_body .= "■お名前: " . $name . "\n";
        $mail_body .= "■メールアドレス: " . $email . "\n";
        $mail_body .= "■会社名: " . ($company ? $company : "未入力") . "\n";
        $mail_body .= "■電話番号: " . ($phone ? $phone : "未入力") . "\n";
        $mail_body .= "■件名: " . $subject . "\n";
        $mail_body .= "■お問い合わせ内容:\n" . $message . "\n\n";
        $mail_body .= "送信日時: " . date('Y-m-d H:i:s') . "\n";
        
        // メールヘッダー
        $headers = "From: " . $email . "\r\n";
        $headers .= "Reply-To: " . $email . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        // メール送信
        if (mail($to, $mail_subject, $mail_body, $headers)) {
            
            // 自動返信メール
            $auto_reply_subject = "【株式会社KGpartners】お問い合わせありがとうございます";
            $auto_reply_body = $name . " 様\n\n";
            $auto_reply_body .= "この度はお問い合わせいただき、ありがとうございます。\n";
            $auto_reply_body .= "以下の内容でお問い合わせを受け付けいたしました。\n\n";
            $auto_reply_body .= "■件名: " . $subject . "\n";
            $auto_reply_body .= "■お問い合わせ内容:\n" . $message . "\n\n";
            $auto_reply_body .= "担当者より3営業日以内にご連絡いたします。\n";
            $auto_reply_body .= "しばらくお待ちください。\n\n";
            $auto_reply_body .= "株式会社KGpartners\n";
            $auto_reply_body .= "営業時間: 平日 9:00-18:00";
            
            $auto_reply_headers = "From: contact@kg-p.jp\r\n";
            $auto_reply_headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            
            mail($email, $auto_reply_subject, $auto_reply_body, $auto_reply_headers);
            
            // 成功ページにリダイレクト
            header("Location: contact_success.html");
            exit();
            
        } else {
            $error_message = "メール送信に失敗しました。しばらく時間をおいて再度お試しください。";
        }
        
    } else {
        $error_message = implode("<br>", $errors);
    }
    
} else {
    header("Location: index.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>エラー - 株式会社KGpartners</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container" style="padding: 100px 20px; text-align: center;">
        <h1>エラーが発生しました</h1>
        <div style="background: #ffebee; border: 1px solid #f44336; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <?php echo $error_message; ?>
        </div>
        <a href="index.html" class="btn btn-primary">戻る</a>
    </div>
</body>
</html>