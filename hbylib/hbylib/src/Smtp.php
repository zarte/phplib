<?php
/**
 * 邮件类
 * Created by PhpStorm
 * User: zjh
 * Date: 2018/9/5 0005
 * Version: 1.0
 */
namespace Hbylib\Hbylib;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class Smtp
{

    private $conf = array(
        'host'=> 'smtp1.example.com;smtp2.example.com',
        'Port'=> 587,
        'Username'=> 'user@example.com',
        'Password'=>''
    );

    private $mail = '';

    public function __construct($conf=null)
    {
        $this->conf = array_merge($this->conf,$conf);
        $this->mail = new PHPMailer(true);
    }

    public function addAttachment($file,$filename=''){
        //Attachments
        $this->mail->addAttachment($file,$filename);         // Add attachments

    }
    public function addContent($title,$content,$AltBody=''){
        //Content
        $this->mail->isHTML(true);                                  // Set email format to HTML
        $this->mail->Subject =$title;
        $this->mail->Body    =$content;
        $this->mail->AltBody = $AltBody;

    }
    public function addCopyUser($email=null){
        if(!$email)return;
        $this->mail->addCC($email);
    }
    public function addToUser($email=null){
        if(!$email)return;
        $this->mail->addAddress($email);
    }
    public function send($from,$to='',$reply=''){

            //Server settings
         //   $this->mail->SMTPDebug = 2;                                 // Enable verbose debug output
            $this->mail->SMTPDebug = 0;
            $this->mail->isSMTP();                                      // Set mailer to use SMTP
            $this->mail->Host = $this->conf['host'];
            $this->mail->SMTPAuth = 'login';                               // Enable SMTP authentication
            $this->mail->Username = $this->conf['Username'];
            $this->mail->Password =  $this->conf['Password'];                         // SMTP password
            $this->mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
            $this->mail->Port = $this->conf['Port'];

            //Recipients
           // $this->mail->setFrom('from@example.com', 'Mailer');
            $this->mail->setFrom($from);
            if($to){
                $this->mail->addAddress($to);     // Add a recipient
            }
            if($reply){
                $this->mail->addReplyTo($reply);
            }

           $res= $this->mail->send();

            return $res;
         //   echo 'Message could not be sent. Mailer Error: ', $this->mail->ErrorInfo;

    }
}