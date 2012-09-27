<?php
namespace Wednesday\Renderers;

/**
 * Description of Renderer
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
 * @author mrhelly
 */
interface Renderer {

//    public function __construct();

    public function __toString();

    public function render();

}
