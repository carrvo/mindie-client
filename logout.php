<?php
session_start();

try {
    // clear all cookies
    setcookie('auth_redirect', '', -1, getenv('CLIENT_PATH').'/', $_SERVER['HTTP_HOST'], false, false);
    setcookie('me', '', -1, getenv('CLIENT_PATH').'/', $_SERVER['HTTP_HOST'], false, false);
    setcookie('oauth_token', '', -1, getenv('CLIENT_PATH').'/', $_SERVER['HTTP_HOST'], false, false);
    setcookie('oauth_scope', '', -1, getenv('CLIENT_PATH').'/', $_SERVER['HTTP_HOST'], false, false);
} catch(Exception $e) {
    # suppress
} finally {
    // clear session
    $_SESSION = array();
    session_destroy();
}

$auth_redirect = '';
// priority 1: a location argument
if (empty(getenv('REDIRECT_URL')) !== true) {
    $auth_redirect = getenv('REDIRECT_URL').'?'.getenv('REDIRECT_QUERY_STRING');
}
// priority 2: the previous page
elseif (isset($_SERVER['HTTP_REFERER'])) {
    $auth_redirect = $_SERVER['HTTP_REFERER'];
}
// priority 3: the home page
elseif (empty(getenv('CLIENT_HOME')) !== true) {
    $auth_redirect = getenv('CLIENT_PATH') . '/' . getenv('CLIENT_HOME');
}
// priority 4: pre-configured logout page
elseif (empty(getenv('CLIENT_LOGOUT')) !== true) {
    $auth_redirect = getenv('CLIENT_PATH') . '/' . getenv('CLIENT_LOGOUT');
}
// priority last: bare bones logout page

if (empty($auth_redirect) !== true) {
    header("location: $auth_redirect");
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="UTF-8">
    <title>MIndie - logout</title>
</head>
<body>
    <p>Session cleared.</p>
</body>
</html>
