<?php

namespace Wednesday\Lucenable;

use Doctrine\Common\EventSubscriber;
use Gedmo\Mapping\MappedEventSubscriber;
use Doctrine\Common\EventArgs;

//use Doctrine\Common\Annotations\AnnotationReader;
//use Doctrine\Common\Annotations\CachedReader;
//use Doctrine\Common\Cache\ArrayCache;
//use Doctrine\Common\Annotations\Reader;
//use Doctrine\Common\Persistence\ObjectManager;
//use Doctrine\Common\Persistence\Mapping\ClassMetadata;


/**
 * Description of LucenableListener
 * This interface is not necessary but can be implemented for
 * Entities which in some cases needs to be identified as
 * Lucenable
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
 * @author mrhelly
 * @package Wednesday.LucenableListener
 * @subpackage LucenableListener
 */
class LucenableListener extends MappedEventSubscriber {

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents(){
        return array(
            'onFlush',
            'loadClassMetadata'
        );
    }

    /**
     * Mapps additional metadata
     *
     * @param EventArgs $eventArgs
     * @return void
     */
    public function loadClassMetadata(EventArgs $eventArgs)
    {
        $ea = $this->getEventAdapter($eventArgs);
        $this->loadMetadataForObjectClass($ea->getObjectManager(), $eventArgs->getClassMetadata());
    }
    /**
     * Generate slug on objects being updated during flush
     * if they require changing
     *
     * @param EventArgs $args
     * @return void
     */
    public function onFlush(EventArgs $args)
    {
        $ea = $this->getEventAdapter($args);
        $om = $ea->getObjectManager();
        $uow = $om->getUnitOfWork();

//        // process all objects being inserted, using scheduled insertions instead
//        // of prePersist in case if record will be changed before flushing this will
//        // ensure correct result. No additional overhead is encoutered
//        foreach ($ea->getScheduledObjectInsertions($uow) as $object) {
//            $meta = $om->getClassMetadata(get_class($object));
//            if ($config = $this->getConfiguration($om, $meta->name)) {
//                // generate first to exclude this object from similar persisted slugs result
//                $this->generateSlug($ea, $object);
//                foreach ($config['fields'] as $slugField => $fieldsForSlugField) {
//                    $slug = $meta->getReflectionProperty($slugField)->getValue($object);
//                    $this->persistedSlugs[$config['useObjectClass']][$slugField][] = $slug;
//                }
//            }
//        }
//        // we use onFlush and not preUpdate event to let other
//        // event listeners be nested together
//        foreach ($ea->getScheduledObjectUpdates($uow) as $object) {
//            $meta = $om->getClassMetadata(get_class($object));
//            if ($config = $this->getConfiguration($om, $meta->name)) {
//                foreach ($config['slugFields'] as $slugField) {
//                    if ($slugField['updatable']) {
//                        $this->generateSlug($ea, $object);
//                    }
//                }
//            }
//        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getNamespace()
    {
        return __NAMESPACE__;
    }
}
