<?php

/**
 * Internationalization and localization wrapper.
 *
 * @version 1.0.0
 *
 * @internal
 */
class Lang
{
    /**
    * @var string Locale for which the resources should be loaded, default value is 'en'
    */
    private $locale;
    /**
     * @var string The directory where the data is stored
     */
    private $bundlename;

    /**
     * Initializes an API object with the given informations.
     *
     * @param string $locale   Locale for which the resources should be loaded, default value is 'en'
     */
    public function __construct($locale = 'en')
    {
        $this->locale = $locale;
        $this->bundlename = $_SERVER['DOCUMENT_ROOT'].'/server/lang';
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
        try {
            $r = new ResourceBundle($this->locale, $this->bundlename);
            if (is_null($template = $r->get($index))) {
                error_log('Intl key not found: ' . $index);
                $template = '';
            }
            $fmt = new MessageFormatter($this->locale, $template);
            $msg = $fmt->format($data);
            if ($msg === false) {
                error_log($fmt->getErrorMessage());
                $msg = '';
            }
        } catch (IntlException $e) {
            error_log($e->getMessage() . $e->getTraceAsString());
            $msg = '';
        }
        return $msg;
    }
}
