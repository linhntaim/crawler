<!doctype html>
<html lang="{{ $locale }}">
<head>
    <meta charset="{{ $charset }}">
    <title></title>
</head>
<body>
<p>
    {{ $name }}様<br>
    お世話になっております。<br>
    <br>
    ご利用のアカウント「{{ $x_email_to_name }}」のパスワードをリセットするには以下のボタンをクリックしてください。<br>
    パスワードのリセットにお心当たりが無い場合はこのメールを無視してください。<br>
    <br>
    ----------------------------------------------------------------<br>
    URL：<a href="{{ $url_reset_password }}">{{ $url_reset_password }}</a><br>
    有効期限：{{ $expired_at }} まで<br>
    ----------------------------------------------------------------<br>
    <br>
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━<br>
    ※セキュリティ確保のために、上記のリンクへアクセスした後、<br>
    　「パスワード設定」機能より、パスワードの変更を行っていただくようお願いいたします。<br>
    ※このメールは送信専用メールアドレスから配信されています。<br>
    　このままご返信いただいてもお答えできませんので予めご了承ください。<br>
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
</p>
</body>
</html>
