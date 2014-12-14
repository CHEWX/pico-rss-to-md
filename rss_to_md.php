<?php

/**
 * RSS to Markdown *
 * @package Pico
 * @subpackage Rss_to_md
 * @version 1.0
 * @author Tom Hopcraft <tom@admirecreative.co.uk>
 */

class Rss_to_md {

    public function __construct() {

    }

    // Get the settings from config.php
    public function config_loaded(&$settings) {

        $this->config = $settings;

    }

    //Set the twig variable
    public function before_render(&$twig_vars, &$twig) {

        $twig_vars['rss_to_md'] = $this->rss_to_md();

    }

    /**
     * Get the rss data
     */

    private function rss_to_md() {

    /*  =============================
        Debugging
        @description:   Debug an array
        @lastupdated:   26/02/2014
        @updatedby:     James Kemp

        $item string/array
        $die boolean Should we kill PHP after showing the debug data?
        $vardump boolean
        ============================= */

        function d($item, $die = true, $vardump = false) {
            if($die) {
                die('<pre>' . ($vardump ? var_dump($item) : print_r($item, true)) . '</pre>');
            } else {
                echo '<pre>';
                    ($vardump ? var_dump($item) : print_r($item));
                echo '</pre>';
            }
        }

        if (isset($_GET['importer'])) {

            // include the config
            $config = $this->config;

            // get the feed url
            if (isset($config['rss_feed'])) {
                $url = $config['rss_feed'];
            }

            // set date format to default
            $date_format = $config['date_format'];

            // build the array
            $rss = new DOMDocument();
            $rss->load($url);

            //d($rss);

            function seoUrl($string) {
                //Lower case everything
                $string = strtolower($string);
                //Make alphanumeric (removes all other characters)
                $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
                //Clean up multiple dashes or whitespaces
                $string = preg_replace("/[\s-]+/", " ", $string);
                //Convert whitespaces and underscore to dash
                $string = preg_replace("/[\s_]/", "-", $string);
                return $string;
            }

            foreach ($rss->getElementsByTagName('item') as $node) {

                $title = $node->getElementsByTagName('title')->item(0)->nodeValue;
                $date = date($date_format, strtotime($node->getElementsByTagName('pubDate')->item(0)->nodeValue));
                $description = $node->getElementsByTagName('description')->item(0)->nodeValue;
                $permalink = seoUrl($title);
                $rssLink = $node->getElementsByTagName('link')->item(0)->nodeValue;

                $postMd = fopen('content/'.$permalink.".md", "w") or die("Unable to open file!");
                // open meta
                $content = "/*\n";
                fwrite($postMd, $content);

                $content = 'Title: '.$title."\n";
                fwrite($postMd, $content);

                $content = 'Date: '.$date."\n";
                fwrite($postMd, $content);

                $content = 'Link: '.$rssLink."\n";
                fwrite($postMd, $content);

                // close meta
                $content = "*/\n\n";
                fwrite($postMd, $content);
                // close meta
                $content = $description."\n";
                fwrite($postMd, $content);

                fclose($postMd);
            }

        }

    }

}
