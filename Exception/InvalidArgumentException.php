<?php

namespace Wednesday\Exception;

use Wednesday\Exception;

/**
 * InvalidArgumentException
 * 
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author James A Helly <james@wednesday-london.com>,  Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @package Wednesday.Exception
 * @subpackage InvalidArgumentException
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class InvalidArgumentException 
    extends \InvalidArgumentException
    implements Exception
{}