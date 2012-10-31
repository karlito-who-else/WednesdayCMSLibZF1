<?php
/**
 * Wednesday_Renderer_Helper_Static
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
  @author jamesh
 */
class Wednesday_Renderer_Helper_Variable extends \Zend_View_Helper_Abstract {
    
    public function Variable() {
	echo "Params:".func_num_args()."<br />";
//	var_dump( func_get_args() );
//	echo "<br />";
    }

}
