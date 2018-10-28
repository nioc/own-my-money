<?php

/**
 * JSON Web Token wrapper.
 *
 *  Token are signed using HMAC-SHA256 algorithm
 *
 * @version 1.0.0
 *
 * @internal
 *
 * @see https://jwt.io/
 */
class Token
{
    /**
     * @var string Default key for Token (please note that for security reasons, you HAVE TO provide a value in constructor)
     */
    private $key = 'ab§CDef12*34-';
    /**
     * @var object Raw data contained in the token
     */
    public $payload;
    /**
     * @var integer Expiration timestamp
     */
    public $expire;
    /**
     * @var string Token as transmitted to client
     */
    public $value;

    /**
     * Initializes a token with the provided hash key.
     *
     * @param string $key Optional hash key
     */
    public function __construct($key = null)
    {
        if ($key !== null) {
            $this->key = $key;
        }
    }

    /**
     * Encodes data and returns a JSON Web Token.
     *
     * @param int $duration Optional duration of the token in hours (default is 7 days)
     */
    public function encode($duration = 168)
    {
        //header part
        $header = array(
                'alg' => 'HS256',
                'typ' => 'JWT',
        );
        $b64Header = base64_encode(json_encode($header));
        //payload part
        $this->expire = time() + ($duration * 60 * 60);
        $payload = array(
                'iss' => 'money',
                'exp' => $this->expire,
        );
        $payload = array_merge($payload, get_object_vars($this->payload));
        $b64Payload = base64_encode(json_encode($payload));
        //sign
        $signature = hash_hmac('sha256', $b64Header.'.'.$b64Payload, $this->key, true);
        $b64Signature = base64_encode($signature);
        $this->value = $b64Header.'.'.$b64Payload.'.'.$b64Signature;
    }

    /**
     * Decodes JSON Web Token and set data in payload attribute.
     *
     * @return bool Indicate if token is valid
     */
    public function decode()
    {
        $elements = explode('.', $this->value);
        if (count($elements) !== 3) {
            //invalid token format
            return false;
        }
        list($b64Header, $b64Payload, $b64Signature) = $elements;
        $headers = json_decode(base64_decode($b64Header));
        $payload = json_decode(base64_decode($b64Payload));
        $signature = base64_decode($b64Signature);
        //check header
        if (!$headers || !property_exists($headers, 'alg') || $headers->alg !== 'HS256' || !property_exists($headers, 'typ') || $headers->typ !== 'JWT') {
            //invalid header
            return false;
        }
        //check signature
        if (!$signature || !hash_equals($signature, hash_hmac('sha256', $b64Header.'.'.$b64Payload, $this->key, true))) {
            //invalid signature
            return false;
        }
        if (!$payload || !property_exists($payload, 'exp') || $payload->exp < time()) {
            //token expired
            return false;
        }
        $this->payload = $payload;
        //raw data is set, returns true
        return true;
    }

    /**
     * Log token creation in database.
     *
     * @return bool Indicate if token was logged
     */
    public function log()
    {
        if (!is_null($this->payload) && property_exists($this->payload, 'sub')) {
            require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
            $connection = new DatabaseConnection();
            //remove expired tokens
            $query = $connection->prepare('DELETE FROM `token` WHERE `user` =:user AND `expire` < UNIX_TIMESTAMP();');
            $query->bindValue(':user', $this->payload->sub, PDO::PARAM_INT);
            $query->execute();
            //insert current token
            $query = $connection->prepare('INSERT INTO `token` (`user`, `creation`, `ip`, `userAgent`, `expire`) VALUES (:user, UNIX_TIMESTAMP(), :ip , :ua, :expire);');
            $query->bindValue(':user', $this->payload->sub, PDO::PARAM_INT);
            $query->bindValue(':ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
            $query->bindValue(':ua', $_SERVER['HTTP_USER_AGENT'], PDO::PARAM_STR);
            $query->bindValue(':expire', $this->expire, PDO::PARAM_INT);
            return $query->execute();
        }
        return false;
    }
}
