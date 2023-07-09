# Cross-domain authentification with Wordpress

Reasonnably secure cross-domain authentication using a WP user database.

**TL;DR; : A PHP server can manage its login mechanism (`$_SESSION`) with the user database of a remote Wordpress install**

It is almost always a bad idea to self-implement cryptographic algorithms, nevertheless this code provides a plug-and-play solution for <u>non-critical</u> applications (the code has not been  audited).  **NO GUARANTEE GIVEN, USE AT YOUR OWN RISK**

**Benefits :**

- You can have PHP authentication without the curation of another user database (LDAP server, SQL, ...)
- You can use your WP install as a single source of truth, and administer it with Wordpress admin dashboard
- There exists also WP plugins to enable Oauth on WP : using them you can login with GMAIL, ….

**Use-case :** A small team with a Wordpress blog want to regulate access to a set of homemade PHP utilities

## Install

Say the WP host is `my-blog.wp-example.com` and the PHP foreign server host is `my-app.php-example.com`

1. **[On `my-blog.wp-example.com`]** Put the `_xdomain_auth_addon` folder in the `wp-admin` folder on your WP installation, and set the list of trusted hosts `$TRUSTED_HOST` in `_xdomain_auth_addon/index.php` to avoid being tricked into leaking the user name and emails

   - The trusted hosts are the full domain names where you want to use the cross-domain authentication (e.g. `my-php-app.php-example.com`)
   - if you need more granularity you can edit the validation code, it is not too difficult
   - If you don't need to know who is login in, but only if this person is a WP user, you can remove the username and the email from the exchanged document. In that case no personnal information about the user is given to the PHP server

2. Make a request to `https://my-blog.wp-example.com/wp-admin/_xdomain_auth_addon/init.php`. You may delete afterwards this `init.php` file (you can keep it also as it is handy to regenerate a new keypair. **Make sure the generated PHP file containing the keys (`_wp_xdomain_auth/_gen_KEYPAIR.php`) is interpreted by the server !**

3. [On `my-app.php-example.com`] Copy the `protected_by_xauth` folder to the PHP server on which you want to use the WP auth mecanism, and set addon endpoint correctly (e.g. : `https://my-blog.wp-example.com/wp-admin/_xdomain_auth_addon`). 
   **PHP authentication does <u>not</u> protect static assets ! It just enable SESSION credentials setting/checking within PHP code**

   - To protect static assets you must configure your web-server access directives
   - A solution  : use `.htaccess` files (or similar) with MOD-REWRITE to send any request for files in `protected_by_xauth` through a PHP proxy script which checks the credentials and send the file (`readfile()` + setting proper MIME-type in the HTTP headers)
     - using a PHP proxy is fairly slow and suboptimal, do not use this solution for large/professional applications

4. ENJOY. 

   **NO GUARANTEE GIVEN, USE AT YOUR OWN RISK**

## How it works

**In a nutshell, the client presents a  document signed by the WP server to the foreign PHP server which verifies the authenticity off the signature with the WP server Public-key (no shared secret)**.

- HTTPS is enforced for all requests. Having this pre-existing these trusted and secure channels implies we don’t have to further encrypt data exchanged between any party.
- The client is redirected by the foreign PHP server  (code 302) to the WP server (more precisely to the addon you put in your WP install)
- If not authenticated on the WP server, the client authentifies himself 
- If/once the client is authenticated on WP, a document is generated on the WP server with a token from the client, the WP user info (name and email), and a random salt. The WP server signs the document with its Private Key (RSA/SHA256)  and sends it to the client as a GET parameter in a redirect  to the <u>whitelisted</u> foreign PHP server (code 302)
- The foreign PHP server receives from the client the document, and the corresponding signature. He can verify with the WP server public key the signature, which is proof the client is a WP user
- If the foreign PHP server does not have the public key, he can ask it directly over HTTPS to the WP server.
- If the signature is verified, the foreign PHP server can open a session with the name and email contained in the document
