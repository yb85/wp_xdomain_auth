<?php
//MUST BE HTTPS !!!! (otherwise it defeats the whole security purpose)
if ($_SERVER["HTTPS"] != "on") {
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    die();
}

$KEYFILE = "./_gen_KEYPAIR.php";

//ABORT IF KEY FILE ALREADY EXISTS
if (is_file($KEYFILE)) {
    print("ERROR - KEY FILE EXISTS ... *cowardly refusing to create a new one*");
    die();
}

//create new private and public keypair object
$keypair = openssl_pkey_new(array(
    "private_key_bits" => 2048,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
));

//EXTRACT PEMs from keypair object
$PUBLICPEM = openssl_pkey_get_details($keypair)['key'];
openssl_pkey_export($keypair, $PRIVATEPEM);

//STORE PEM IN PHP FILE : the goal is to avoid unauthorized access. 
//If you know how to properly setup .htaccess you can store them in PEM files, or even better outside the server ROOT
//
// Using PHP makes it a bit less secure but it is 0-config

$KEY_fp = fopen($KEYFILE, "w") or die("Unable to open file!");
$timestamp = date('d-m-y_h:i:s');
fwrite($KEY_fp, "<?php /* $timestamp */\n\$PRIVATEPEM='$PRIVATEPEM';\n\$PUBLICPEM='$PUBLICPEM';\n?>") or die("ERROR no file generated");
fclose($KEY_fp);

print("<h3>SUCCESS !</h3>Here is the PUBLIC PEM<pre><code>$PUBLICPEM<code></pre>");
