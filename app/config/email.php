<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

// SMTP / Mailer configuration
return [
    'driver'         => 'smtp', // smtp | sendmail | mail
    'smtp_host'      => 'smtp.gmail.com',
    'smtp_port'      => 587,
    'smtp_secure'    => 'tls', // tls | ssl | ''
    'smtp_user'      => 'donaldlumio1@gmail.com',    // your Gmail address (or SMTP username)
    'smtp_pass'      => 'gdlm ysve mqsj tcmf',    // Gmail App Password (recommended)
    'from_address'   => 'donaldlumio1@gmail.com',
    'from_name'      => 'AutoTrack CarRental',
    'reply_to'       => '',
    'debug'          => true, // true to enable verbose output (development only)
];
