<?php


namespace Server\Api\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Server\Core\YamlParser;

class MailingService
{
    private PHPMailer $phpMailer;
    private YamlParser $yamlParser;
    private string $mailHost;
    private string $mailUsername;
    private string $mailPassword;
    private int $mailPort;

    public function __construct()
    {
        $this->phpMailer = new PHPMailer(true);
        $this->yamlParser = new YamlParser();

        $mailConfigVariables = $this->yamlParser->parseIt("./config/mail.yml");

        $this->mailHost = $mailConfigVariables['mail_host'];
        $this->mailUsername = $mailConfigVariables['mail_username'];
        $this->mailPassword = $mailConfigVariables['mail_password'];
        $this->mailPort = $mailConfigVariables['mail_port'];
    }

    private function configureService ()
    {
        $this->phpMailer->SMTPDebug = 0;
        $this->phpMailer->isSMTP();
        $this->phpMailer->SMTPAuth = true;
        $this->phpMailer->Host =$this->mailHost;
        $this->phpMailer->Username = $this->mailUsername;
        $this->phpMailer->Password = $this->mailPassword;
        $this->phpMailer->Port = $this->mailPort;
        $this->phpMailer->SMTPSecure = "tls";
    }

    public function sendMail (string $to, string $name, string $htmlContent, $files = array()) : bool
    {
        try {
            $this->phpMailer->setFrom($this->mailUsername, "Diary APP Development Team");
            $this->phpMailer->addAddress($to, $name);

            $this->phpMailer->isHTML(true);
            $this->phpMailer->Subject = "Email Verification";
            $this->phpMailer->Body = $htmlContent;
            return $this->phpMailer->send();
        } catch (Exception $e) {
            return false;
        }
    }
}