<?php
namespace Wednesday\Models;
//namespace Application\Entities;

use Doctrine\Common\Collections\ArrayCollection,
    Gedmo\Mapping\Annotation AS Gedmo,
	Doctrine\ORM\Mapping AS ORM,
    Wednesday\Mapping\Annotation AS WED;

/**
 * Wednesday\Models\CoreEntity
 * Uses Translatable
 *
 * @ORM\MappedSuperclass
 */
class CoreEntityRich extends CoreEntityAbstract
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

    /**
     * @var string $longtitle
     * @Gedmo\Translatable
     * @ORM\Column(name="longtitle", type="text", nullable=true)
     */
    protected $longtitle;

    /**
     * @var string $summary
     * @Gedmo\Translatable
     * @ORM\Column(name="summary", type="text", nullable=true)
     * @WED\Form(renderer="rich", required=false)
     */
    protected $summary;

    /**
     * @var text $description
     * @Gedmo\Translatable
     * @ORM\Column(name="description", type="text", nullable=true)
     * @WED\Form(renderer="rich", required=false)
     */
    protected $description;

    /**
     * @ORM\ManyToMany(targetEntity="\Wednesday\Models\Categories", cascade={"persist"})
     * @ORM\OrderBy({"title" = "ASC"})
     * @WED\Form(renderer="entitypicker", options="\Wednesday\Models\Categories", required=false)
     */
    protected $categories;

    /**
     * @ORM\ManyToMany(targetEntity="\Wednesday\Models\Tags", cascade={"persist"})
     * @WED\Form(renderer="entitypicker", options="\Wednesday\Models\Tags", required=false)
     */
    protected $tags;

    /**
     * @ORM\ManyToMany(targetEntity="\Wednesday\Models\MetaData", cascade={"persist"})
     * @WED\Form(renderer="entitypicker", options="\Wednesday\Models\MetaData", required=false)
     */
    protected $metadata;

    /**
     * @Gedmo\Locale
     */
    protected $locale;

    /**
     *
     */
    public function __construct() {
        $this->categories = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->metadata = new ArrayCollection();
    }

    /**
     *
     * @param type $locale
     */
    public function setTranslatableLocale($locale)
    {
        $this->locale = strtolower($locale);
    }

    /**
     * Add category
     *
     * @param Categories $categories
     */
    public function addCategory(Categories $category)
    {
        $this->categories->add($category);
    }

    /**
     * Add category
     *
     * @param Categories $categories
     */
    public function removeCategory(Categories $category)
    {
        if($this->categories->contains($category)===false){
            //Throw error.
            return;
        }
        $this->categories->removeElement($category);
    }

    /**
     * Add category
     *
     * @param Categories $categories
     */
    public function listCategories($asids = false)
    {
        $return = "";
        foreach ($this->categories as $key => $category) {
            if($asids) {
                $return .= "".$category->id.",";
            } else {
                $return .= "".$category->title.",";
            }
        }
        return rtrim($return, ",");
    }

    /**
     * Add tags
     *
     * @param Tags $Tags
     */
    public function addTag(Tags $tag)
    {
        $this->tags->add($tag);
    }

    /**
     * Add tag
     *
     * @param Tag $tag
     */
    public function removeTag(Tags $tag)
    {
        if($this->tags->contains($tag)===false){
            //Throw error.
            return;
        }
        $this->tags->removeElement($tag);
    }

    /**
     * Add category
     *
     * @param Categories $categories
     */
    public function listTags()
    {
        $return = "";
        foreach ($this->tags as $key => $tag) {
            $return .= "".$tag->title.",";
        }
        return $return;
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
    public function getMetadataType($key)
    {
        $retmeta = array();
        foreach($this->metadata as $meta){
            if($meta->type == $key) {
                $retmeta[] = $meta;
            }
        }
        return $retmeta;
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
