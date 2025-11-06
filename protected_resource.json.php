<?php
header('Content-type: application/json');
$issuer = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'];
$client_path = preg_replace('/^\/.well-known\/oauth-protected-resource/', '', $_SERVER['REQUEST_URI']);
$client_uri = $client_path . '/' . getenv('CLIENT_HOME');
$client_tos = $client_path . '/' . getenv('CLIENT_TOS');
$client_policy = $client_path . '/' . getenv('CLIENT_POLICY');
$client_name = getenv('CLIENT_NAME');
$client_scope = getenv('CLIENT_SCOPE');
// see the spec https://datatracker.ietf.org/doc/html/draft-ietf-oauth-resource-metadata
$meta = [
	"issuer" => $issuer,
	"resource" => "$issuer$client_path/oauth-client-server",
	"resource_uri" => !empty(getenv('CLIENT_HOME')) ? "$issuer$client_uri" : null,
	"resource_name" => !empty(getenv('CLIENT_NAME')) ? "$client_name" : null,
	"resource_documentation" => "https://github.com/carrvo/mindie-client",
	"resource_tos_uri" => !empty(getenv('CLIENT_TOS')) ? "$issuer$client_tos" : null,
	"resource_policy_uri" => !empty(getenv('CLIENT_POLICY')) ? "$issuer$client_policy" : null,
	"scopes_supported" => !empty(getenv('CLIENT_SCOPE')) ? "$client_scope" : null,
	"bearer_methods_supported" => ["header", "cookie"],
];
exit(json_encode(array_filter($meta)));
?>
