<!doctype html>
<html lang="{{ $locale }}">
<head>
    <meta charset="{{ $charset }}">
    <title></title>
</head>
<body>
<p>
    Dear {{ $name }},<br>
    <br>
    We've got a request to reset your password.<br>
    <br>
    Please click the link below to reset password for your account.<br>
    If you do not make the request, please ignore this email.<br>
    <br>
    ----------------------------------------------------------------<br>
    URL: <a href="{{ $url_reset_password }}">{{ $url_reset_password }}</a><br>
    @if ($expired_at)
        The link will be expired at: {{ $expired_at }}<br>
    @endif
    ----------------------------------------------------------------<br>
    <br>
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━<br>
    For security, please change the password immediately after you access the link.<br>
    This email is for sending only. Please do not make any reply to it.<br>
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
</p>
</body>
</html>
