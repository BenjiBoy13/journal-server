<?php


namespace Server\Models;



use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Server\Repositories\JournalRepository")
 * @ORM\Table(name="journal")
 */
class JournalEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="jrnl_id")
     * @ORM\GeneratedValue
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", name="jrnl_title")
     */
    private string $title;

    /**
     * @ORM\Column(type="text", name="jrnl_content")
     */
    private string $content;

    /**
     * @ORM\ManyToOne(targetEntity="UserEntity", inversedBy="journals")
     * @ORM\JoinColumn(name="jrnl_usr_id", referencedColumnName="usr_id")
     */
    private UserEntity $user;

    /**
     * @ORM\Column(type="date", name="jrnl_creation_date")
     */
    private DateTime $creationDate;

    /**
     * @ORM\Column(type="boolean", name="jrnl_share")
     */
    private bool $share = false;

    /**
     * @return int
     */
    public function getId ()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return UserEntity
     */
    public function getUser(): UserEntity
    {
        return $this->user;
    }

    /**
     * @param UserEntity $user
     */
    public function setUser(UserEntity $user): void
    {
        $this->user = $user;
    }

    /**
     * @return DateTime
     */
    public function getCreationDate(): DateTime
    {
        return $this->creationDate;
    }

    /**
     * @param DateTime $creationDate
     */
    public function setCreationDate(DateTime $creationDate): void
    {
        $this->creationDate = $creationDate;
    }

    public function getShare (): bool
    {
        return $this->share;
    }

    public function setShare (bool $share)
    {
        $this->share = $share;
    }
}