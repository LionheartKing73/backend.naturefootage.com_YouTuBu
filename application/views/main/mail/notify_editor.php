<html>
  <head>
    <style>
      body, table {font-family:tahoma,verdana; font-size: 8pt}
      table td, table th { padding: 3px 10px 2px 0; text-align: left; vertical-align: top }
    </style>
  </head>
  <body>
    Dear <?= $fname . ' ' . $lname ?>.<br><br>
    Thank you for registering on our website.
    <br><br>
    Your account is active now.
    Please fee free to upload your content into the library using details below.
    <br><br>
    <table border="0" cellspacing="0">
      <tr><th>Username: </th><td><?= $login ?></td></tr>
      <tr><th>Password:</th><td><?= $password ?></td></tr>
    </table>
    <br><br>
    If you have any queries, please do not hesitate to contact us by e-mail at
    <a href="mailto:<?= $this->config->item('vendor_email') ?>"><?= $this->config->item('vendor_email') ?></a>, or by phone
    on <?= $this->config->item('vendor_phone') ?>.
    <br><br>
    Kind regards,<br>
    <?= $this->config->item('title') ?> Library team
  </body>
</html>