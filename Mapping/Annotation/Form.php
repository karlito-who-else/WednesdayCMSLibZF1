<?php

namespace Wednesday\Mapping\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Form annotation for Form behavioral extension
 *
 * @Annotation
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author James A Helly <james@wednesday-london.com>
 * @package Wednesday.Mapping.Annotation
 * @subpackage Form
 * @link http://www.wednesday-london.com
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
final class Form extends Annotation
{
    public $renderer = 'default'; // or ["timedatepicker","datepicker","ckeditor","colourpicker","datepicker"]
    public $options  = false;
    public $required = true;
}

