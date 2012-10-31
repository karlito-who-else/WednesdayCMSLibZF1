<?php
//namespace Wednesday\Controller\Action\Helper;

use Zend_Controller_Action_Helper_ContextSwitch as ContextSwitchActionHelper;
/**
 * RestActionController - The rest error controller class
 * Description of AdminAction
 * filename - The default error controller class
 *
 * @category	category_declaration
 * @package		package_declaration
 * @subpackage	subpackage_declaration
 * @copyright	copyright_declaration
 * @license		license_declaration
 * @author		mrhelly
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.7.4 RC1, jameshelly $		version
 *
 */

class Wednesday_Controller_Action_Helper_WednesdayContext/*Context*/ extends ContextSwitchActionHelper //*extends ControllerActionHelper*/
//class Wednesday_Controller_Action_Helper_MultipleUploader extends ControllerActionHelper {
{
    /**
     * Controller property to utilize for context switching
     * @var string
     */
    protected $_contextKey = 'ajaxable';

    /**
     * Constructor
     *
     * Add HTML context
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->addContext('html', array('suffix' => 'ajax'));
        $this->addContext('xml', array('suffix' => 'xml'));
        $this->addContext('json', array('suffix' => 'json'));
    }

    /**
     * Initialize AJAX context switching
     *
     * Checks for XHR requests; if detected, attempts to perform context switch.
     *
     * @param  string $format
     * @return void
     */
    public function initContext($format = null)
    {
        $this->_currentContext = null;

        if (!$this->getRequest()->isXmlHttpRequest()) {
            return;
        }

        return parent::initContext($format);
    }
}
