# MIndie Client

Built upon [IndieAuth Client](indieauth-client-php/README.md) ([source](https://github.com/indieweb/indieauth-client-php)) to give a minimally self-hosted [IndieAuth](https://indieweb.org/IndieAuth) client. This functions as both a native [IndieAuth](https://indieauth.net/) ([spec](https://indieauth.spec.indieweb.org/)) client and a generic [OAuth2.0](https://www.oauth.com/) ([Auth0.com](https://auth0.com/docs)) client.

This client is compatible with, and requires, [mod_oauth2](https://github.com/OpenIDC/mod_oauth2). See the Setup section for how to configure compatibility.

This provides the Client component of [MIndie](https://github.com/carrvo/mindie).

## Security Note

This module ***REQUIRES*** Apache to have write access to `indieauth-client-php/.htaccess`
to facilitate multiple unknown IdPs. **ENSURE** that you do not point any other endpoints
towards this file or allow input that can result in this file being modified elsewhere.
Access outside of this module poses a **SECURITY RISK** that can compromise your system.

This file *can and should* be periodically cleared (empty contents) to help the
integrity of your system (not grow exponentially in size).

## Setup

1. Clone
1. Run `debian-package-dependencies` to install dependent *build* Debian packages
1. Run `make debian-package` to build package locally
1. Run `dpkg -i package/mindie-idp_X.X.X_all.deb` to install package locally
1. Modify the configuration for your Apache HTTPd configuration (installed to `/etc/apache2/conf-available/indieauth-client-php.conf`) replacing `<client>` (installed with `oauth`) throughout
    ```
    AliasMatch ^/<client>/index$ /usr/src/mindie-client/indieauth-client-php/index.php
    AliasMatch ^/<client>/login$ /usr/src/mindie-client/indieauth-client-php/login.php
    AliasMatch ^/<client>/redirect$ /usr/src/mindie-client/indieauth-client-php/redirect.php
    AliasMatch ^/<client>/oauth-client-server$ /usr/src/mindie-client/client_id.json.php
    AliasMatch ^/.well-known/oauth-protected-resource/<client>$ /usr/src/mindie-client/protected_resource.json.php
    <Location /<client>/>
	    SetEnv CLIENT_PATH /<client>
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
1. Run `new-mindie-client </filesystem/path/to/client/>` to create `.htaccess` file and add the output configuration to your Apache HTTPd configuration
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

### Support of Multiple Authentication

The [HTTP spec](https://datatracker.ietf.org/doc/html/rfc7235#section-4.1)
does not restrict to only one `AuthType`; yet Apache HTTPd does.
However, the workaround is to specify the `WWW-Authenticate` header during the 401 handler.
To facilitate this, it is useful to set `AuthType none` within Apache HTTPd so that it does not interfere
(not required; but if you set `AuthType Basic` *and* have it in the challenge,
then this will cause a second prompt by the browser when cancel is clicked).

The workaround can be configured with
```
<Location /<client>/>
    SetEnv AUTH_CHALLENGES "Bearer realm=\"my realm\", Basic realm=\"my realm\""
</Location>
```

You can support other Authentication schemes with a setup similar to
```
<Location /<client>/>
	<If "%{HTTP:Authorization} =~ /^Basic/">
		AuthType Basic
		Require valid-user
	</If>
	<ElseIf "%{HTTP:Authorization} =~ /^Bearer/">
		AuthType oauth2
		Require valid-user
	</ElseIf>
	<Else>
		# let the ErrorDocument 401 give the AuthType Basic to avoid the double-prompt
		AuthType none
	</Else>
</Location>
```

### Insecure Configuration

If you are choosing to use this isolated from the internet on your homenet, you **MAY** make the additional modification to the [Client.php](https://github.com/indieweb/indieauth-client-php/blob/main/src/IndieAuth/Client.php#L229) to allow the insecure `HTTP`. This is **not required** if you are using a self-signed certificate--but be warned that your browser will complain until you accept the risk.

```diff
/usr/src/mindie-client/vendor/indieauth/client/src/IndieAuth/Client.php:229
-    if (!array_key_exists('scheme', $parts) || $parts['scheme'] != 'https') {
+    if (!array_key_exists('scheme', $parts) || ($parts['scheme'] != 'https' && $parts['scheme'] != 'http')) {
```

### Potential Issue - Mismatched Issuer

If there are complaints that the issuer does not match, this could be because of the presence or absence of a trailing slash (`/`) in your metadata endpoint. To resolve this, you *MAY* make the additional modification to the [Client.php](https://github.com/indieweb/indieauth-client-php/blob/main/src/IndieAuth/Client.php#L534) to allow the insecure `HTTP`.

```diff
/usr/src/mindie-client/vendor/indieauth/client/src/IndieAuth/Client.php:534
-    if ($params['iss'] !== $expected_issuer) {
+    if (self::normalizeMeURL($params['iss']) !== self::normalizeMeURL($expected_issuer)) {
```

### Potential Issue - Required Endpoints give Forbidden (403)

If the required endpoints are returning `403 Forbidden` then there is likely another part of your configuration denying access to the webspace. Simply grant access in webspace in addition to filesystem space.

```
    <LocationMatch ^/<client>/(index|login|redirect|oauth-client-server)$ >
	    AuthType None
	    <RequireAll>
		    Require all granted
	    </RequireAll>
    </LocationMatch>
```

### Protected Resource AND Client ID

Technically, the use of [mod_oauth2](https://github.com/OpenIDC/mod_oauth2)
defines it as a [protected resource](https://datatracker.ietf.org/doc/html/draft-ietf-oauth-resource-metadata)
**AND** the use of [IndieAuth Client](https://github.com/indieweb/indieauth-client-php)
defines it as an [OAuth client](https://datatracker.ietf.org/doc/html/draft-ietf-oauth-client-id-metadata-document/); the third piece being an authorization server between them
(see [MIndie-IDP](https://github.com/carrvo/mindie-idp) as an example).
As such, it is an OAuth client who is accessing *itself* as the resource,
regardless of what document is behind the authorization layer.
This is an abnormal (and not intended by the specs) situation.
However, compatibility with [OpenID Connect](https://github.com/OpenIDC/mod_auth_openidc) was not achieved
([mod_oauth2](https://github.com/OpenIDC/mod_oauth2) was achieved);
thus the project was developed and shall remain for the time being.

For this to function properly, the following **SHOULD** be added to the client
```diff
/usr/src/mindie-client/vendor/indieauth/client/src/IndieAuth/Client.php:83
      'scope' => $scope,
+      'resource' => self::$clientID,
/usr/src/mindie-client/vendor/indieauth/client/src/IndieAuth/Client.php:124
        'code_verifier' => $_SESSION['indieauth_code_verifier'],,
+        'resource' => self::$clientID,
/usr/src/mindie-client/vendor/indieauth/client/src/IndieAuth/Client.php:131
        'code_verifier' => $_SESSION['indieauth_code_verifier'],
+        'resource' => self::$clientID,
```

Key environment variables (`CLIENT_*`) configured under `<Location /<client>/>`
**SHOULD** also be configured under `<Location /.well-known/oauth-protected-resource/<client>/>`
as a result of this duality.

### Environment Variables

Set these in Apache HTTPd config.

- `SetEnv CLIENT_PATH /<client>` - for your client ID to be `https://example.com/<client>/`
- `SetEnv CLIENT_SCOPE "profile oauth"` - *optional* to set the scopes that will be requested
- `SetEnv CLIENT_FILESYSTEM_PATH /filesystem/path/to/client/` - so that the `.htaccess` can be updated appropriately (note that if the client does not reside on the filesystem, then this should be set to `/usr/local/src/mindie-client/indieauth-client-php/` due to the aliases that are required)
- `SetEnv CLIENT_HOME <path/to/homepage>"` - *optional* path (relative to `CLIENT_PATH`) for the client's public webpage
- `SetEnv CLIENT_LOGO <path/to/logo>"` - *optional* path (relative to `CLIENT_PATH`) for the client's public logo image
- `SetEnv CLIENT_NAME <human friendly>"` - *optional* human friendly name for the IdP to display
- `SetEnv CLIENT_TOS <path/to/tos>"` - *optional* path (relative to `CLIENT_PATH`) for the client's terms of service
- `SetEnv CLIENT_POLICY <path/to/policy>"` - *optional* path (relative to `CLIENT_PATH`) for the client's privacy policy document
- `SetEnv CLIENT_ANONYMOUS https://example.com/user/anonymous` - *optional* user URI for supporting an additional "Stay Anonymous" login (they *WILL* be able to fill in their own URI) **that will be in effect for all endpoints of the client it is configured on** (this is due to the need to set this value during `/<client>/index` request or redirect during `ErrorDocument`)
- `SetEnv CLIENT_AUTO_ANONYMOUS https://example.com/user/anonymous` - *optional* user URI for supporting an anonymous login (they *WILL* ***NOT*** be able to fill in their own URI) **that will be in effect for all endpoints of the client it is configured on** (this is due to the need to set this value during `/<client>/index` request or redirect during `ErrorDocument`)
- `SetEnv AUTH_CHALLENGES "Bearer realm=\"my realm\", Basic realm=\"my realm\""` - *optional* for `index` to send its own `WWW-Authenticate` header with the provided challenge (useful for supporting *both* OAuth, this client, *and* Basic or another Authentication)

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

### Alternative

1. Navigate to `https://example.com/<client>/some/public/path`
1. Click on a `<a href="https://example.com/<client>/index">Login</a>`
1. On the login page, enter your profile URI and click `Log In`
1. Authenticate and Authorize (unselect scopes that you wish to deny)
1. Be redirected back to `https://example.com/<client>/some/public/path`

## License

Copyright 2024 by carrvo

Available under the MIT license.

See [MIT-LICENSE.md](MIT-LICENSE.md) for the text of this license.

