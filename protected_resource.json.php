<?php
header('Content-type: application/json');
$issuer = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'];
$client_path = preg_replace('/^\/.well-known\/oauth-protected-resource/', '', $_SERVER['REQUEST_URI']);
$client_uri = $client_path . '/' . getenv('CLIENT_HOME');
$client_tos = $client_path . '/' . getenv('CLIENT_TOS');
$client_policy = $client_path . '/' . getenv('CLIENT_POLICY');
$client_name = getenv('CLIENT_NAME');

$client_scope = getenv('CLIENT_SCOPE');
$client_scope = "$client_scope";
if (str_contains($client_scope, "indieauth") !== true) {
    $client_scope = "indieauth $client_scope";
}
if (str_contains($client_scope, "profile") !== true) {
    $client_scope = "profile $client_scope";
}
$client_scope = trim($client_scope);

$client_scope_descriptions_raw = getenv('CLIENT_SCOPE_DESCRIPTIONS');
$client_scope_descriptions = json_decode($client_scope_descriptions_raw, associative: true);
if (isset($client_scope_descriptions) !== true) {
    $client_scope_descriptions = array();
    if (!empty($client_scope_descriptions_raw)) {
        $path = $_SERVER['REQUEST_URI'];
        error_log("CLIENT_SCOPE_DESCRIPTIONS for $issuer$path is invalid json: $client_scope_descriptions_raw");
    }
}
if (array_key_exists("profile", $client_scope_descriptions) === false) {
    // description taken from https://indieauth.spec.indieweb.org/#profile-information
    // further inspiration: https://openid.net/specs/openid-connect-core-1_0.html#ScopeClaims
    $client_scope_descriptions["profile"] = "Request access to the user's default profile information which include the following properties: name, photo, url.";
}
if (array_key_exists("indieauth", $client_scope_descriptions) === false) {
    // description taken from https://auth0.com/docs/get-started/apis/scopes/openid-connect-scopes
    $client_scope_descriptions["indieauth"] = "Indicate that the application intends to use IndieAuth to verify the user’s identity.";
}
// see the spec https://datatracker.ietf.org/doc/html/draft-ietf-oauth-resource-metadata
$meta = [
	"issuer" => $issuer,
	"resource" => "$issuer$client_path/oauth-client-server",
	"resource_uri" => !empty(getenv('CLIENT_HOME')) ? "$issuer$client_uri" : null,
	"resource_name" => !empty(getenv('CLIENT_NAME')) ? "$client_name" : null,
	"resource_documentation" => "https://github.com/carrvo/mindie-client",
	"resource_tos_uri" => !empty(getenv('CLIENT_TOS')) ? "$issuer$client_tos" : null,
	"resource_policy_uri" => !empty(getenv('CLIENT_POLICY')) ? "$issuer$client_policy" : null,
	"scopes_supported" => $client_scope,
	"scope_descriptions" => $client_scope_descriptions,
	"bearer_methods_supported" => ["header", "cookie"],
];
exit(json_encode(array_filter($meta)));
?>
