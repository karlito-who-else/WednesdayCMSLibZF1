<?php
namespace Wednesday\Renderers;

/**
 * Description of Renderer
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
 * @author mrhelly
 */
interface Renderer {

//    public function __construct();

    public function __toString();

    public function render();

}
