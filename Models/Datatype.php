<?php
namespace Wednesday\Models;
//namespace Application\Entities;

use Gedmo\Mapping\Annotation AS Gedmo,
    Doctrine\ORM\Mapping AS ORM,
    Doctrine\Common\Collections\ArrayCollection;

/**
 * Settings
 *
 * @ORM\Table(name="datatypes")
 * @ORM\Entity(repositoryClass="Wednesday\Models\ManageRepository")
 */
class Datatype extends CoreEntityAbstract {

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
     *
     */
    public function __construct() {
//        $this->categories = new ArrayCollection();
    }
}