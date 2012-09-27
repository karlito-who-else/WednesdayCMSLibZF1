<?php
namespace Wednesday\Models;
//namespace Application\Entities;

use Gedmo\Mapping\Annotation AS Gedmo,
    Wednesday\Mapping\Annotation AS WED,
    Doctrine\ORM\Mapping AS ORM,
    Doctrine\Common\Collections\ArrayCollection;

/**
 * Categories
 *
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="categories")
 * @ORM\Entity(repositoryClass="Wednesday\Models\CategoriesRepository")
 */
class Categories extends CoreEntityAbstract {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @WED\Form(renderer="disabled", required=true)
     */
    protected $id;

    /**
     * @var string $title
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     * @WED\Form(renderer="text", required=true)
     */
    protected $title;

    /**
     * @var text $description
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @WED\Form(renderer="rich", required=true)
     */
    protected $description;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     * @WED\Form(renderer="none", required=false)
     */
    protected $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     * @WED\Form(renderer="none", required=false)
     */
    protected $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     * @WED\Form(renderer="none", required=false)
     */
    protected $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer")
     * @WED\Form(renderer="none", required=false)
     */
    protected $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="\Wednesday\Models\Categories", inversedBy="children")
     * @WED\Form(renderer="entitypicker", options="\Wednesday\Models\Categories", required=false)
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="\Wednesday\Models\Categories", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     * @WED\Form(renderer="none", required=false)
     */
    protected $children;
    
    /**
     * @var string $sortorder
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $sortorder;

    /**
     *
     * @param type $name
     */
    public function __construct() {
//        $this->children = new ArrayCollection();
    }

}
