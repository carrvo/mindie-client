# MIndie Client

Built upon [IndieAuth Client](indieauth-client-php/README.md) ([source](https://github.com/indieweb/indieauth-client-php)) to give a minimally self-hosted [IndieAuth](https://indieweb.org/IndieAuth) client. This functions as both a native [IndieAuth](https://indieauth.net/) ([spec](https://indieauth.spec.indieweb.org/)) client and a generic [OAuth2.0](https://www.oauth.com/) ([Auth0.com](https://auth0.com/docs)) client.

## Security Note

This module ***REQUIRES*** Apache to have write access to `indieauth-client-php/.htaccess`
to facilitate multiple unknown IdPs. **ENSURE** that you do not point any other endpoints
towards this file or allow input that can result in this file being modified elsewhere.
Access outside of this module poses a **SECURITY RISK** that can compromise your system.

This file *can and should* be periodically cleared (empty contents) to help the
integrity of your system (not grow exponentially in size).

## Setup

1. Clone to `/usr/local/src/`
1. Run `dependencies.bash` to install dependent Ubuntu packages (like Apache HTTPd and PHP).
1. Run `setup.bash` to setup required directories and files.
1. Add configuration to your Apache HTTPd configuration (example found [indieauth-client-php.conf.example](indieauth-client-php.conf.example)) replacing `<client>`, and `<your host>` throughout
    ```
    <FilesMatch ^/usr/local/src/mindie-client/indieauth-client-php/(index|login|redirect).php$ >
	    AuthType None
	    <RequireAll>
		    Require all granted
	    </RequireAll>
    </FilesMatch>
    <Files /usr/local/src/mindie-client/client_id.json.php >
	    AuthType None
	    <RequireAll>
		    Require all granted
	    </RequireAll>
    </Files>

    AliasMatch ^/<client>/index$ /usr/local/src/mindie-client/indieauth-client-php/index.php
    AliasMatch ^/<client>/login$ /usr/local/src/mindie-client/indieauth-client-php/login.php
    AliasMatch ^/<client>/redirect$ /usr/local/src/mindie-client/indieauth-client-php/redirect.php
    AliasMatch ^/<client>/oauth-client-server$ /usr/local/src/mindie-client/client_id.json.php
    <Location /<client>/>
	    SetEnv CLIENT_PATH <client>
	    AuthType oauth2
	    AuthName "Hello OAuth"
	    # set the scopes to request
	    SetEnv CLIENT_SCOPE "profile oauth"
	    ErrorDocument 401 /<client>/index
	    OAuth2AcceptTokenIn header
	    OAuth2AcceptTokenIn cookie name=oauth_token
	    <RequireAll>
		    Require valid-user
	    </RequireAll>
    </Location>
    ```
1. Run `new-client.bash </filesystem/path/to/client/>` to create `.htaccess` file and add the output configuration to your Apache HTTPd configuration
    ```
    <Directory /filesystem/path/to/client/>
	    AllowOverride AuthConfig
    </Directory>
    SetEnv CLIENT_FILESYSTEM_PATH /filesystem/path/to/client/
    ```

This will setup the following endpoints on your Apache server:
- `https://example.com/<client>/index`
- `https://example.com/<client>/login`
- `https://example.com/<client>/redirect`
- `https://example.com/<client>/oauth-client-server`

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
- `SetEnv CLIENT_SCOPE "profile oauth"` - *optional* to set the scopes that will be requested
- `SetEnv CLIENT_FILESYSTEM_PATH /filesystem/path/to/client/` - so that the `.htaccess` can be updated appropriately (note that if the client does not reside on the filesystem, then this should be set to `/usr/local/src/mindie-client/indieauth-client-php/` due to the aliases that are required)
- `SetEnv CLIENT_HOME <path/to/homepage>"` - *optional* path (relative to `CLIENT_PATH`) for the client's public webpage
- `SetEnv CLIENT_LOGO <path/to/logo>"` - *optional* path (relative to `CLIENT_PATH`) for the client's public logo image
- `SetEnv CLIENT_NAME <human friendly>"` - *optional* human friendly name for the IdP to display
- `SetEnv CLIENT_TOS <path/to/tos>"` - *optional* path (relative to `CLIENT_PATH`) for the client's terms of service
- `SetEnv CLIENT_POLICY <path/to/policy>"` - *optional* path (relative to `CLIENT_PATH`) for the client's privacy policy document

### Session Variables

For your use on your PHP endpoints.

- `$_SESSION['user']`
- `$_SESSION['token']`
- `$_SESSION['scope']`
- `$_SESSION['indieauth_metadata']` (endpoint)
- `$_SESSION['indieauth_issuer']` (endpoint)

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

