<?php

/**
 * Mailer interface.
 *
 * @version 1.0.0
 *
 * @internal
 */
class Mailer
{
    /**
     * @var string Subject of the mail
     */
    private $subject;

    /**
     * @var string Mail address of the sender
     */
    private $sender;

    /**
     * @var string Mail address(es) to send the mail
     */
    private $receivers;

    /**
     * @var string Blind Carbon Copy
     */
    private $bcc;

    /**
     * @var string Composed message to send
     */
    private $message;

    /**
     * @var boolean Mailer is valid
     */
    private $isValid;

    /**
     * Initializes a Mailer object with provided informations.
     *
     * @param string	$receivers  List of mail receiver(s) separated with coma : SomeOne <someone@example.com>, SomeOneElse <someoneelse@example.com>
     * @param string	$subject    Subject of the mail
     * @param string	$message    Content of the mail
     * @param string	$sender     Optionnal parameter : Adress of the sender (default value readed from configuration)
     * @param string	$bcc        Optionnal parameter : Adress of the blind carbon copy receivers (default value : null)
     */
    public function __construct($receivers, $subject, $message, $sender = null, $bcc = null)
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Configuration.php';
        $configuration = new Configuration();
        if ($configuration->get('mailer') !== '1') {
            $this->isValid = false;
            //mailer is inactive
            return;
        }
        if (is_null($sender) && !$this->sender = $configuration->get('mailSender')) {
            $this->isValid = false;
            //sender is not set
            return;
        }
        $this->isValid = true;
        if (!is_null($sender)) {
            //override sender with provided one
            $this->sender = $sender;
        }
        $this->receivers = $receivers;
        $this->subject = $subject;
        $this->message = $message;
        $crlf = "\r\n";
        $this->headers = "From: $this->sender$crlf";
        if (isset($this->bcc)) {
            $this->headers .= "Bcc: $this->bcc$crlf";
        }
    }

    /**
     * Send a mail to user(s)
     * @return boolean True on success
     */
    public function send()
    {
        if ($this->isValid) {
            $boo_result = @mail($this->receivers, $this->subject, $this->message, $this->headers);
            if (!$boo_result) {
                error_log("Mailing system error (mailer error)");
            }
            //return the result of the send command
            return $boo_result;
        }
        //return mail was not sent
        return false;
    }
}
