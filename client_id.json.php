<?php
header('Content-type: application/json');
$issuer = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'];
$client_id = $_SERVER['REQUEST_URI'];
$client_uri = getenv('CLIENT_PATH') . '/' . getenv('CLIENT_HOME');
$client_logo = getenv('CLIENT_PATH') . '/' . getenv('CLIENT_LOGO');
$client_tos = getenv('CLIENT_PATH') . '/' . getenv('CLIENT_TOS');
$client_policy = getenv('CLIENT_PATH') . '/' . getenv('CLIENT_POLICY');
$client_name = getenv('CLIENT_NAME');
$client_scope = getenv('CLIENT_SCOPE');
$meta = [
	"issuer" => $issuer,
	"application_type" => "web",
	"client_id" => "$issuer$client_id",
	"client_uri" => !empty(getenv('CLIENT_HOME')) ? "$issuer$client_uri" : null,
	"client_logo" => !empty(getenv('CLIENT_LOGO')) ? "$issuer$client_logo" : null,
	"client_name" => !empty(getenv('CLIENT_NAME')) ? "$client_name" : null,
	"client_tos" => !empty(getenv('CLIENT_TOS')) ? "$issuer$client_tos" : null,
	"client_policy" => !empty(getenv('CLIENT_POLICY')) ? "$issuer$client_policy" : null,
	"scope" => !empty(getenv('CLIENT_SCOPE')) ? "$client_scope" : null,
	"response_types" => ["code"],
	"grant_types" => ["authorization_code"],
	"token_endpoint_auth_method" => "client_secret_basic",
	"introspection_endpoint_auth_method" => "client_secret_basic",
	"client_secret" => "_",
	"client_secret_expires_at" => 0,
];
exit(json_encode(array_filter($meta)));
?>
