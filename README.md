# MIndie Client

Built upon [IndieAuth Client](indieauth-client-php/README.md) ([source](https://github.com/indieweb/indieauth-client-php)) to give a minimally self-hosted [IndieAuth](https://indieweb.org/IndieAuth) client. This functions as both a native [IndieAuth](https://indieauth.net/) ([spec](https://indieauth.spec.indieweb.org/)) client and a generic [OAuth2.0](https://www.oauth.com/) ([Auth0.com](https://auth0.com/docs)) client.

## Setup

1. Clone to `/usr/local/src/`
1. Run `dependencies.bash` to install dependent Ubuntu packages (like Apache HTTPd and PHP).
1. Run `setup.bash` to setup required directories and files.
1. Add configuration to your Apache HTTPd configuration (example found [indieauth-client-php.conf.example](indieauth-client-php.conf.example)) replacing `<client>`, `<your idp>`, and `<your host>` throughout
    ```
    Alias /<client>/index /usr/local/src/mindie-client/indieauth-client-php/index.php
    Alias /<client>/login /usr/local/src/mindie-client/indieauth-client-php/login.php
    Alias /<client>/redirect /usr/local/src/mindie-client/indieauth-client-php/redirect.php
    <LocationMatch /<client>/(index/login/redirect)$ >
	    SetEnv CLIENT_PATH <client>
	    <RequireAll>
		    Require all granted
	    </RequireAll>
    </LocationMatch>
    <Location /<client>/>
	    SetEnv CLIENT_PATH <client>
	    AuthType oauth2
	    AuthName "Hello OAuth"
	    # set the scopes to request
	    SetEnv CLIENT_SCOPE "profile oauth"
	    ErrorDocument 401 /<client>/index
	    OAuth2AcceptTokenIn header
	    OAuth2AcceptTokenIn cookie name=oauth_token
	    OAuth2TokenVerify metadata <your idp>/.well-known/oauth-authorization-server introspect.auth=client_secret_basic&client_id=<your host>/<client>/&client_secret=_
	    <RequireAll>
		    Require valid-user
	    </RequireAll>
    </Location>
    ```

This will setup the following endpoints on your Apache server:
- `https://example.com/<client>/index`
- `https://example.com/<client>/login`
- `https://example.com/<client>/redirect`

### Insecure Configuration

If you are choosing to use this isolated from the internet on your homenet, you **MUST** make the additional modification to the [Client.php](https://github.com/indieweb/indieauth-client-php/blob/main/src/IndieAuth/Client.php#L229) to allow the insecure `HTTP`.

```diff
/usr/local/lib/indieauth-client-php/vendor/indieauth/client/src/IndieAuth/Client.php:229
-    if (!array_key_exists('scheme', $parts) || $parts['scheme'] != 'https') {
+    if (!array_key_exists('scheme', $parts) || ($parts['scheme'] != 'https' && $parts['scheme'] != 'http')) {
```

### Potential Issue

If there are complaints that the issuer does not match, this could be because of the presence or absence of a trailing slash (`/`) in your metadata endpoint. To resolve this, you *MAY* make the additional modification to the [Client.php](https://github.com/indieweb/indieauth-client-php/blob/main/src/IndieAuth/Client.php#L534) to allow the insecure `HTTP`.

```diff
/usr/local/lib/indieauth-client-php/vendor/indieauth/client/src/IndieAuth/Client.php:534
-    if ($params['iss'] !== $expected_issuer) {
+    if (self::normalizeMeURL($params['iss']) !== self::normalizeMeURL($expected_issuer)) {
```

### Environment Variables

Set these in Apache HTTPd config.

- `SetEnv CLIENT_PATH <client>` - for your client ID to be `https://example.com/<client>/`
- `SetEnv CLIENT_SCOPE "profile oauth"` - to set the scopes that will be requested

### Session Variables

For your use on your PHP endpoints.

- `$_SESSION['user']`
- `$_SESSION['token']`
- `$_SESSION['scope']`

### Cookies

For your use on your endpoints.

- `me`
- `oauth_token`
- `oauth_scope`

Temporary cookie to facilitate restoring path after being redirected to a login page.

- `auth_redirect`

## Usage

1. Navigate to `https://example.com/<client>/some/path`
1. When redirected to a login page, enter your profile URI and click `Log In`
1. Authenticate and Authorize (unselect scopes that you wish to deny)
1. Be redirected back to `https://example.com/<client>/some/path`

## License

Copyright 2024 by carrvo

Available under the MIT license.

See [MIT-LICENSE.md](MIT-LICENSE.md) for the text of this license.

