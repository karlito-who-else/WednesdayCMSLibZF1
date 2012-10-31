<?php

namespace Wednesday\Mapping\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Description of LuceneIndex
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
 * @author mrhelly
 */
final class LuceneIndex extends Annotation {
    public $indexes = 'default';
    public $type = 'keyword'; // or ["keyword","unindexed","binary","text","unstored"]
    public $follow = false;
}
