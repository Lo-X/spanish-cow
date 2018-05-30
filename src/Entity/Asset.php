<?php
/**
 * This file is part of the spanish-cow project.
 *
 * (c) Nvision S.A.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Created by PhpStorm.
 * User: loic
 * Date: 03/04/18
 * Time: 23:01
 */

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\Timestampable\Timestampable;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AssetRepository")
 * @ORM\Table(name="asset__asset")
 * @ApiResource(
 *     itemOperations={
 *         "get"={"access_control"="object.isAssociatedToProject(user)", "access_control_message"="Domain not found."},
 *         "put"={"access_control"="object.isAssociatedToProject(user)", "access_control_message"="Domain not found."},
 *         "delete"={"access_control"="object.isAssociatedToProject(user) and is_granted('ROLE_ADMIN')", "access_control_message"="Asset not found."}
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={"domain": "exact"})
 */
class Asset
{
    use Timestampable;

    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id", nullable=false)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="resname", nullable=false)
     */
    protected $resname;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="source", nullable=false)
     */
    protected $source;

    /**
     * @var string
     *
     * @ORM\Column(type="text", name="notes", nullable=true)
     */
    protected $notes;

    /**
     * @var Domain
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Domain", inversedBy="assets")
     * @ORM\JoinColumn(name="domain_id", referencedColumnName="id")
     */
    protected $domain;

    /**
     * @var Translation[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Translation", mappedBy="asset", fetch="EAGER", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"locale": "ASC"})
     */
    protected $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function isAssociatedToProject(User $user)
    {
        if (!$this->getDomain()) {
            return false;
        }

        return $this->getDomain()->isAssociatedToProject($user);
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return sha1($this->getResname());
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getResname(): ?string
    {
        return $this->resname;
    }

    /**
     * @param string $resname
     *
     * @return Asset
     */
    public function setResname($resname)
    {
        $this->resname = $resname;

        if (null === $this->source) {
            $this->source = $resname;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getSource(): ?string
    {
        return $this->source;
    }

    /**
     * @param string $source
     *
     * @return Asset
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return string
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * @param string $notes
     *
     * @return Asset
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return Domain
     */
    public function getDomain(): ?Domain
    {
        return $this->domain;
    }

    /**
     * @param Domain $domain
     *
     * @return Asset
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @return Translation[]
     */
    public function getTranslations(): \Traversable
    {
        return $this->translations;
    }

    /**
     * @param Translation[] $translations
     *
     * @return Asset
     */
    public function setTranslations($translations)
    {
        foreach ($this->translations as $translation) {
            $this->removeTranslation($translation);
        }

        $this->translations = new ArrayCollection();

        foreach ($translations as $translation) {
            $this->addTranslation($translation);
        }

        return $this;
    }

    /**
     * @param Translation $translation
     *
     * @return $this
     */
    public function addTranslation(Translation $translation)
    {
        if (!$this->translations->contains($translation)) {
            $translation->setAsset($this);
            $this->translations->add($translation);
        }

        return $this;
    }

    /**
     * @param Translation $translation
     *
     * @return $this
     */
    public function removeTranslation(Translation $translation)
    {
        if ($this->translations->contains($translation)) {
            $translation->setAsset(null);
            $this->translations->removeElement($translation);
        }

        return $this;
    }
}
