<?php

namespace Wednesday\Lucenable\Mapping\Event\Adapter;

use Gedmo\Mapping\Event\Adapter\ORM as BaseAdapterORM;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Query;
use Wednesday\Lucenable\Mapping\Event\LucenableAdapter;

/**
 *
 * Doctrine event adapter for ORM adapted for Lucenable behavior
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
 * @author mrhelly
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
  @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @package Wednesday\Lucenable\Mapping\Event\Adapter
 * @subpackage ORM
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
final class ORM extends BaseAdapterORM implements LucenableAdapter
{
    // Nothing specific yet
}
