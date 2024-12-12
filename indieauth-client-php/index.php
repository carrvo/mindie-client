<?php
$auth_redirect = getenv('REDIRECT_URL').'?'.getenv('REDIRECT_QUERY_STRING');
setcookie('auth_redirect', $auth_redirect, 0, '/'.getenv('CLIENT_PATH').'/', $_SERVER['HTTP_HOST'], false, false);
?>
<!DOCTYPE HTML>
<html>
<head>
</head>
<body>
    <form action="/<?php echo getenv('CLIENT_PATH') ?>/login" method="post">
    <input type="url" name="url" placeholder="IndieAuth URI" required autofocus>
    <input type="submit" value="Log In">
  </form>
</body>
</html>
