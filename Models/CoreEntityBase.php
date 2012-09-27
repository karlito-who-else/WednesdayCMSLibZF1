<?php
namespace Wednesday\Models;
//namespace Application\Entities;

use Doctrine\Common\Collections\ArrayCollection,
    Gedmo\Mapping\Annotation AS Gedmo,
	Doctrine\ORM\Mapping AS ORM,
    Wednesday\Mapping\Annotation AS WED;

/**
 * Wednesday\Models\CoreEntity
 *
 * @ORM\MappedSuperclass
 */
class CoreEntityBase extends CoreEntityAbstract
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
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    protected $title;

    /**
     * @var text $description
     * @ORM\Column(name="description", type="text", nullable=true)
     * @WED\Form(renderer="rich", required=false)
     */
    protected $description;

    /**
     * @ORM\ManyToMany(targetEntity="\Wednesday\Models\MetaData")
     * @WED\Form(renderer="entitypicker", options="\Wednesday\Models\MetaData", required=false)
     */
    protected $metadata;


    /**
     *
     */
    public function __construct() {
        $this->metadata = new ArrayCollection();
    }

    /**
     * Add MetaData
     *
     * @param MetaData $metadata
     */
    public function getMetadata($key)
    {
        foreach($this->metadata as $meta){
            if($meta->title == $key) {
                return $meta;
            }
        }
        return false;
    }

    /**
     * Add MetaData
     *
     * @param MetaData $metadata
     */
    public function addMetadata(MetaData $metadata)
    {
        $this->metadata->add($metadata);
    }

    /**
     * Add MetaData
     *
     * @param MetaData $metadata
     */
    public function removeMetadata(MetaData $metadata)
    {
        if($this->metadata->contains($metadata)===false){
            //Throw error.
            return;
        }
        $this->metadata->removeElement($metadata);
    }

/*    */
}
