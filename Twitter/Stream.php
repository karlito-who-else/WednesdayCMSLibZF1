<?php
namespace Wednesday\Twitter;

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Twitter
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Search.php 23775 2011-03-01 17:25:24Z ralph $
 */

/**
 * @see Zend_Http_Client
 * @see Zend_Json
 * @see Zend_Feed
 */
use	\Zend_Service_Twitter_Exception,
	\Zend_Service_Twitter,
	\Zend_Http_Client,
	\Zend_Rest_Client,
	\Zend_Rest_Client_Result,
	\Zend_Json,
	\Zend_Feed;

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Twitter
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
//Zend_Service_Twitter_Search
class Stream extends Zend_Service_Twitter
{
    /**
     * Types of API methods
     *
     * @var array
     */
    protected $_methodTypes = array(
        'status',
        'user',
        'directMessage',
        'friendship',
        'account',
        'favorite',
        'block',
        'statuses'
    );
          
    /**
     * Public Timeline status
     *
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function statusPublicFilter(array $params = array())
    {
        $this->_init();
        $path = '/1/statuses/filter.json';
        $_params = array();
        foreach ($params as $key => $value) {
        	switch(strtolower($key)) {        	
		        case 'count':
	                $count = (int) $value;
	                if (0 >= $count) {
	                    $count = 1;
	                } elseif (200 < $count) {
	                    $count = 200;
	                }
	                $_params['count'] = (int) $count;
	                break;	        
		        case 'delimited':
			        $_params['delimited'] = $this->_validInteger($value);
					break;
		        case 'follow':
		        	$_params['follow'] = $value;
					break;
		        case 'locations':
			        $_params['locations'] = $value;
					break;
		        case 'track':
		        	$_params['track'] = $value;
					break;
		        case 'stall_warnings':
			        $_params['stall_warnings'] = ($value)?'true':'false';
			        break;
		       	default:
		       		break;
        	}
        }
		//$httpClient = $this->getLocalHttpClient();
		//$httpClient->setStream("twitter_stream.txt");
        $response = $this->_post($path, $_params);
        return $response->getBody();
    }    
}
