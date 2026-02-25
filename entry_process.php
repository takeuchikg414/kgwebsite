<?php
// エラー表示設定（本番環境では削除）
error_reporting(E_ALL);
ini_set('display_errors', 1);

// セッション開始
session_start();

// POSTデータの取得と検証
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 基本情報の取得
    $lastName = trim($_POST['last_name']);
    $firstName = trim($_POST['first_name']);
    $lastNameKana = trim($_POST['last_name_kana']);
    $firstNameKana = trim($_POST['first_name_kana']);
    $birthDate = trim($_POST['birth_date']);
    $gender = trim($_POST['gender']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    
    // 応募情報の取得
    $desiredPosition = trim($_POST['desired_position']);
    $employmentType = trim($_POST['employment_type']);
    $desiredLocation = trim($_POST['desired_location']);
    $startDate = trim($_POST['start_date']);
    $desiredSalary = trim($_POST['desired_salary']);
    
    // 経歴・スキル情報の取得
    $education = trim($_POST['education']);
    $workExperience = trim($_POST['work_experience']);
    $workHistory = trim($_POST['work_history']);
    $qualifications = trim($_POST['qualifications']);
    $motivation = trim($_POST['motivation']);
    $selfPr = trim($_POST['self_pr']);
    
    // プライバシー同意
    $privacyConsent = isset($_POST['privacy_consent']) ? 'はい' : 'いいえ';
    
    // バリデーション
    $errors = [];
    
    // 必須項目チェック
    if (empty($lastName)) $errors[] = "姓は必須です。";
    if (empty($firstName)) $errors[] = "名は必須です。";
    if (empty($lastNameKana)) $errors[] = "セイ（カナ）は必須です。";
    if (empty($firstNameKana)) $errors[] = "メイ（カナ）は必須です。";
    if (empty($birthDate)) $errors[] = "生年月日は必須です。";
    if (empty($gender)) $errors[] = "性別は必須です。";
    if (empty($phone)) $errors[] = "電話番号は必須です。";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "有効なメールアドレスは必須です。";
    if (empty($address)) $errors[] = "住所は必須です。";
    if (empty($desiredPosition)) $errors[] = "希望職種は必須です。";
    if (empty($employmentType)) $errors[] = "希望雇用形態は必須です。";
    if (empty($startDate)) $errors[] = "入社希望時期は必須です。";
    if (empty($education)) $errors[] = "最終学歴は必須です。";
    if (empty($motivation)) $errors[] = "志望動機は必須です。";
    if (!isset($_POST['privacy_consent'])) $errors[] = "個人情報の取り扱いに同意してください。";
    
    // ファイルアップロード処理
    $uploadedFiles = [];
    $uploadDir = 'uploads/entry/';
    
    // アップロードディレクトリが存在しない場合は作成
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // 履歴書（必須）
    if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "履歴書の添付は必須です。";
    } else {
        $resumeFile = handleFileUpload($_FILES['resume'], $uploadDir, 'resume');
        if ($resumeFile['error']) {
            $errors[] = $resumeFile['error'];
        } else {
            $uploadedFiles['resume'] = $resumeFile['filename'];
        }
    }
    
    // 職務経歴書（任意）
    if (isset($_FILES['career_history']) && $_FILES['career_history']['error'] === UPLOAD_ERR_OK) {
        $careerFile = handleFileUpload($_FILES['career_history'], $uploadDir, 'career_history');
        if ($careerFile['error']) {
            $errors[] = $careerFile['error'];
        } else {
            $uploadedFiles['career_history'] = $careerFile['filename'];
        }
    }
    
    // その他書類（任意・複数）
    if (isset($_FILES['other_docs']) && is_array($_FILES['other_docs']['name'])) {
        $otherDocs = [];
        foreach ($_FILES['other_docs']['name'] as $key => $name) {
            if ($_FILES['other_docs']['error'][$key] === UPLOAD_ERR_OK) {
                $fileInfo = array(
                    'name' => $_FILES['other_docs']['name'][$key],
                    'type' => $_FILES['other_docs']['type'][$key],
                    'tmp_name' => $_FILES['other_docs']['tmp_name'][$key],
                    'error' => $_FILES['other_docs']['error'][$key],
                    'size' => $_FILES['other_docs']['size'][$key]
                );
                $otherFile = handleFileUpload($fileInfo, $uploadDir, 'other_' . $key);
                if ($otherFile['error']) {
                    $errors[] = $otherFile['error'];
                } else {
                    $otherDocs[] = $otherFile['filename'];
                }
            }
        }
        if (!empty($otherDocs)) {
            $uploadedFiles['other_docs'] = $otherDocs;
        }
    }
    
    // エラーがない場合はメール送信
    if (empty($errors)) {
        
        // 職種名の変換
        $positionNames = [
            'sales' => '営業スタッフ',
            'it_engineer' => 'ITエンジニア',
            'call_center' => 'コールセンタースタッフ',
            'hr_consultant' => '人材コンサルタント',
            'administrative' => '事務スタッフ',
            'other' => 'その他'
        ];
        
        $employmentTypeNames = [
            'fulltime' => '正社員',
            'contract' => '契約社員',
            'parttime' => 'パートタイム',
            'temp' => '派遣社員'
        ];
        
        $educationNames = [
            'high_school' => '高等学校卒業',
            'vocational_school' => '専門学校卒業',
            'junior_college' => '短期大学卒業',
            'university' => '大学卒業',
            'graduate_school' => '大学院卒業',
            'other' => 'その他'
        ];
        
        $startDateNames = [
            'immediately' => '即日',
            'within_1month' => '1ヶ月以内',
            'within_2months' => '2ヶ月以内',
            'within_3months' => '3ヶ月以内',
            'negotiable' => '要相談'
        ];
        
        // 年齢計算
        $today = new DateTime();
        $birth = new DateTime($birthDate);
        $age = $today->diff($birth)->y;
        
        // メール設定
        $to = "contact@kg-p.jp";
        $mailSubject = "【エントリー】" . $lastName . " " . $firstName . "様からの応募";
        
        // メール本文
        $mailBody = "エントリーフォームから応募がありました。\n\n";
        $mailBody .= "==== 基本情報 ====\n";
        $mailBody .= "■氏名: " . $lastName . " " . $firstName . "\n";
        $mailBody .= "■フリガナ: " . $lastNameKana . " " . $firstNameKana . "\n";
        $mailBody .= "■生年月日: " . $birthDate . " (年齢: " . $age . "歳)\n";
        $mailBody .= "■性別: " . $gender . "\n";
        $mailBody .= "■電話番号: " . $phone . "\n";
        $mailBody .= "■メールアドレス: " . $email . "\n";
        $mailBody .= "■住所: " . $address . "\n\n";
        
        $mailBody .= "==== 応募情報 ====\n";
        $mailBody .= "■希望職種: " . ($positionNames[$desiredPosition] ?? $desiredPosition) . "\n";
        $mailBody .= "■希望雇用形態: " . ($employmentTypeNames[$employmentType] ?? $employmentType) . "\n";
        $mailBody .= "■希望勤務地: " . ($desiredLocation ?: "未入力") . "\n";
        $mailBody .= "■入社希望時期: " . ($startDateNames[$startDate] ?? $startDate) . "\n";
        $mailBody .= "■希望年収: " . ($desiredSalary ?: "未入力") . "\n\n";
        
        $mailBody .= "==== 経歴・スキル ====\n";
        $mailBody .= "■最終学歴: " . ($educationNames[$education] ?? $education) . "\n";
        $mailBody .= "■職歴年数: " . ($workExperience ?: "未選択") . "\n";
        $mailBody .= "■職歴概要:\n" . ($workHistory ?: "未入力") . "\n\n";
        $mailBody .= "■保有資格・スキル:\n" . ($qualifications ?: "未入力") . "\n\n";
        $mailBody .= "■志望動機:\n" . $motivation . "\n\n";
        $mailBody .= "■自己PR:\n" . ($selfPr ?: "未入力") . "\n\n";
        
        $mailBody .= "==== 添付書類 ====\n";
        if (isset($uploadedFiles['resume'])) {
            $mailBody .= "■履歴書: " . $uploadedFiles['resume'] . "\n";
        }
        if (isset($uploadedFiles['career_history'])) {
            $mailBody .= "■職務経歴書: " . $uploadedFiles['career_history'] . "\n";
        }
        if (isset($uploadedFiles['other_docs'])) {
            $mailBody .= "■その他書類: " . implode(", ", $uploadedFiles['other_docs']) . "\n";
        }
        $mailBody .= "\n";
        
        $mailBody .= "■個人情報取り扱い同意: " . $privacyConsent . "\n";
        $mailBody .= "送信日時: " . date('Y-m-d H:i:s') . "\n";
        
        // メールヘッダー
        $headers = "From: " . $email . "\r\n";
        $headers .= "Reply-To: " . $email . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        // メール送信
        if (mail($to, $mailSubject, $mailBody, $headers)) {
            
            // 自動返信メール
            $autoReplySubject = "【株式会社KGpartners】エントリーありがとうございます";
            $autoReplyBody = $lastName . " " . $firstName . " 様\n\n";
            $autoReplyBody .= "この度は弊社にエントリーいただき、誠にありがとうございます。\n";
            $autoReplyBody .= "以下の内容でエントリーを受け付けいたしました。\n\n";
            $autoReplyBody .= "■希望職種: " . ($positionNames[$desiredPosition] ?? $desiredPosition) . "\n";
            $autoReplyBody .= "■希望雇用形態: " . ($employmentTypeNames[$employmentType] ?? $employmentType) . "\n";
            $autoReplyBody .= "■入社希望時期: " . ($startDateNames[$startDate] ?? $startDate) . "\n\n";
            $autoReplyBody .= "担当者より1週間以内にご連絡いたします。\n";
            $autoReplyBody .= "しばらくお待ちください。\n\n";
            $autoReplyBody .= "なお、添付いただいた書類は選考終了後、適切に処分いたします。\n\n";
            $autoReplyBody .= "株式会社KGpartners 採用担当\n";
            $autoReplyBody .= "営業時間: 平日 9:00-18:00\n";
            $autoReplyBody .= "Email: contact@kg-p.jp";
            
            $autoReplyHeaders = "From: contact@kg-p.jp\r\n";
            $autoReplyHeaders .= "Content-Type: text/plain; charset=UTF-8\r\n";
            
            mail($email, $autoReplySubject, $autoReplyBody, $autoReplyHeaders);
            
            // セッションに成功情報を保存
            $_SESSION['entry_success'] = [
                'name' => $lastName . ' ' . $firstName,
                'name_kana' => $lastNameKana . ' ' . $firstNameKana,
                'age' => $today->diff($birth)->y,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'desired_position' => $desiredPosition,
                'employment_type' => $employmentType,
                'desired_location' => $desiredLocation,
                'start_date' => $startDate,
                'desired_salary' => $desiredSalary,
                'files' => $uploadedFiles,
                'submit_date' => date('Y年m月d日 H:i')
            ];
            
            // 成功ページにリダイレクト
            header("Location: entry_success.php");
            exit();
            
        } else {
            $errorMessage = "送信に失敗しました。しばらく時間をおいて再度お試しください。";
        }
        
    } else {
        $errorMessage = implode("<br>", $errors);
    }
    
} else {
    header("Location: entry.html");
    exit();
}

// ファイルアップロード処理関数
function handleFileUpload($file, $uploadDir, $prefix) {
    $maxFileSize = 20 * 1024 * 1024; // 20MB
    $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'];
    $allowedExtensions = ['pdf', 'doc', 'docx', 'zip'];
    
    // ファイルサイズチェック
    if ($file['size'] > $maxFileSize) {
        return ['error' => 'ファイルサイズが大きすぎます（最大20MB）', 'filename' => null];
    }
    
    // ファイル拡張子チェック
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $allowedExtensions)) {
        return ['error' => '対応していないファイル形式です', 'filename' => null];
    }
    
    // ファイル名の生成（重複防止）
    $timestamp = date('YmdHis');
    $randomString = bin2hex(random_bytes(8));
    $fileName = $prefix . '_' . $timestamp . '_' . $randomString . '.' . $fileExtension;
    $filePath = $uploadDir . $fileName;
    
    // ファイル移動
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return ['error' => null, 'filename' => $fileName];
    } else {
        return ['error' => 'ファイルのアップロードに失敗しました', 'filename' => null];
    }
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
        <div style="background: #ffebee; border: 1px solid #f44336; padding: 20px; border-radius: 5px; margin: 20px 0; line-height: 1.6;">
            <?php echo $errorMessage; ?>
        </div>
        <a href="entry.html" class="btn btn-primary">戻る</a>
    </div>
</body>
</html>