<?php


namespace Server\Models;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * @ORM\Entity(repositoryClass="Server\Repositories\UserRepository")
 * @ORM\Table(name="user")
 */
class UserEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="usr_id")
     * @ORM\GeneratedValue
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", name="usr_nickname")
     */
    private string $nickname;

    /**
     * @ORM\Column(type="string", name="usr_password")
     */
    private string $password;

    /**
     * @ORM\Column(type="string", name="usr_email")
     */
    private string $email;

    /**
     * @ORM\Column(type="date", name="usr_creation_date")
     */
    private DateTime $creationDate;

    /**
     * @ORM\Column(type="text", name="usr_token", nullable=true)
     */
    private ?string $token;

    /**
     * @ORM\Column(type="boolean", name="usr_email_verfified")
     */
    private bool $emailVerified = false;

    /**
     * @ORM\Column(type="string", name="usr_login_provider")
     */
    private string $loginProvider = "diary_app_provider";

    /**
     * @ORM\OneToMany(targetEntity="JournalEntity", mappedBy="user")
     */
    private PersistentCollection $journals;

    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getNickname() : string
    {
        return $this->nickname;
    }

    /**
     * @param string $nickname
     */
    public function setNickname(string $nickname) : void
    {
        $this->nickname = $nickname;
    }

    /**
     * @return string
     */
    public function getPassword() : string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password) : void
    {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getEmail() : string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email) : void
    {
        $this->email = $email;
    }

    /**
     * @return DateTime
     */
    public function getCreationDate() : DateTime
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

    /**
     * @return string
     */
    public function getToken ()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken (string $token)
    {
        $this->token = $token;
    }

    /**
     * @return bool
     */
    public function getEmailVerified ()
    {
        return $this->emailVerified;
    }

    /**
     * @param bool $emailVerified
     */
    public function setEmailVerified (bool $emailVerified)
    {
        $this->emailVerified = $emailVerified;
    }

    /**
     * @return string
     */
    public function getLoginProvider ()
    {
        return $this->loginProvider;
    }

    /**
     * @param string $loginProvider
     */
    public function setLoginProvider (string $loginProvider)
    {
        $this->loginProvider = $loginProvider;
    }

    /**
     * @return PersistentCollection
     */
    public function getJournals() : PersistentCollection
    {
        return $this->journals;
    }
}