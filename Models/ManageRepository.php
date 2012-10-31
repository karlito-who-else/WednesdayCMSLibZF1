<?php
namespace Wednesday\Models;
//namespace Application\Entities;

use Doctrine\ORM\Query,
    Doctrine\ORM\Proxy\Proxy,
    Doctrine\ORM\EntityRepository;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository,
    Gedmo\Tool\Wrapper\EntityWrapper,
    Gedmo\Tree\Strategy,
    Gedmo\Tree\Strategy\ORM\Nested,
    Gedmo\Exception\InvalidArgumentException;
use \Zend_Controller_Front as Front;

/**
 * Description of ManageRepository
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
  @author jamesahelly
 */
class ManageRepository extends EntityRepository {
    public function getEntityItems($sortOption = 'title', $sortDir = 'asc', $keyword = '') {
        
        if(is_null($sortOption)) $sortOption = 'title';
        if(is_null($sortDir)) $sortDir = 'asc';
        
        $query = $this->_em->createQueryBuilder()
                ->select("n")
                ->from($this->_entityName, 'n')
                ->orderBy('n.' . $sortOption, $sortDir);
        
        if(!empty($keyword)){
            $query->where('n.title like :keyword')
                  ->setParameters(array('keyword'=>'%'.$keyword.'%'));
        }        

        return $query->getQuery()->execute();
    }

}