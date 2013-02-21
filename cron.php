<?php
require 'src/twitter.class.php';
require 'config.php';

define('LOCK_FILE', 'se.lock');

// check wether the lockfile exists
touch(LOCK_FILE);

$twitter = new Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);

$feed = simplexml_load_file('http://magento.stackexchange.com/feeds/');
$lock = (int)file_get_contents(LOCK_FILE);
if ($feed && count($feed->entry)) {
    foreach ($feed->entry as $entry) {
        $id = preg_replace('/^.*\//', '', $entry->id);
        if ($lock < $id) {
            /* Update lock file */
            if (!isset($first_id)) {
                $first_id = $id;
                file_put_contents(LOCK_FILE, $first_id);
            }
            $url = make_bitly_url($entry->id, $bitlyLogin, $bitlyApiKey);
            $text = $entry->title;
            $chars_left = 140 - strlen($url) - 1;
            if (strlen($entry->title) > $chars_left) {
                $text = substr($text, 0, $chars_left - 2) . '..';
            }
            $text .= ' ' . $url;
            echo $text . "\n";

            try {
                $tweet = $twitter->send($text);
            } catch (TwitterException $e) {
                echo 'Error: ' . $e->getMessage();
            }
        } else {
            break;
        }
    }
}

function make_bitly_url($url, $login, $api, $format = 'xml', $version = '2.0.1')
{
    $bitly = 'http://api.bit.ly/shorten?version=' . $version . '&longUrl=' . urlencode($url) . '&login=' . $login
        . '&apiKey=' . $api . '&format=' . $format;
    $response = file_get_contents($bitly);
    if (strtolower($format) == 'json') {
        $json = @json_decode($response, true);
        return $json['results'][$url]['shortUrl'];
    } else {
        $xml = simplexml_load_string($response);
        return 'http://bit.ly/' . $xml->results->nodeKeyVal->hash;
    }
}