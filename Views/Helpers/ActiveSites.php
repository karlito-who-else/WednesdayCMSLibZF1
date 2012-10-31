<?php
//namespace Wednesday\View\Helper;

use \Zend_Controller_Front as Front,
    \Zend_Session_Namespace as SessionNamespace,
    \Zend_View_Helper_Abstract as ViewHelperAbstract;

/**
 * Description of ActiveSites
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
 * @author jamesh
 */
class Wednesday_View_Helper_ActiveSites extends ViewHelperAbstract {
    
    const PAGES = "Application\Entities\Pages";
    
    protected $activeSite;
    protected $session;
    protected $log;
    
    /**
     *
     * @return type
     */
    public function activeSites($setActive = false) {
        $rendered = "\n";
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $this->log = $bootstrap->getResource('Log');
        $this->config = $bootstrap->getContainer()->get('config');
        
        $this->session = new SessionNamespace('wedcms');
        if($setActive!=false){
            $this->session->siteroot = $setActive;
        }
        $this->activeSite = (isset($this->session->siteroot)===true)?$this->session->siteroot:$this->config['settings']['application']['siteroot'];
        foreach($this->config['settings']['application']['sites'] as $uid => $name) {
            $active = ($this->activeSite==$uid)?'active':'';
            $rendered .= '<li class="'.$active.'"><a href="/admin/sites/switch/?active='.$uid.'">'.$name.'</a></li>'."\n";            
        }
        $this->log->info(get_class($this) . '::activeSites( ' . $this->activeSite . ' )');
        
        return $rendered;
    }
}
