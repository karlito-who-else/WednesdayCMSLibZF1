<?php
namespace Wednesday\Lucenable\Mapping\Driver;

use Gedmo\Mapping\Driver\AnnotationDriverInterface,
    Doctrine\Common\Persistence\Mapping\ClassMetadata,
    Gedmo\Exception\InvalidMappingException;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Annotation
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
 * @author mrhelly
 */
class Annotation  implements AnnotationDriverInterface {

    /**
     * Annotation to mark field as lucenable and include it in slug building.
     */
    const LUCENABLE = 'Wednesday\\Mapping\\Annotation\\Lucenable';

    /**
     * Annotation to identify field as one which has an indexable variable.
     */
    const LUCENEINDEX = 'Wednesday\\Mapping\\Annotation\\LuceneIndex';

    /**
     * {@inheritDoc}
     */
    public function setAnnotationReader($reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritDoc}
     */
    public function validateFullMetadata(ClassMetadata $meta, array $config)
    {
        if ($config) {
            if (!isset($config['fields'])) {
                throw new InvalidMappingException("Unable to find any sluggable fields specified for Sluggable entity - {$meta->name}");
            }
            foreach ($config['fields'] as $slugField => $fields) {
                if (!isset($config['slugFields'][$slugField])) {
                    throw new InvalidMappingException("Unable to find {$slugField} slugField specified for Sluggable entity - {$meta->name}, you should specify slugField annotation property");
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function readExtendedMetadata(ClassMetadata $meta, array &$config) {

    }

    /**
     * Passes in the mapping read by original driver
     *
     * @param $driver
     * @return void
     */
    public function setOriginalDriver($driver)
    {
        $this->_originalDriver = $driver;
    }

}
