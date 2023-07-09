<?php
include './auth.inc.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>WP XAUTH DEMO</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
</head>

<body>
  <main style="width:80vw;padding:10px;margin:auto">
    <h1>WP XAUTH DEMO</h1>
    Open <code>Devtools>Network</code> to see how it works and how much time it took.
    <?php
    print("<h3>YOU ARE AUTHED !</h3>");
    print("<h4>Session Data : </h4><pre><code>" . print_r($_SESSION, true) . "</pre></code>");
    ?>
    <a href="./" role="button" class="outline">Reload (verify SESSION)</a>
    <a href="./reset.php" role="button" class="outline">Reset Session (verify WP)</a>
    <a href="./reset.php?full" role="button" class="outline">Reset Session and delete PEM</a>
  </main>
</body>

</html>