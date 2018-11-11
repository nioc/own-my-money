<?php

/**
 * Updater wrapper.
 *
 * @version 1.0.0
 *
 * @internal
 */
class Updater
{
    /**
     * @var string Source repository url
     */
    private $url;

    /**
     * @var string User agent used in HTTP request
     */
    private $userAgent;

    /**
     * @var string Root web server folder
     */
    private $rootFolder;

    /**
     * @var string Archive filename
     */
    private $filename;

    /**
     * @var int Timeout for external HTTP request
     */
    private $timeout;

    /**
     * @var string Version
     */
    public $version;

    /**
     * @var string Archive download URL
     */
    private $downloadUrl;

    /**
     * @var array Update logs
     */
    public $logs = [];

    public function __construct()
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Configuration.php';
        $configuration = new Configuration();
        $this->url = $configuration->get('repositoryUrl');
        $this->userAgent = $configuration->get('userAgent');
        $this->timeout = intval($configuration->get('externalTimeout'));
        $this->rootFolder = $_SERVER['DOCUMENT_ROOT'];
        if (basename($this->rootFolder) === 'dist') {
            $this->rootFolder = dirname($this->rootFolder);
        }
        if (basename($this->rootFolder) === 'own-my-money') {
            $this->rootFolder = dirname($this->rootFolder);
        }
    }

    /**
     * Log content in timestamped array
     *
     * @param string $content Information to be logged
     * @param boolean $insertLine If false, the content will be added to the last log message
     */
    private function output($content, $insertLine = true)
    {
        if ($insertLine) {
            $log = new stdClass;
            $log->timestamp = time();
            $log->message = $content;
            array_push($this->logs, $log);
            return;
        }
        $log = end($this->logs);
        $log->message = $log->message . ' ' . $content;
    }

    /**
     * Execute an HTTP GET request
     *
     * @param string $url URL to request
     * @param resource $handle Local file resource for getting file
     *
     * @return bool|string HTTP response or false
     */
    private function doHttpRequest($url, $handle = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        if ($handle) {
            curl_setopt($curl, CURLOPT_FILE, $handle);
        } else {
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        }
        $result = curl_exec($curl);
        if (curl_errno($curl)) {
            $this->output('download error : ' . curl_error($curl), false);
            $result = false;
        } else {
            $curlInfo = curl_getinfo($curl);
            $this->output($curlInfo['download_content_length'] . ' bytes on ' . $curlInfo['total_time'] . ' seconds', false);
        }
        curl_close($curl);
        return $result;
    }

    /**
     * Get last version on the repository
     *
     * @return bool Result of the operation
     */
    public function getLastVersion()
    {
        $this->version = '';
        $this->downloadUrl = null;
        //get last release on repository
        $this->output('Get last version...', true);
        $result = $this->doHttpRequest($this->url);
        if (!$result) {
            $this->output('failed', false);
            return false;
        }
        $this->output('done', false);

        //get version in release response
        $release = json_decode($result);
        if (!property_exists($release, 'tag_name')) {
            $this->output('No release was found', true);
            return false;
        }
        $this->version = $release->tag_name;
        $this->output("Release is $this->version", true);

        //iterate on assets to find the archive
        if (property_exists($release, 'assets') && is_array($release->assets) && count($release->assets) > 0) {
            foreach ($release->assets as $asset) {
                if (property_exists($asset, 'name') && $asset->name === "own-my-money-$this->version.tar.gz" && property_exists($asset, 'browser_download_url')) {
                    $this->downloadUrl = $asset->browser_download_url;
                    $this->filename = $asset->name;
                }
            }
        }
        if ($this->downloadUrl === null) {
            $this->output('No download url was found', true);
            return false;
        }

        return true;
    }

    /**
     * Download an archive from the repository
     *
     * @return bool Result of the download
     */
    public function downloadArchive()
    {
        if (!is_writable($this->rootFolder)) {
            $webUser = posix_getpwuid(posix_getuid())['name'];
            $this->output("Folder '$this->rootFolder' is not writable, you should run: chown $webUser $this->rootFolder -R", true);
            return false;
        }
        $this->output('Download archive...', true);
        if (!$localHandle = fopen("$this->rootFolder/$this->filename", 'w+')) {
            $this->output("Unable to write archive at $this->rootFolder/$this->filename", false);
            return false;
        }
        $result = $this->doHttpRequest($this->downloadUrl, $localHandle);
        fclose($localHandle);
        if (!$result) {
            $this->output('failed', false);
            unlink("$this->rootFolder/$this->filename");
            return false;
        }
        $this->output('done', false);

        return true;
    }

    /**
     * Extract files from archive
     *
     * @return bool Result of the extraction
     */
    public function extractArchive()
    {
        //extract files from archive
        $pharData = new PharData($this->rootFolder . '/' . $this->filename);
        $this->output("Extract archive to $this->rootFolder", true);
        try {
            $pharData->extractTo($this->rootFolder, null, true);
        } catch (Exception $e) {
            $this->output('failed', false);
            $this->output($e->getMessage(), false);
            return false;
        }
        $this->output('done', false);

        //change back-end url
        $domain = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
        if ($handle = opendir("$this->rootFolder/own-my-money/money-front-vue/dist/static/js/")) {
            while (false !== ($entry = readdir($handle))) {
                $current = "$this->rootFolder/own-my-money/money-front-vue/dist/static/js/$entry";
                if (is_file($current)) {
                    $content = file_get_contents($current);
                    $content = str_replace('http://localhost', $domain, $content);
                    file_put_contents($current, $content);
                }
            }
            closedir($handle);
        }
        //redirect to the setup page
        $this->output("Application is updated, you may have to configure at : $domain/#/setup", true);

        return true;
    }
}
