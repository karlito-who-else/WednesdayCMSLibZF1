<?php

namespace Wednesday\Twitter;

use \PDO,
    \PDOException,
    \Zend_Json,
    \Zend_Json_Exception,
    \Application\Entities\TwitterItems;

/**
 * Version class allows to checking the dependencies required
 * and the current version of doctrine extensions
 *
 * @version $Id: 1.7.4 RC1 jameshelly $
 * @author James A Helly <james@wednesday-london.com>
 * @subpackage Tweet
 * @package Wednesday
 * @link http://tech.wednesday-london.com/Twitter.Structs.1.7.4.html
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
final class StreamParser {

    /**
     * PDO Connection.
     *
     */
    protected $response;
    protected $config;

    /**
     *
     */
    public function __construct($config) {
        $this->config = $config;
    }

    protected function parseJsonResponse() {
        global $entityManager;

        try {
            $added = $count = 0;
            foreach ($this->response as $result) {

                var_dump($result);
                $entity = $entityManager->getRepository('Application\Entities\TwitterItems')->findOneBy(array('tweet_id' => $result['id_str']));

                if (empty($entity)) {
                    $tweets[$count] = new TwitterItems();
                    $tweets[$count]->setTweet($result);
                    $entityManager->persist($tweets[$count]);
                    $entityManager->flush();
                    $added++;
                }
            }
            echo "Added $added Tweets" . "\n";
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br />\n";
            die("\n");
        }
    }

    /**
     *
     */
    public function handleResponse($response) {
        try {
            $resp = "";
            if (strpos($response, '{') !== false) {
                $resp = substr($response, strpos($response, '{'));
                $bits = explode("\n", $resp);
                $rows = array();

                foreach ($bits as $row) {
                    if (strpos($row, '{') !== false) {
                        $rows[] = $row;
                    }
                }
                $resp = "[" . implode(',', $rows) . "]";

                if (!empty($resp)) {
                    $this->response = Zend_Json::decode($resp);
                    $this->parseJsonResponse();
                }
            }
        } catch (Zend_Json_Exception $e) {
            print "Error!: " . $e->getMessage() . "<br />\n";
            die("\n" . "\n--" . $resp . "--\n");
        }
    }

}
