<?php

namespace App\Entity;

use App\Exception\NonUpdatableException;
use App\Repository\NewsletterRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NewsletterRepository::class)]
class Newsletter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToMany(mappedBy: 'newsletter', targetEntity: News::class)]
    private Collection $news;

    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    private DateTimeImmutable $scheduledFor;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $sentAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $generatedHtml = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $generatedTxt = null;

    public function __construct()
    {
        $this->news = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isScheduled(): bool
    {
        return $this->getId() === null;
    }

    /**
     * @return Collection<int, News>
     */
    public function getNews(): Collection
    {
        return $this->news;
    }

    public function addNews(News $news): self
    {
        $this->assertCanUpdate();
        if (!$this->news->contains($news)) {
            $this->news[] = $news;
            $news->setNewsletter($this);
        }

        return $this;
    }

    public function removeNews(News $news): self
    {
        $this->assertCanUpdate();
        if ($this->news->removeElement($news)) {
            // set the owning side to null (unless already changed)
            if ($news->getNewsletter() === $this) {
                $news->setNewsletter(null);
            }
        }

        return $this;
    }

    public function getScheduledFor(): ?DateTimeImmutable
    {
        return $this->scheduledFor;
    }

    public function setScheduledFor(?DateTimeImmutable $scheduledFor): self
    {
        $this->assertCanUpdate();
        $this->scheduledFor = $scheduledFor;

        return $this;
    }

    public function getSentAt(): ?DateTimeImmutable
    {
        return $this->sentAt;
    }

    public function isSent(): bool
    {
        return $this->sentAt !== null;
    }

    public function setSentAt(?DateTimeImmutable $sentAt): self
    {
        $this->assertCanUpdate();
        $this->sentAt = $sentAt;

        return $this;
    }

    public function getGeneratedHtml(?User $user = null): ?string
    {
        return $this->replaceTokens($this->generatedHtml, $user);
    }

    public function setGeneratedHtml(?string $generatedHtml): self
    {
        $this->assertCanUpdate();
        $this->generatedHtml = $generatedHtml;

        return $this;
    }

    public function getGeneratedTxt(?User $user = null): ?string
    {
        return $this->replaceTokens($this->generatedTxt, $user);;
    }

    public function setGeneratedTxt(?string $generatedTxt): self
    {
        $this->assertCanUpdate();
        $this->generatedTxt = $generatedTxt;

        return $this;
    }

    /**
     * @throws NonUpdatableException
     */
    protected function assertCanUpdate(): void
    {
        if ($this->getSentAt() !== null) {
            throw new NonUpdatableException('Newsletter already sent');
        }
    }

    public function isEditable(): bool
    {
        return $this->getSentAt() === null;
    }

    public function getStatus(): string
    {
        if ($this->isSent()) {
            return 'sent';
        }
        if ($this->getGeneratedHtml()) {
            return 'in_pipeline';
        }
        return 'draft';
    }

    protected function replaceTokens(string $content, ?User $user): string
    {
        if ($user !== null) {
            $content = str_replace('%%user.firstname%%', $user->getFirstname(), $content);
        }
        return $content;
    }
}
