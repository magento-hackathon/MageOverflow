<?php
require 'src/twitter.class.php';
require 'config.php';

$twitter = new Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);

$rss = file_get_contents('http://magento.stackexchange.com/feeds');
$xml = new SimpleXMLElement($rss);

// Post the message

#$twitter->send('Hey world, this is the first test!');