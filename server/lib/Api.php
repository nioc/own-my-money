<?php

/**
 * API wrapper.
 *
 * @version 1.2.0
 *
 * @internal
 */
class Api
{
    const PARAM_STRING = 0;
    const PARAM_INTEGER = 1;
    const PARAM_FLOAT = 2;
    /**
     * @var string HTTP verb used to call API
     */
    public $method;
    /**
     * @var array Parameters provided in API call ; query parameters are in query[param], body request is in query['body']
     */
    public $query;
    /**
     * @var string Requested output format
     */
    private $outputFormat;
    /**
     * @var array HTTP verbs allowed for calling API
     */
    private $allowedMethods;
    /**
     * @var int HTTP status code returned by API
     */
    private $httpCode;
    /**
     * @var string Returned data
     */
    private $responseBody;
    /**
     * @var int User identifier who is requesting API
     */
    public $requesterId;
    /**
     * @var string Requested language provided in Accept-Language header
     */
    public $language;

    /**
     * Initializes an API object with the given informations.
     *
     * @param string $outputFormat   Indicates API output format, default value is json
     * @param array  $allowedMethods Allowed HTTP methods for the API, default value is ['POST', 'GET', 'DELETE', 'PUT']
     */
    public function __construct($outputFormat = 'json', $allowedMethods = array('POST', 'GET', 'DELETE', 'PUT'))
    {
        $this->outputFormat = $outputFormat;
        $this->allowedMethods = $allowedMethods;
        $this->requesterId = null;
        //check call method
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $this->method = $_SERVER['REQUEST_METHOD'];
            array_push($this->allowedMethods, 'OPTIONS');
            if (!in_array($this->method, $this->allowedMethods)) {
                //return a 501 error
                $this->output(501, $this->getMessage('methodNotImplemented', [$this->method]));
                throw new RuntimeException($this->method.' method is not supported for this ressource');
            }
            //get parameters
            $this->query = array();
            switch ($this->method) {
                case 'POST':
                case 'PUT':
                case 'PATCH':
                    $this->query['body'] = json_decode(file_get_contents('php://input'));
                    // no break
                case 'DELETE':
                case 'GET':
                default:
                    $this->query = array_merge($this->query, $_GET);
            }
            // get Language
            $this->language = Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            if (!in_array($this->language, ['fr', 'en'])) {
                $this->language = 'en';
            }
            header('Content-Language: '.$this->language);
        }
    }

    /**
     * Checks if a specific parameter was provided in the request (query string or body) and returns it by reference.
     *
     * @param string $parameter The searched parameter
     * @param string $value     The returned value of the parameter
     */
    public function checkParameterExists($parameter, &$value, $type = self::PARAM_STRING)
    {
        $value = null;
        if (array_key_exists($parameter, $this->query)) {
            //parameter found in the query string
            switch ($type) {
                case self::PARAM_FLOAT:
                    $value = floatval($this->query[$parameter]);
                    break;
                case self::PARAM_INTEGER:
                    $value = intval($this->query[$parameter], 10);
                    break;
                case self::PARAM_STRING:
                default:
                    $value = $this->query[$parameter];
                    break;
            }
            //returns requested parameter has been found in the query string
            return true;
        }
        //try in the body request, if it exists
        if (array_key_exists('body', $this->query) && $this->query['body'] && property_exists($this->query['body'], $parameter)) {
            switch ($type) {
                case self::PARAM_FLOAT:
                    $value = floatval($this->query['body']->$parameter);
                    break;
                case self::PARAM_INTEGER:
                    $value = intval($this->query['body']->$parameter, 10);
                    break;
                case self::PARAM_STRING:
                default:
                    $value = $this->query['body']->$parameter;
                    break;
            }
            //returns requested parameter has been found in the body
            return true;
        }
        //returns requested parameter has not been not found
        return false;
    }

    /**
     * Checks if a specific header was provided in the request and returns it by reference.
     *
     * @param string $name  Name of the searched header
     * @param string $value Value of the header
     *
     * @return bool Return true if header was provided
     */
    public function checkHeaderExists($name, &$value)
    {
        $value = null;
        if (!function_exists('apache_request_headers')) {
            /**
             * Fetches all HTTP request headers from the current request.
             *
             * @return array|bool An associative array of all the HTTP headers in the current request, or FALSE on failure
             */
            function apache_request_headers()
            {
                $headers = [];
                foreach ($_SERVER as $key => $value) {
                    if (substr($key, 0, 5) == 'HTTP_') {
                        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))))] = $value;
                    }
                }
                //return headers array
                return $headers;
            }
        }
        $headers = apache_request_headers();
        if (array_key_exists($name, $headers)) {
            $value = $headers[$name];
            return true;
        } elseif (array_key_exists(strtolower($name), $headers)) {
            $value = $headers[strtolower($name)];
            return true;
        }
        return false;
    }

    /**
     * Check the if the user have a correct authentication and authorization.
     *
     * @return int|bool User identifier or false if user do not have a valid authentication/authorization
     */
    public function checkAuth()
    {
        if (!$this->checkHeaderExists('Authorization', $authorization_header)) {
            $this->output(401, $this->getMessage('authorizationNotFound'));
            header('WWW-Authenticate: Bearer realm="money"');
            //Authorization header not provided
            return false;
        }
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Token.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Configuration.php';
        $configuration = new Configuration();
        $token = new Token($configuration->get('hashKey'));
        list($scheme, $token->value) = explode(' ', $authorization_header, 2);
        if ($scheme !== 'Bearer') {
            $this->output(401, $this->getMessage('tokenSchemeBearer'));
            header('WWW-Authenticate: Bearer realm="money"');
            //Not using Bearer scheme
            return false;
        }
        if (!$token->decode()) {
            $this->output(401, $this->getMessage('invalidToken'));
            header('WWW-Authenticate: Bearer realm="money"');
            //Token is not valid
            return false;
        }
        if (!property_exists($token->payload, 'sub')) {
            $this->output(401, $this->getMessage('subjectNotFound'));
            header('WWW-Authenticate: Bearer realm="money"');
            //Token do not includes user profile
            return false;
        }
        $this->requesterId = (int) $token->payload->sub;
        //Token is valid, returns the user identifier
        return $this->requesterId;
    }

    /**
     * Check the if the user have a correct authentication and authorization.
     *
     * @param string $requiredScope Required user scope for processing the API
     *
     * @return bool Return if user has the required scope
     */
    public function checkScope($requiredScope)
    {
        if (is_int($this->requesterId)) {
            require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/User.php';
            $requester = new User($this->requesterId);
            //return if required scope is found in user scope
            return $requester->hasScope($requiredScope);
        }
        //requester is not identified, return false
        return false;
    }

    /**
     * Output the provided data in the wished format.
     *
     * @param int    $httpCode     HTTP code returned
     * @param string $responseBody Data returned in the HTTP response body
     *
     * @todo Provide XML formatting (actually raw data)
     */
    public function output($httpCode = 500, $responseBody = null)
    {
        //check http code format
        $this->httpCode = 500;
        if (preg_match('/^\d\d\d$/', $httpCode)) {
            $this->httpCode = $httpCode;
        }
        //return http status
        http_response_code($this->httpCode);
        $this->responseBody = $responseBody;
        if (!preg_match('/^2\d\d$/', $this->httpCode)) {
            if ($this->httpCode == 403) {
                //add the error in webserver log
                error_log('client denied by server configuration: '.$_SERVER['SCRIPT_NAME']);
            }
            if ($this->outputFormat === 'json' || $this->outputFormat === 'xml') {
                $responseBody = new stdClass();
                $responseBody->code = $this->httpCode;
                $responseBody->message = '';
                if (isset($this->responseBody) && (is_string($this->responseBody) || is_array($this->responseBody))) {
                    $responseBody->message = $this->responseBody;
                }
                $this->responseBody = $responseBody;
            }
        }
        //return correct content-type header and output
        switch ($this->outputFormat) {
            case 'html':
                header('Content-type: text/html; charset=UTF-8');
                echo $this->responseBody;
                break;
            case 'base64':
                header('Content-type: text/plain; charset=UTF-8');
                header('Content-Transfer-Encoding: base64');
                echo $this->responseBody;
                break;
            case 'xml':
                header('Content-type: application/xml; charset=UTF-8');
                echo $this->responseBody;
                break;
            case 'json':
            default:
                header('Content-type: application/json; charset=UTF-8');
                echo json_encode($this->responseBody);
        }
    }

    /**
     * Generate a token for API usage.
     *
     * @return string Token generated
     */
    public function generateToken($payload)
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Token.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Configuration.php';
        $configuration = new Configuration();
        $token = new Token($configuration->get('hashKey'));
        $token->payload = $payload;
        $token->encode();
        $token->log();
        $result = new stdClass();
        $result->token = $token->value;
        //return the token
        return $result;
    }

    /**
     * Get localized message data.
     *
     * @param string $index Data index
     * @param array $data Informations to bind in template
     *
     * @return string Localized data located at the index
     */
    public function getMessage($index, $data = [])
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Lang.php';
        $lang = new Lang($this->language);
        $msg = $lang->getMessage($index, $data);
        return $msg;
    }
}
