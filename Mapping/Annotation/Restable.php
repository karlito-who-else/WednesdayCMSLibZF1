<?php

namespace Wednesday\Mapping\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Form annotation for Restable behavioral extension
 *
 * @Annotation
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author James A Helly <james@wednesday-london.com>
 * @package Wednesday.Mapping.Annotation
 * @subpackage Restable
 * @link http://www.wednesday-london.com
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @version    $Id: 1.7.4 RC1 jameshelly $
 * @author mrhelly
 */
final class Restable extends Annotation
{
    public $forwardTo = 'default';
    public $exclude = false;
}