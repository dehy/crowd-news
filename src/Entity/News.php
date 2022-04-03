<?php

namespace App\Entity;

use App\Exception\NonUpdatableException;
use App\Repository\NewsRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NewsRepository::class)]
class News
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\Column(type: 'string', length: 50)]
    private string $title;

    #[ORM\Column(type: 'string', length: 140)]
    private string $abstract;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?DateTimeImmutable $eventDate;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $deletedAt;

    #[ORM\ManyToOne(targetEntity: Newsletter::class, inversedBy: 'news')]
    private ?Newsletter $newsletter;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'news')]
    #[ORM\JoinColumn(nullable: false)]
    private User $author;

    public function __construct() {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->assertCanUpdate();
        $this->title = $title;

        return $this;
    }

    public function getAbstract(): ?string
    {
        return $this->abstract;
    }

    public function setAbstract(string $abstract): self
    {
        $this->assertCanUpdate();
        $this->abstract = $abstract;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->assertCanUpdate();
        $this->content = $content;

        return $this;
    }

    public function getEventDate(): ?DateTimeImmutable
    {
        return $this->eventDate;
    }

    public function setEventDate(?DateTimeImmutable $eventDate): self
    {
        $this->assertCanUpdate();
        $this->eventDate = $eventDate;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->assertCanUpdate();
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function setDeletedAt(?DateTimeImmutable $deletedAt): self
    {
        $this->assertCanUpdate();
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getNewsletter(): ?Newsletter
    {
        return $this->newsletter;
    }

    public function setNewsletter(?Newsletter $newsletter): self
    {
        $this->assertCanUpdate();
        $this->newsletter = $newsletter;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->assertCanUpdate();
        $this->author = $author;

        return $this;
    }

    /**
     * @throws NonUpdatableException
     */
    protected function assertCanUpdate(): void
    {
        if ($this->getNewsletter() && $this->getNewsletter()->isSent()) {
            throw new NonUpdatableException('News is linked to a sent newsletter');
        }
    }
}
