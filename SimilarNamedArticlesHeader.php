<?php

$wgHooks['OutputPageBeforeHTML'][] = 'fnSimilarNamedArticlesHeader';

$wgExtensionCredits['other'][] = array (
    'path' => __file__,
    'name' => 'SimilarNamedArticlesHeader',
    'description' => 'Displays articles with a similar name on top of each page',
    'version' => '2.0.3',
    'author' => 'Mathias Ertl',
    'url' => 'http://fs.fsinf.at/wiki/SimilarNamedArticlesHeader',
);

function fnSimilarNamedArticlesHeader($output_page, $qText)
{
    global $wgTitle, $wgRequest;
    global $wgSimilarNamedArticlesHeaderEnable;
    if (!$wgSimilarNamedArticlesHeaderEnable) {
        return true;
    }

    global $wgSimilarNamedArticlesHeaderOnNamespaces;
    global $wgSimilarNamedArticlesHeaderOnSubpages;

    if (is_array($wgSimilarNamedArticlesHeaderOnNamespaces)) {
        $tmpArray = array_keys($wgSimilarNamedArticlesHeaderOnNamespaces, true);
        if (!in_array($wgTitle->getNamespace(), $tmpArray)) {
            return true;
        }
    }
    if (! $wgSimilarNamedArticlesHeaderOnSubpages && $wgTitle->isSubpage()) {
        $baseTitle = Title::newFromText(
            $wgTitle->getBaseText(),
            $wgTitle->getNamespace());
        if ($baseTitle->exists()) {
            return true;
        }
    }

    # dont show when we edit pages:
    if ($wgRequest->getVal('action') != '')
        return true;

    global $wgParser;
    global $wgSimilarNamedArticlesHeaderIncludeSubpages;
    global $wgSimilarNamedArticlesHeaderPREG;

    $snaPage = new SimilarNamedArticles();

    $titleText = $wgTitle->getText();

    # This is custimized for our needs. The List of SimilarNamedArticles
    # that is displayed above each article should be created from a
    # searchstring that is NOT the entire title (it would always only find
    # itself) but rather only the first part (in our case only 'til the
    # regular expression).
    $searchstring = preg_replace ($wgSimilarNamedArticlesHeaderPREG, '', $titleText);

    $title = Title::newFromtext($searchstring);

    # this gets the text that is actually prepended.
    $output = $snaPage->getSimilarNames($title, NULL, false);

    if ($output != '') {
        $parserOutput = $wgParser->parse("$output\n", $wgTitle, $output_page->parserOptions(),
            true, true, $output_page->getRevisionId());
        $output_page->addParserOutputNoText($parserOutput);

        # you could invert this to append the text instead of prepending it.
        $qText =  $parserOutput->getText() . $qText;
    }

    return true;
}

?>
