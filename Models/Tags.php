<?php
namespace Wednesday\Models;
//namespace Application\Entities;

use Gedmo\Mapping\Annotation AS Gedmo,
    Doctrine\ORM\Mapping AS ORM,
    Wednesday\Mapping\Annotation AS WED,
    Doctrine\Common\Collections\ArrayCollection;

/**
 * Tags
 *
 * @ORM\Table(name="tags")
 * @ORM\Entity(repositoryClass="Wednesday\Models\TagsRepository")
 */
class Tags extends CoreEntityAbstract {

    /**
     * @var integer $id
     *
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
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @WED\Form(renderer="rich", required=false)
     */
    protected $description;

    /**
     * @ORM\ManyToMany(targetEntity="\Wednesday\Models\Categories")
     * @ORM\OrderBy({"lft" = "ASC"})
     * @WED\Form(renderer="entitypicker", options="\Wednesday\Models\Categories", required=false)
     */
    protected $categories;

    /**
     *
     */
    public function __construct() {
        $this->categories = new ArrayCollection();
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
}