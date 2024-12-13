<?php
header('Content-type: application/json');
$issuer = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'];
$client_id = $_SERVER['REQUEST_URI'];
$client_uri = getenv('CLIENT_PATH') . '/' . getenv('CLIENT_HOME');
$client_logo = getenv('CLIENT_PATH') . '/' . getenv('CLIENT_LOGO');
$client_tos = getenv('CLIENT_PATH') . '/' . getenv('CLIENT_TOS');
$client_policy = getenv('CLIENT_PATH') . '/' . getenv('CLIENT_POLICY');
$meta = [
	"issuer" => $issuer,
	"application_type" => "web",
	"client_id" => "$issuer$client_id",
	"client_uri" => "$issuer$client_uri",
	"client_logo" => "$issuer$client_logo",
	"client_name" => getenv('CLIENT_NAME'),
	"client_logo" => "$issuer$client_tos",
	"client_logo" => "$issuer$client_policy",
	"scope" => getenv('CLIENT_SCOPE'),
	"response_types" => ["code"],
	"grant_types" => ["authorization_code"],
	"token_endpoint_auth_method" => "client_secret_basic",
	"introspection_endpoint_auth_method" => "client_secret_basic",
	"client_secret" => "_",
	"client_secret_expires_at" => 0,
];
exit(json_encode($meta));
?>
