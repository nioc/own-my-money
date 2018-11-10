<?php

/**
 * Configuration wrapper.
 *
 * @version 1.1.0
 *
 * @internal
 */
class Configuration
{
    /**
     * @var array Configuration settings
     */
    private $configuration;

    public function __construct($includeLocal = true)
    {
        $confDist = array();
        $confLocal = array();
        //get original configuration
        if (is_file($_SERVER['DOCUMENT_ROOT'].'/server/configuration/configuration.ini')) {
            $confDist = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/server/configuration/configuration.ini');
        }
        $confLocal = [];
        if ($includeLocal) {
            //get local configuration
            if (is_file($_SERVER['DOCUMENT_ROOT'].'/server/configuration/local.ini')) {
                $confLocal = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/server/configuration/local.ini');
            }
        }
        //merge configurations (local override original)
        $this->configuration = array_merge($confDist, $confLocal);
    }

    /**
     * Returns a configuration setting.
     *
     * @param string $key Searched setting
     *
     * @return mixed|bool Setting value
     */
    public function get($key)
    {
        if (array_key_exists($key, $this->configuration)) {
            return $this->configuration[$key];
        }
        //unknown key
        return false;
    }

    /**
     * Returns all configuration settings in a key/value array.
     *
     * @return array Settings
     */
    public function query()
    {
        $settings = array();
        foreach ($this->configuration as $key => $value) {
            $setting = new stdClass();
            $setting->key = $key;
            $setting->value = $value;
            $settings[] = $setting;
        }

        return $settings;
    }

    /**
     * Set a setting value.
     *
     * @param string $key   Setting to update
     * @param string $value Value to set
     *
     * @return bool Operation status
     */
    public function set($key, $value)
    {
        $oldValue = $this->get($key);
        if ($oldValue === false) {
            //unknown key
            return false;
        }
        $path = $_SERVER['DOCUMENT_ROOT'].'/server/configuration/local.ini';
        $localConfiguration = file_get_contents($path);
        if (!$localConfiguration) {
            //Local file was not created, set an empty string
            $localConfiguration = "; This your local configuration file\n";
        }
        $localConfiguration = str_replace("$key = \"$oldValue\"", "$key = \"$value\"", $localConfiguration, $count);
        if ($count == 0) {
            //The key was not in local file, add it
            $localConfiguration .= "$key = \"$value\"\n";
        }
        file_put_contents($path, $localConfiguration);
        //check if new value is set
        $newConfiguration = new self();
        if ($value !== $newConfiguration->get($key)) {
            //key is not set
            return false;
        }
        //Seems to be ok
        return true;
    }
}
