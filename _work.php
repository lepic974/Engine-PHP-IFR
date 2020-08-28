<?php
$to      = 'christophe.thibault@gmail.com';
$subject = 're pour voir';
$message = 'Bonjour !';
$headers = 'From: site@restaurant-peron.com' . "\r\n" .
    'Reply-To: site@restaurant-peron.com' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

mail($to, $subject, $message, $headers);
?>
