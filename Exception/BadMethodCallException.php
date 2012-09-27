<?php

namespace Wednesday\Exception;

use Wednesday\Exception;

/**
 * BadMethodCallException
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author James A Helly <james@wednesday-london.com>,  Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @package Wednesday.Exception
 * @subpackage BadMethodCallException
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class BadMethodCallException
    extends \BadMethodCallException
    implements Exception
{}
