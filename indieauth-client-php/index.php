<?php
$auth_redirect = getenv('REDIRECT_URL').'?'.getenv('REDIRECT_QUERY_STRING');
setcookie('auth_redirect', $auth_redirect, 0, '/'.getenv('CLIENT_PATH').'/', $_SERVER['HTTP_HOST'], false, false);
?>
<!DOCTYPE HTML>
<html>
<head>
        <?php if (empty(getenv('CLIENT_AUTO_ANONYMOUS')) !== true) : ?>
        <script>
        document.addEventListener("DOMContentLoaded", (event) => {
            document.getElementById("url").value = "<?php echo getenv('CLIENT_AUTO_ANONYMOUS') ?>";
            document.getElementsByClassName("submit")[0].click();
        });
        </script>
        <?php endif; ?>
    <style>
	.error {
	    color: yellow;
            background-color: black;
        }
    </style>
</head>
<body>
    <?php if (isset($_COOKIE['me'])) : ?>
	<?php if (isset($_COOKIE['oauth_token'])) : ?>
            <p class="error">Please ensure that your IdP provides the <code>sub</code> claim during introspection!</p>
	<?php else : ?>
            <p class="error">Please ensure that your IdP supports introspection!</p>
	<?php endif; ?>
        <p class="error">You may want to login with <a href="#url-anonymous">Stay Anonymous</a> in the meantime.</p>
    <?php endif; ?>
    <form action="/<?php echo getenv('CLIENT_PATH') ?>/login" method="post">
    <input id="url" type="url" name="url" placeholder="IndieAuth URI" required autofocus>
    <input class="submit" type="submit" value="Log In">
  </form>
    <?php if (empty(getenv('CLIENT_ANONYMOUS')) !== true) : ?>
        <form action="/<?php echo getenv('CLIENT_PATH') ?>/login" method="post">
        <input id="url-anonymous" type="url" name="url" value="<?php echo getenv('CLIENT_ANONYMOUS') ?>" required hidden>
        <input class="submit" type="submit" value="Stay Anonymous">
        </form>
	<?php else : ?>
	    <?php trigger_error('no CLIENT_ANONYMOUS set', E_USER_NOTICE) ?>
    <?php endif; ?>
</body>
</html>
