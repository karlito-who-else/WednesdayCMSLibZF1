<?php
namespace Wednesday\Models;
//namespace Application\Entities;

use Doctrine\Common\Collections\ArrayCollection,
    Doctrine\ORM\Mapping AS ORM,
    Gedmo\Mapping\Annotation AS Gedmo,
    Wednesday\Mapping\Annotation AS WED;

/**
 * Settings
 *
 * @ORM\Table(name="settings")
 * @ORM\Entity(repositoryClass="Wednesday\Models\ManageRepository")
 */
class Settings extends CoreEntityAbstract {

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
     * @var text $content
     *
     * @ORM\Column(name="content", type="object", nullable=true)
     */
    protected $content;

    /**
     * @ORM\ManyToMany(targetEntity="\Wednesday\Models\Categories")
     * @ORM\OrderBy({"lft" = "ASC"})
     * @WED\Form(renderer="entitypicker", options="\Wednesday\Models\Categories", required=false)
     */
    protected $categories;

    /**
     * @ORM\ManyToOne(targetEntity="\Wednesday\Models\Datatype")
     * @WED\Form(renderer="none", required=false)
     */
    protected $datatype;

    /**
     *
     */
    public function __construct() {
        $this->categories = new ArrayCollection();
    }

}