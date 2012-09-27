<?php
//namespace Wednesday\View\Helper;

use \Zend_Controller_Front as Front,
    \Zend_View_Helper_Abstract as ViewHelperAbstract;

/**
 * Description of Resource
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author jamesh
 */
class Wednesday_View_Helper_Vcard extends ViewHelperAbstract {

    /**
     *
     * @return type
     */
    public function vcard($entity = false) {
        $fallback = <<<EOV
            <div class="vcard screen-offset" data-pattern="http://microformats.org/wiki/hcard" id="client">
                <h5 class="fn org">[CLIENT NAME]</h5>
                <h6>Address</h6>
                <address class="adr" data-pattern="http://microformats.org/wiki/adr">
                    <div class="street-address"  >street address</div>
                    <div class="extended-address">extended address</div>
                    <div class="locality"        >locality</div>
                    <div class="region"          >region</div>
                    <div class="postal-code"     >postal code</div>
                    <div class="country"         >country</div>
                </address>
                <h6>Tel</h6>
                <div class="tel">
                    <span class="type">work</span>:
                    <span class="value">000000</span>
                </div>
                <h6>Fax</h6>
                <div class="tel">
                    <span class="type">fax</span>:
                    <span class="value">000000</span>
                </div>
                <h6>Email</h6>
                <div><a class="email url" href="#">email</a></div>
            </div>
EOV;
		$bootstrap = Front::getInstance()->getParam("bootstrap");
		$this->config = $bootstrap->getContainer()->get('config');
        $this->em = $bootstrap->getContainer()->get('entity.manager');
        if($entity instanceof Application\Entities\Hcards) {
            return $entity->toHtml();
        } else if(is_numeric($entity)) {
            $id = $entity;
            $entity = $this->em->getRepository('Application\Entities\Hcards')->findOneById($id);
            if(isset($entity)===false){
                return $fallback;
            }
            return $entity->toHtml();
        }

        return $fallback;
    }
}
