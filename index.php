<?php
// Starts with util function
function startsWith($haystack, $needle)
{
    return (substr($haystack, 0, strlen($needle)) === $needle);
}

// Append sml elements
function sxml_append(SimpleXMLElement $to, SimpleXMLElement $from)
{
    $toDom = dom_import_simplexml($to);
    $fromDom = dom_import_simplexml($from);
    $toDom->appendChild($toDom->ownerDocument->importNode($fromDom, true));
}

// Handle an RSS feed
function handle_feed($feed)
{
    // Check valid url
    if (!filter_var($feed, FILTER_VALIDATE_URL)) {
        header("HTTP/1.1 422 Unprocessable Entity");
        die("The provided feed '" . $feed . "' is malformed. Ensure it is a valid url.");
    }

    // Ensure http
    if (startsWith($feed, "https://")) {
        $feed = "http://" . explode("https://", $feed, 2)[1];
    }

    // Get contents
    try {
        $feed_content = @file_get_contents($feed);
    } catch (Exception $e) {
        header("HTTP/1.1 500 Internal Server Error");
        die("An error occurred whilst fetching the feed '" . $feed . "'.");
    }
    if (!$feed_content) {
        header("HTTP/1.1 500 Internal Server Error");
        die("An invalid response was received when fetching the feed '" . $feed . "'");
    }

    // Parse
    try {
        $feed_content = simplexml_load_string($feed_content);
    } catch (Exception $e) {
        header("HTTP/1.1 500 Internal Server Error");
        die("An error occurred whilst parsing the feed '" . $feed . "'.");
    }

    // Done
    return $feed_content->xpath('/rss//item');
}

// Get feeds for URI
try {
    $feeds = [];
    if (isset($_GET['feed'])) {
        if (is_array($_GET['feed'])) {
            foreach ($_GET['feed'] as $f) {
                $feeds[] = handle_feed($f);
            }
        } else {
            $feeds[] = handle_feed($_GET['feed']);
        }
    }
    if (isset($_GET['feeds'])) {
        if (is_array($_GET['feeds'])) {
            foreach ($_GET['feeds'] as $f) {
                $feeds[] = handle_feed($f);
            }
        } else {
            $feeds[] = handle_feed($_GET['feeds']);
        }
    }

    if (!isset($_GET['feed']) && !isset($_GET['feeds'])) {
        header("HTTP/1.1 422 Unprocessable Entity");
        die("Please provide RSS feeds to merge in URL. (Eg. ?feeds[]=https://blog.jetbrains.com/feed/&feeds[]=https://blog.jetbrains.com/idea/feed/)");
    }
} catch (Exception $e) {
    header("HTTP/1.1 500 Internal Server Error");
    die("An error occurred whilst parsing feeds from request URL.");
}

// Export the RSS feed
try {
    // Combine
    $feeds = array_merge(...$feeds);

    // Sort
    usort($feeds, function ($x, $y) {
        return strtotime($y->pubDate) - strtotime($x->pubDate);
    });

    // Create RSS
    $root = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/';
    $rss = new SimpleXMLElement('<rss><channel><title>RSS Merger</title><description>A PHP tool to merge multiple RSS streams into one output.</description><link>' . $root . '</link></channel></rss>');
    $rss->addAttribute('version', '2.0');
    foreach ($feeds as $feed) {
        sxml_append($rss->channel, $feed);
    }

    // Display
    header("Content-type: text/xml");
    //echo $rss->asXML(); // Ugly print
    $dom = dom_import_simplexml($rss)->ownerDocument; // Pretty print
    $dom->formatOutput = true;
    echo $dom->saveXML();
    die();

} catch (Exception $e) {
    header("HTTP/1.1 500 Internal Server Error");
    die("An error occurred whilst generating final RSS feed.");
}

?>