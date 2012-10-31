<?php
namespace Wednesday\Models;
//namespace Application\Entities;

use Doctrine\ORM\Query,
    Doctrine\ORM\Proxy\Proxy,
    Doctrine\ORM\EntityRepository,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Mapping\ClassMetadata;
use \Zend_Controller_Front as Front;

/**
 * Description of TagsRepository
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
  @author jameshelly
 */
class TagsRepository extends EntityRepository {
    #TODO add extra methods to account for other orders and searching.

//    /**
//     * Allows the following 'virtual' methods:
//     * - searchFor($node)
//     * - persistAsFirstChildOf($node, $parent)
//     * - persistAsLastChild($node)
//     * - persistAsLastChildOf($node, $parent)
//     * - persistAsNextSibling($node)
//     * - persistAsNextSiblingOf($node, $sibling)
//     * - persistAsPrevSibling($node)
//     * - persistAsPrevSiblingOf($node, $sibling)
//     * Inherited virtual methods:
//     * - find*
//     *
//     * @see \Doctrine\ORM\EntityRepository
//     * @throws InvalidArgumentException - If arguments are invalid
//     * @throws BadMethodCallException - If the method called is an invalid find* or persistAs* method
//     *      or no find* either persistAs* method at all and therefore an invalid method call.
//     * @return mixed - TreeNestedRepository if persistAs* is called
//     */
//    public function __call($method, $args)
//    {
////        if (substr($method, 0, 9) === 'persistAs') {
////            if (!isset($args[0])) {
////                throw new \Gedmo\Exception\InvalidArgumentException('Node to persist must be available as first argument');
////            }
////            $node = $args[0];
////            $wrapped = new EntityWrapper($node, $this->_em);
////            $meta = $this->getClassMetadata();
////            $config = $this->listener->getConfiguration($this->_em, $meta->name);
////            $position = substr($method, 9);
////            if (substr($method, -2) === 'Of') {
////                if (!isset($args[1])) {
////                    throw new \Gedmo\Exception\InvalidArgumentException('If "Of" is specified you must provide parent or sibling as the second argument');
////                }
////                $parent = $args[1];
////                $wrapped->setPropertyValue($config['parent'], $parent);
////                $position = substr($position, 0, -2);
////            }
////            $wrapped->setPropertyValue($config['left'], 0); // simulate changeset
////            $oid = spl_object_hash($node);
////            $this->listener
////                ->getStrategy($this->_em, $meta->name)
////                ->setNodePosition($oid, $position);
////
////            $this->_em->persist($node);
////            return $this;
////        }
//        return parent::__call($method, $args);
//    }    

    public function searchFor($field, $query) {
        $qb = $this->_em->createQueryBuilder();
        $query = $this->_em->createQueryBuilder()
                ->select("n")
                ->from($this->_entityName, 'n')
                ->add('where', $qb->expr()->like('n.'.$field, $qb->expr()->literal('%'.$query.'%')))
//                ->setFirstResult($page)
//                ->setMaxResults($limit)
                ->getQuery();
        return $query->execute();        
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