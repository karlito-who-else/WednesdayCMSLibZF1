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
 * Description of CategoriesRepository
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
  @author jameshelly
 */
class CategoriesRepository extends NestedTreeRepository {
    #TODO add extra methods to account for other orders and searching.

    public function getInOrder($ids) {
        $ordered = array();
        foreach ($ids as $id) {
            $ordered[] = $this->find($id);
        }
        return $ordered;
    }

    public function getUltimateParent($node,$depth=3) {
        $parent = NULL; // there should only be one parent.
        $ents = NULL;
        foreach ($node as $children) {
            //$siteroot = $cnf['settings']['application']['siteroot'];
            $log = $this->getLog();
            $ents = $this->getPathQuery($children)->getResult();
//            $ents = $this->childrenHierarchy($node);
//            foreach ($ents as $key => $value) {
//                echo $value->title.'<br/>';
//                $log->debug(get_class($this)."::getUltimateParent('".$key."::".$value->title."')");
//            }
            $log->debug(get_class($this) . "::getUltimateParent('" . $children->title . "')");
//            $log->debug($kids);
//            return $ents[2];
            if ($ents[$depth] != $parent) {
                $parent = $ents[$depth];
            }

        }
        if(!isset($parent)) {
            if ($ents[$depth-1] != $parent)
                $parent = $ents[$depth-1];

        }
        return $parent;
    }
    
 	public function getUniqueCategories(){
    	return $this->_em->createQueryBuilder()
    				->select('DISTINCT c.title, c.id')
					->from($this->_entityName, 'c')
					->orderBy('c.title', 'ASC')
					->getQuery()
					->execute();
    }

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
    
    private function getLog() {
        $front = Front::getInstance();
        $bootstrap = $front->getParam("bootstrap");
        return $bootstrap->getResource('Log');
    }

    private function getConfig() {
        $front = Front::getInstance();
        $bootstrap = $front->getParam("bootstrap");
        return $bootstrap->getContainer()->get('config');
    }
 
}