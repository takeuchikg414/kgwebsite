<?php
session_start();

// セッションデータの確認
if (!isset($_SESSION['entry_success'])) {
    // 直接アクセスされた場合は採用情報ページにリダイレクト
    header("Location: recruit.html");
    exit();
}

$entryData = $_SESSION['entry_success'];

// セキュリティのためデータをエスケープ
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

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

$startDateNames = [
    'immediately' => '即日',
    'within_1month' => '1ヶ月以内',
    'within_2months' => '2ヶ月以内',
    'within_3months' => '3ヶ月以内',
    'negotiable' => '要相談'
];

// 受付番号生成
$entryId = 'ENT-' . date('Ymd') . '-' . sprintf('%04d', rand(1, 9999));
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>エントリー完了 - 株式会社KGpartners</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="entry-styles.css">
</head>

<body>
    <!-- 紙吹雪エフェクト -->
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>

    <header>
        <nav class="container">
            <div class="logo">
                <a href="index.html">
                    <img src="img/logo.png" alt="kgpartnerslogo" width="200px">
                </a>
            </div>
<ul class="nav-links">
    <li><a href="index.html">ホーム</a></li>
    <li><a href="index.html#services">サービス</a></li>
    <li><a href="index.html#about">会社概要</a></li>
    <li><a href="philosophy.html">企業理念</a></li>
    <li><a href="recruit.html">採用情報</a></li>
    <li><a href="entry.html">エントリーフォーム</a></li> <!-- ← この行を追加 -->
    <li><a href="index.html#contact">お問い合わせ</a></li>
</ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>

    <section style="padding: 120px 0 100px; background: linear-gradient(135deg, #f8fafc 0%, #e8f5e8 100%);">
        <div class="container">
            <div class="success-container">
                <div class="success-card">
                    <div class="success-icon">
                        ✓
                    </div>
                    
                    <h1 class="success-title">エントリー完了</h1>
                    <p class="success-subtitle">ありがとうございました！</p>
                    
                    <div class="success-message">
                        <strong><?php echo h($entryData['name']); ?></strong> 様<br>
                        エントリーを受け付けました。<br>
                        担当者より1週間以内にご連絡いたします。<br>
                        ご応募いただき、誠にありがとうございます。
                    </div>

                    <div class="entry-details">
                        <h3>エントリー内容確認</h3>
                        
                        <div class="detail-grid">
                            <!-- 基本情報 -->
                            <div class="detail-section">
                                <h4>📋 受付情報</h4>
                                <div class="detail-item">
                                    <span class="detail-label">受付番号</span>
                                    <span class="detail-value"><?php echo h($entryId); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">受付日時</span>
                                    <span class="detail-value"><?php echo h($entryData['submit_date']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">お名前</span>
                                    <span class="detail-value"><?php echo h($entryData['name']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">フリガナ</span>
                                    <span class="detail-value"><?php echo h($entryData['name_kana']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">年齢</span>
                                    <span class="detail-value"><?php echo h($entryData['age']); ?>歳</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">メールアドレス</span>
                                    <span class="detail-value"><?php echo h($entryData['email']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">電話番号</span>
                                    <span class="detail-value"><?php echo h($entryData['phone']); ?></span>
                                </div>
                            </div>

                            <!-- 応募情報 -->
                            <div class="detail-section">
                                <h4>💼 応募情報</h4>
                                <div class="detail-item">
                                    <span class="detail-label">希望職種</span>
                                    <span class="detail-value"><?php echo h($positionNames[$entryData['desired_position']] ?? $entryData['desired_position']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">希望雇用形態</span>
                                    <span class="detail-value"><?php echo h($employmentTypeNames[$entryData['employment_type']] ?? $entryData['employment_type']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">希望勤務地</span>
                                    <span class="detail-value"><?php echo h($entryData['desired_location'] ?: '指定なし'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">入社希望時期</span>
                                    <span class="detail-value"><?php echo h($startDateNames[$entryData['start_date']] ?? $entryData['start_date']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">希望年収</span>
                                    <span class="detail-value"><?php echo h($entryData['desired_salary'] ?: '要相談'); ?></span>
                                </div>
                            </div>

                            <!-- 添付書類 -->
                            <div class="detail-section">
                                <h4>📎 添付書類</h4>
                                <div class="files-list">
                                    <?php if (!empty($entryData['files']['resume'])): ?>
                                    <div class="file-item">
                                        📄 履歴書: <?php echo h($entryData['files']['resume']); ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($entryData['files']['career_history'])): ?>
                                    <div class="file-item">
                                        📋 職務経歴書: <?php echo h($entryData['files']['career_history']); ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($entryData['files']['other_docs'])): ?>
                                    <?php foreach ($entryData['files']['other_docs'] as $doc): ?>
                                    <div class="file-item">
                                        📎 その他書類: <?php echo h($doc); ?>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="next-steps">
                        <h3>今後の流れ</h3>
                        <div class="steps-list">
                            <div class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-content">
                                    <h4 class="step-title">書類選考</h4>
                                    <p class="step-description">ご提出いただいた履歴書・職務経歴書を基に書類選考を行います（3-5営業日）</p>
                                </div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-content">
                                    <h4 class="step-title">ご連絡</h4>
                                    <p class="step-description">選考結果を<strong><?php echo h($entryData['phone']); ?></strong>または<strong><?php echo h($entryData['email']); ?></strong>にご連絡いたします</p>
                                </div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-content">
                                    <h4 class="step-title">面接</h4>
                                    <p class="step-description">書類選考通過の方には面接日程をご相談させていただきます</p>
                                </div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">4</div>
                                <div class="step-content">
                                    <h4 class="step-title">内定・入社</h4>
                                    <p class="step-description">最終選考通過の方には内定通知後、入社手続きを進めさせていただきます</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="entry-contact-info">
                        <h3>お問い合わせ</h3>
                        <p><strong>株式会社KGpartners 採用担当</strong></p>
                        <p>📧 Email: contact@kg-p.jp</p>
                        <p>🕒 営業時間: 平日 10:00-18:00</p>
                        <p>※選考に関するお問い合わせは、受付番号<strong><?php echo h($entryId); ?></strong>をお知らせください</p>
                    </div>

                    <div class="action-buttons">
                        <a href="index.html" class="btn-action btn-primary">ホームに戻る</a>
                        <a href="recruit.html" class="btn-action btn-secondary">採用情報を見る</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>サービス</h4>
                    <ul>
                        <li><a href="jinzai.html">人材紹介・派遣</a></li>
                        <li><a href="eigyo.html">営業代行</a></li>
                        <li><a href="it.html">IT事業</a></li>
                        <li><a href="syuzen.html">修繕事業</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>企業情報</h4>
                    <ul>
                        <li><a href="index.html#about">会社概要</a></li>
                        <li><a href="philosophy.html">企業理念</a></li>
                        <li><a href="recruit.html">採用情報</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>サポート</h4>
                    <ul>
                        <li><a href="index.html#contact">お問い合わせ</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>お問い合わせ</h4>
                    <div class="footer-contact">
                        <p>営業時間：平日 10:00-18:00</p>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 KGpartners Corporation. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // ハンバーガーメニュー
        const hamburger = document.querySelector('.hamburger');
        const navLinks = document.querySelector('.nav-links');

        hamburger.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            hamburger.classList.toggle('active');
        });

        // 紙吹雪エフェクトを3秒後に削除
        setTimeout(function() {
            const confetti = document.querySelectorAll('.confetti');
            confetti.forEach(piece => piece.remove());
        }, 3000);
    </script>
</body>

</html>

<?php
// セッション情報をクリア（セキュリティのため）
unset($_SESSION['entry_success']);
?>