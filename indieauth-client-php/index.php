<?php
if (empty(getenv('REDIRECT_URL')) !== true) {
    $auth_redirect = getenv('REDIRECT_URL').'?'.getenv('REDIRECT_QUERY_STRING');
}
else if (isset($_SERVER['HTTP_REFERER'])) {
    $auth_redirect = $_SERVER['HTTP_REFERER'];
}
else {
    $auth_redirect = getenv('CLIENT_PATH') . '/' . getenv('CLIENT_HOME');
}
setcookie('auth_redirect', $auth_redirect, 0, getenv('CLIENT_PATH').'/', $_SERVER['HTTP_HOST'], false, false);
if (empty(getenv('AUTH_CHALLENGES')) !== true) {
    $challenges = getenv('AUTH_CHALLENGES');
    header("WWW-Authenticate: $challenges", getenv('AUTH_CHALLENGES_OVERRIDE') === "on");
}
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
    <script>
      /* Copyright: aaronparecki Source: https://aaronparecki.com/2018/06/03/4/url-form-field */
      /* add http:// to URL fields on blur or when enter is pressed */
      document.addEventListener('DOMContentLoaded', function() {
        function addDefaultScheme(target) {
          if(target.value.match(/^(?!https?:).+\..+/)) {
            target.value = "http://"+target.value;
          }
        }
        var elements = document.querySelectorAll("input[type=url]");
        Array.prototype.forEach.call(elements, function(el, i){
          el.addEventListener("blur", function(e){
            addDefaultScheme(e.target);
          });
          el.addEventListener("keydown", function(e){
            if(e.keyCode == 13) {
              addDefaultScheme(e.target);
            }
          });
        });
      });

    </script>
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
    <form action="<?php echo getenv('CLIENT_PATH') ?>/login" method="post">
    <input id="url" type="url" name="url" placeholder="IndieAuth URI" required autofocus>
    <input class="submit" type="submit" value="Log In">
  </form>
    <?php if (empty(getenv('CLIENT_ANONYMOUS')) !== true) : ?>
        <form action="<?php echo getenv('CLIENT_PATH') ?>/login" method="post">
        <input id="url-anonymous" type="url" name="url" value="<?php echo getenv('CLIENT_ANONYMOUS') ?>" required hidden>
        <input class="submit" type="submit" value="Stay Anonymous">
        </form>
	<?php else : ?>
	    <?php trigger_error('no CLIENT_ANONYMOUS set', E_USER_NOTICE) ?>
    <?php endif; ?>
</body>
</html>
