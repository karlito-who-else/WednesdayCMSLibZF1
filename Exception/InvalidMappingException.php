<?php

namespace Wednesday\Exception;

use Wednesday\Exception;

/**
 * InvalidMappingException
 * 
 * Triggered when mapping user argument is not
 * valid or incomplete.
 * 
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author James A Helly <james@wednesday-london.com>,  Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @package Wednesday.Exception
 * @subpackage InvalidMappingException
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class InvalidMappingException 
    extends InvalidArgumentException
    implements Exception
{}