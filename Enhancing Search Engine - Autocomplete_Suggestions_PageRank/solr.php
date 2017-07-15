<?php
header('Content-Type: text/html; charset=utf-8');
include('snippet.php');
include('SpellCorrector.php');
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$defaultResults = false;
$pageRankResults = false;
$spellFlag = false;
if ($query) {
    // The Apache Solr Client library should be on the include path
    // which is usually most easily accomplished by placing in the
    // same directory as this script ( . or current directory is a default
    // php include path entry in the php.ini)
    require_once('solr-php-client/Apache/Solr/Service.php');


    $csvMap = Array();
    $fileHandler = fopen('mapNYTimesDataFile.csv', 'r');
    $csvLines = file('mapNYTimesDataFile.csv');

    foreach ($csvLines as $line) {
        $splitLine = explode(',', $line);
        $csvMap[$splitLine[0]] = $splitLine[1];
    }

    // create a new solr service instance - host, port, and corename
    // path (all defaults in this example)

    $solr = new Apache_Solr_Service('localhost', 8983, '/solr/irhw5/');

    // if magic quotes is enabled then stripslashes will be needed
    if (get_magic_quotes_gpc() == 1) {
        $query = stripslashes($query);
    }

    $spellFlag = false;
    $oldQuery = strtolower($query);
    $queryArray = explode(" ",$query);
    $newQueryArray = array();
    foreach($queryArray as $term){
        $newQueryArray[] = SpellCorrector::correct($term);
    }

    $newQuery = implode(" ", $newQueryArray);

    if($oldQuery != $newQuery){
        $spellFlag = true;
        $query = $newQuery;
    }
    // in production code you'll always want to use a try /catch for any
    // possible exceptions emitted by searching (i.e. connection
    // problems or a query parsing error)
    try {
        $defaultResults = $solr->search($query, 0, $limit);

	   $additionalParameters = array('sort' => 'pageRankFile desc');
        $pageRankResults = $solr->search($query, 0, $limit, $additionalParameters);
    } catch (Exception $e) {
        // in production you'd probably log or email this error to an admin
        // and then show a special message to the user but for this example
        // we're going to show the full exception
        die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
    }
}
?>
<html>
<head>
    <title>PHP Solr Client Example</title>
     <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
    <script src="suggestion.js"></script>
</head>
<body>
<form accept-charset="utf-8" method="get">
    <label for="q">Search:</label>
    <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
    <input type="submit"/>
</form>
<?php

if($spellFlag){ ?>
    <p>Showing results for: 
        <a href="?q=<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"><?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>
        </a>
    </p>
    <p>Search instead for: 
        <a href="?f=1&q=<?php echo urlencode($oldQuery);?>"><?php echo $oldQuery;?></a>
    </p>
<?php
}
// display results
if ($defaultResults && $pageRankResults) {
    $totalDefault = (int)$defaultResults->response->numFound;
    $startDefault = min(1, $totalDefault);
    $endDefault = min($limit, $totalDefault);

    $totalPageRank = (int)$pageRankResults->response->numFound;
    $startPageRank = min(1, $totalPageRank);
    $endPageRank = min($limit, $totalPageRank);
    ?>
    <div>Default Results <?php echo $startDefault; ?> - <?php echo $endDefault; ?> of <?php echo $totalDefault; ?>:</div>
    <div>Page Rank Results <?php echo $startPageRank; ?> - <?php echo $endPageRank; ?> of <?php echo $totalPageRank; ?>:</div>
    <ol>
        <?php

        // iterate result documents
        for($i = 0; $i < count($defaultResults->response->docs); $i++) {

            $defaultIdArr = explode('/', $defaultResults->response->docs[$i]->id);
            $defaultDocId = $defaultIdArr[count($defaultIdArr) - 1];

            $pageRankIdArr = explode('/', $pageRankResults->response->docs[$i]->id);
            $pageRankDocId = $pageRankIdArr[count($pageRankIdArr) - 1];

            $defaultUrl = $csvMap[$defaultDocId];
            $pageRankUrl = $csvMap[$pageRankDocId];
            ?>
            <li>
                <table border=1 style="border: 1px solid black; text-align: left">
                    <tr>
                        <td>
                            <p><b><u>Default Result:</u></b></p>
                            <p>
                                <b>id:</b> <?php echo htmlspecialchars($defaultDocId, ENT_NOQUOTES, 'utf-8'); ?>
                            </p>
                            <p>
                                <a href="<?php echo $defaultUrl; ?>" target="_blank">
				  <?php echo htmlspecialchars($defaultResults->response->docs[$i]->title, ENT_NOQUOTES, 'utf-8'); ?>
				</a>
                            </p>
                            <p>
                                <a href="<?php echo $defaultUrl; ?>"
                                   target="_blank"><?php echo htmlspecialchars($defaultUrl, ENT_NOQUOTES, 'utf-8'); ?></a>
                            </p>
                            <p>
                                <?php 
                                echo "<b>Snippet: </b>" . htmlspecialchars(generateSnippet($defaultResults->response->docs[$i]->id, $query), ENT_NOQUOTES, 'utf-8'); ?></a>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <p><b><u>Page Rank Result:</u></b></p>
                            <p>
                                <b>id:</b> <?php echo htmlspecialchars($pageRankDocId, ENT_NOQUOTES, 'utf-8'); ?>
                            </p>
                            <p>
                                <a href="<?php echo $pageRankUrl; ?>" target="_blank">
				<?php echo htmlspecialchars($pageRankResults->response->docs[$i]->title, ENT_NOQUOTES, 'utf-8'); ?>
				</a>
                            </p>
                            <p>
                                <a href="<?php echo $pageRankUrl; ?>"
                                   target="_blank"><?php echo htmlspecialchars($pageRankUrl,
                                        ENT_NOQUOTES, 'utf-8'); ?></a>
                            </p>
                            <p>
                                <?php
                                echo "<b>Snippet: </b>" . htmlspecialchars(generateSnippet($pageRankResults->response->docs[$i]->id, $query), ENT_NOQUOTES, 'utf-8'); ?></a>
                            </p>
                        </td>
                    </tr>
                </table>
            </li>
            <?php
        }
        ?>
    </ol>
    <?php
}
?>
</body>
</html>

