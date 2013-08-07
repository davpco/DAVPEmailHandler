<?php

//SMTP Server Configuration

$smtpConfig['host'] = "locahost";
$smtpConfig['username'] = "your@email.com";
$smtpConfig['password'] = "secret";
$smtpConfig['encription'] = "tls";
$smtpConfig['wordwrap'] = 50;
$smtpConfig['ishtml'] = true;

//Akistmet API Configuration

$akismetConfig['api_key'] = ""; //Your akismet API key
$akismetConfig['blog'] = ""; //Website used to setup akismet account

//Honeypot Configuration

$honeypotConfig['enabled'] = true;
$honeypotConfig['default_fieldname'] = ""; //If set then Field Names setting will be ignored
$honeypotConfig['fieldnames'] = array('website', 'blog', 'address', 'phone'); //It will generate a honeypot field using one of the field names randomly

?>