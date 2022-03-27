<?php
/**
 * Installation script
 *
 * This should be executed from the shell as webserver user (usually www-data),
 * like this: su www-data -s /bin/sh -c "php installer.php"
 *
 */

/**
 * Execute an HTTP GET request
 *
 * @param string $url URL to request
 * @param resource $handle Local file resource for getting file
 *
 * @return bool|string HTTP response or false if failure
 */
function doHttpRequest($url, $handle = null)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_USERAGENT, 'own-my-money-installer');
    curl_setopt($curl, CURLOPT_TIMEOUT, 90);
    if ($handle) {
        curl_setopt($curl, CURLOPT_FILE, $handle);
        curl_setopt($curl, CURLOPT_NOPROGRESS, false);
        curl_setopt($curl, CURLOPT_PROGRESSFUNCTION, 'progress');
    } else {
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    }
    $result = curl_exec($curl);
    if (curl_errno($curl)) {
        output('download error : ' . curl_error($curl), false);
        $result = false;
    } else {
        $curlInfo = curl_getinfo($curl);
        output($curlInfo['download_content_length'] . ' bytes on ' . $curlInfo['total_time'] . ' seconds', false);
    }
    curl_close($curl);
    return $result;
}

/**
 * Output content
 *
 * @param string $content Information to be logged
 * @param boolean $insertLine If false, the content will be added to the last log message
 */
function output($content, $insertLine = true)
{
    if ($insertLine) {
        echo "\r\n";
    } else {
        echo ' ';
    }
    echo $content;
}

/**
 * Display a question and wait for user answer
 *
 * @param string $question Question to display
 *
 * @return string User answer
 */
function prompt($question)
{
    output($question, true);
    $stdin = fopen('php://stdin', 'r');
    $response = fgets($stdin);
    fclose($stdin);
    return trim($response);
}

/**
 * Display download progress
 *
 * @param string $content Information to be logged
 * @param boolean $insertLine If false, the content will be added to the last log message
 */
function progress($resource, $download_size, $downloaded_size, $upload_size, $uploaded_size)
{
    static $previousProgress = 0;
    if ($download_size == 0) {
        $progress = 0;
        return;
    }
    $progress = round($downloaded_size * 100 / $download_size);
    if ($progress > $previousProgress) {
        $previousProgress = $progress;
        output("$progress%...", false);
    }
}

$folder = getcwd();
if (!is_writable($folder)) {
    $webUser = posix_getpwuid(posix_getuid())['name'];
    output("Folder '$folder' is not writable, you should run: chown $webUser $folder -R", true);
    return false;
}

//get last release on repository
$url = 'https://api.github.com/repos/nioc/own-my-money/releases/latest';
output('Get last version...', true);
$result = doHttpRequest($url, null);
if (!$result) {
    output('failed', false);
    return false;
}
output('done', false);

//get version in release response
$release = json_decode($result);
if (!property_exists($release, 'tag_name')) {
    output('No release was found', true);
    return false;
}
$version = $release->tag_name;
output('Release is '.$version, true);

//iterate on release assets to find the archive
$downloadUrl = null;
if (property_exists($release, 'assets') && is_array($release->assets) && count($release->assets) > 0) {
    foreach ($release->assets as $asset) {
        if (property_exists($asset, 'name') && $asset->name === "own-my-money-$version.tar.gz" && property_exists($asset, 'browser_download_url')) {
            $downloadUrl = $asset->browser_download_url;
            $filename = $asset->name;
        }
    }
}
if ($downloadUrl === null) {
    output('No download url was found', true);
    return false;
}

//download archive
output('Download archive...', true);
if (!$localHandle = fopen($filename, 'w+')) {
    output("unable to write archive at $folder/$filename", false);
    return false;
}
$result = doHttpRequest($downloadUrl, $localHandle);
fclose($localHandle);
if (!$result) {
    output('failed', false);
    return false;
}
output('done', false);
output('', true);

//extract files from archive
$pharData = new PharData($filename);
do {
    $rootFolder = prompt('Type your document root folder (default: /var/www):');
    if ($rootFolder === '') {
        $rootFolder = '/var/www';
    }
    if (!$isDir = is_dir($rootFolder)) {
        output("Folder '$rootFolder' does not exist", true);
    }
    if (!$isWriteable = is_writable($rootFolder)) {
        output("Folder '$rootFolder' is not writable", true);
    }
} while (!$isDir || !$isWriteable);
output("Extract archive to $rootFolder...", true);
try {
    $pharData->extractTo($rootFolder);
} catch (Exception $e) {
    output('failed', false);
    output($e->getMessage(), false);
    return false;
}
output('done', false);

//create public folder
output('Create web folder...', true);
if (mkdir("$rootFolder/own-my-money/dist")) {
    output('done', false);
} else {
    output("failed, you have to create it from shell: mkdir $rootFolder/own-my-money/dist", false);
}

//create symolic links
output('Create symolic links...', true);
if (symlink("$rootFolder/own-my-money/server", "$rootFolder/own-my-money/dist/server")) {
    output('done', false);
} else {
    output("failed, you have to create it from shell: ln -s $rootFolder/own-my-money/server $rootFolder/own-my-money/dist/server", false);
}
output('', true);

//change back-end url
do {
    $domain = prompt('Type your domain name (example: https://money.yourdomain.ltd):');
    $isValid = filter_var($domain, FILTER_VALIDATE_URL);
    if (!$isValid) {
        output("'$domain' is not a valid domain", true);
    }
} while (!$isValid);
if ($handle = opendir("$rootFolder/own-my-money/money-front-vue/dist/assets/")) {
    while (false !== ($entry = readdir($handle))) {
        $current = "$rootFolder/own-my-money/money-front-vue/dist/assets/$entry";
        if (is_file($current)) {
            $content = file_get_contents($current);
            $content = str_replace('http://localhost', $domain, $content);
            file_put_contents($current, $content);
        }
    }
    closedir($handle);
}

//set permission
output('If you did not use webserver (www-data, apache, ...) user to launch this script, you have to change the owner of files served to your web user:', true);
output("chown www-data $rootFolder/own-my-money/ -RL", true);
output('', true);

//redirect to the setup page
output("Application is installed, you have to configure it, please visit $domain/#/setup", true);
output('', true);
output('', true);
