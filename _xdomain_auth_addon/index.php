<?php
$KEYFILE = "./_gen_KEYPAIR.php";
$TRUSTED_HOST = [$_SERVER["HTTP_HOST"], 'EDIT ME']; //put here your whitelist for redirection (useful to protect privacy, to ensure an unknown host cannot trick you into revealing name and email)
$SALTLEN = 32; //in Bytes, must be even 32B = 256b

//MUST BE HTTPS !!!! (otherwise it defeats the whole security purpose)
if ($_SERVER["HTTPS"] != "on") {
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    die();
}

require_once($KEYFILE);

//NO-TOKEN = give public-key, BAD-TOKEN = DIE
if (!isset($_GET['t'])) {
    print($PUBLICPEM);
    die();
} elseif (!(ctype_alnum($_GET['t']) && strlen($_GET['t']) === $SALTLEN)) {
    die("BAD TOKEN");
}

//utility functions to transmit strings over GET params
function urlb64_enc($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
function urlb64_dec($data)
{
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

//THIS GOES THROUGH ALL WP-ADMIN INIT, and checks for credentials
require_once __DIR__ . '/../admin.php';
//if we are here, we are authentified on WP

//GENERATE THE DOCUMENT TO SIGN
require_once __DIR__ . '/../../wp-includes/user.php';
$wpuser = wp_get_current_user();
$SALT = bin2hex(random_bytes(ceil($SALTLEN / 2)));
$jsonToken = json_encode(array("user" => array("id" => $wpuser->user_login, "email" => $wpuser->user_email), "salt" => $SALT, "request" => $_GET['t']));

//SIGN IT
openssl_sign($jsonToken, $signature, $PRIVATEPEM, OPENSSL_ALGO_SHA256) or die("ERROR on signature");

//CHECK THE REDIRECT URL
if (!isset($_GET['r'])) { //is it provided ?
    die("Missing callback URL");
}
$redir = urlb64_dec($_GET['r']);
$host = parse_url($redir)['host'];
if (!in_array($host, $TRUSTED_HOST, true)) { // is it allowed ?
    die("FORBIDDEN REDIRECT : $host is not in the allowed hosts");
}

//FINALLY REDIRECT
header('Location: ' . $redir . '?j=' . urlb64_enc($jsonToken) . "&s=" . urlb64_enc($signature));
