<?php
namespace Wednesday\Restable;
//namespace Application\Entities;
//namespace Wednesday\Models;

use Wednesday\Models\Categories,
    Wednesday\Models\Tags,
    Wednesday\Models\MetaData,
    Wednesday\Models\CoreEntityAbstract,
    Gedmo\Translatable\Translatable,
    Doctrine\Common\Collections\ArrayCollection,
    Gedmo\Mapping\Annotation AS Gedmo,
	Doctrine\ORM\Mapping AS ORM,
    Wednesday\Mapping\Annotation AS WED;

/**
 * CoreItems
 *
 * @ORM\MappedSuperclass
 */
class Entity extends CoreEntityAbstract
{
   /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string $title
     * @Gedmo\Translatable
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    protected $title;

}
