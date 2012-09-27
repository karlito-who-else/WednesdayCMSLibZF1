<?php
namespace Wednesday\Models;
//namespace Application\Entities;

use Doctrine\ORM\Mapping AS ORM;

/**
 * MetaData
 *
 * @ORM\Table(name="metadata")
 * @ORM\Entity(repositoryClass="Wednesday\Models\ManageRepository")
 */
class MetaData extends CoreEntityAbstract {

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
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    protected $content;

    /**
     * @var string $type
     *
     * @ORM\Column(name="type", type="string", length=128, nullable=false)
     */
    protected $type;

}