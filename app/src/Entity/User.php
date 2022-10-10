<?php

namespace App\Entity;

use App\Entity\Traits\UuidTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\ActiveTrait;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use App\Entity\Traits\TimestampableCreatedTrait;
use App\Entity\Traits\TimestampableDeletedTrait;

use App\Entity\Traits\TimestampableUpdatedTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, TwoFactorInterface
{
    use UuidTrait;
    use ActiveTrait;
    use TimestampableCreatedTrait;
    use TimestampableUpdatedTrait;
    use TimestampableDeletedTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column]
    private array $roles = ['ROLE_USER'];


    #[ORM\Column]
    private ?string $password = null;
    private ?string $plainPassword = null;

    #[ORM\Column(type: 'boolean')]
    private $isVerified = false;

    #[ORM\Column(length: 255)]
    private ?string $timezone = null;

    #[ORM\Column(length: 5, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $date_format = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $time_format = null;

    #[ORM\Column(length: 2)]
    private ?string $locale = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Log::class)]
    private Collection $logs;

    #[ORM\Column(name:"2fa_active", type: "boolean")]
    private bool $twoFactor_active = false;

    #[ORM\Column(name:"2fa_secret_google", type:"string", nullable:true)]
    private ?string $twoFactor_secret_google;

    #[ORM\OneToMany(mappedBy: 'User', targetEntity: Email::class)]
    private Collection $emails;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ApiToken::class, orphanRemoval: true)]
    private Collection $apiTokens;





    public function __construct()
    {
        $this->logs = new ArrayCollection();
        $this->emails = new ArrayCollection();
        $this->apiTokens = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }


    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFullName(): ?string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }
    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $password): void
    {
        $this->plainPassword = $password;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getDateFormat(): ?string
    {
        return $this->date_format;
    }

    public function setDateFormat(?string $date_format): self
    {
        $this->date_format = $date_format;

        return $this;
    }

    public function getTimeFormat(): ?string
    {
        return $this->time_format;
    }

    public function setTimeFormat(?string $time_format): self
    {
        $this->time_format = $time_format;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function __toString()
    {
        return (string) $this->getUsername();
    }

    /**
     * @return Collection<int, Log>
     */
    public function getLogs(): Collection
    {
        return $this->logs;
    }

    public function addLog(Log $log): self
    {
        if (!$this->logs->contains($log)) {
            $this->logs->add($log);
            $log->setUser($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Email>
     */
    public function getEmails(): Collection
    {
        return $this->emails;
    }

    public function addEmail(Email $email): self
    {
        if (!$this->emails->contains($email)) {
            $this->emails->add($email);
            $email->setUser($this);
        }

        return $this;
    }

    public function removeEmail(Email $email): self
    {
        if ($this->emails->removeElement($email)) {
            // set the owning side to null (unless already changed)
            if ($email->getUser() === $this) {
                $email->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ApiToken>
     */
    public function getApiTokens(): Collection
    {
        return $this->apiTokens;
    }

    public function addApiToken(ApiToken $apiToken): self
    {
        if (!$this->apiTokens->contains($apiToken)) {
            $this->apiTokens->add($apiToken);
            $apiToken->setUser($this);
        }

        return $this;
    }

    public function removeApiToken(ApiToken $apiToken): self
    {
        if ($this->apiTokens->removeElement($apiToken)) {
            // set the owning side to null (unless already changed)
            if ($apiToken->getUser() === $this) {
                $apiToken->setUser(null);
            }
        }

        return $this;
    }


    /*
     * ====== 2FA
     */

    public function isTwoFactorEnabled(): bool
    {
        return ($this->twoFactor_active == true);
    }

    public function setTwoFactorEnabled(?bool $twoFactor_active): void
    {
        $this->twoFactor_active = $twoFactor_active;
    }

    
    public function isGoogleAuthenticatorEnabled(): bool
    {
        return (null !== $this->twoFactor_secret_google) and $this->isTwoFactorEnabled();
    }

    public function getGoogleAuthenticatorUsername(): string
    {
        return $this->username;
    }

    public function getGoogleAuthenticatorSecret(): ?string
    {
        return $this->twoFactor_secret_google;
    }

    public function setGoogleAuthenticatorSecret(?string $googleAuthenticatorSecret): void
    {
        $this->twoFactor_secret_google = $googleAuthenticatorSecret;
    }


    
}
