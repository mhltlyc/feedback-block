<?php

include 'phpMailer.class.php';

/**
 * 发送邮件
 * 
 * @param string $address 接收方邮箱地址
 * @param string $subject
 * @param string $body
 * @link https://github.com/Synchro/PHPMailer
 */
function sendMail($address, $subject, $body) {
    ignore_user_abort();
    date_default_timezone_set('PRC');    //其中PRC为“中华人民共和国”
    $mail = new PHPMailer;
    $mail->IsSMTP();       // Set mailer to use SMTP
    $mail->Charset = 'utf-8';
    $mail->Encoding = 'base64';
    $this->Mailer = 'SMTP';
    $mail->IsHTML(true);
    $mail->Host = 'smtp.qq.com';  // Specify main and backup server
    $mail->Port = 25;
    $mail->SMTPAuth = TRUE;       // Enable SMTP authentication
    $mail->Username = 'mhltlyc';       // SMTP username
    $mail->Password = maolyc999; // SMTP password
    $mail->SMTPSecure = '';
    // $mail->SMTPDebug = TRUE;   //需要时开启调试
    $mail->From = 'unary';
    $mail->FromName = "=?utf-8?B?" . base64_encode($this->getConfig('mail_from_name')) . "?=";
    $mail->AddReplyTo('unary');
    $mail->AddAddress($address);     // Add a recipient
    $mail->Subject = "=?utf-8?B?" . base64_encode($subject) . "?="; //修正邮件主题乱码问题
    $mail->WordWrap = 80; // set word wrap
    $mail->Body = $body;
    //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
    if (!$mail->Send()) {
        echo $mail->ErrorInfo;
        return FALSE;
    }
    return TRUE;
}

?>
