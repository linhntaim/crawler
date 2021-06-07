<!doctype html>
<html lang="{{ $locale }}">
<head>
    <meta charset="{{ $charset }}">
    <title></title>
</head>
<body>
<p>
    {{ $name }}様<br>
    <br>
    アカウントのメールアドレスを承認するには、以下のリンクをクリックしてください。<br>
    <br>
    ----------------------------------------------------------------<br>
    URL：<a href="{{ $url_verify_email }}">{{ $url_verify_email }}</a><br>
    @if ($expired_at)
        有効期限：{{ $expired_at }} まで<br>
    @endif
    ----------------------------------------------------------------<br>
    <br>
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━<br>
    ※このメールは送信専用メールアドレスから配信されています。<br>
    　このままご返信いただいてもお答えできませんので予めご了承ください。<br>
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
</p>
</body>
</html>
