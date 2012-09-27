<?php
/**
 * Wednesday_Renderer_Helper_Static
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author jamesh
 */
class Wednesday_Renderer_Helper_Variable extends \Zend_View_Helper_Abstract {
    
    public function Variable() {
	echo "Params:".func_num_args()."<br />";
//	var_dump( func_get_args() );
//	echo "<br />";
    }

}
