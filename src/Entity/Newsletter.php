<?php

namespace App\Entity;

use App\Repository\NewsletterRepository;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NewsletterRepository::class)]
class Newsletter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\OneToMany(mappedBy: 'newsletter', targetEntity: News::class)]
    private Collection $news;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private DateTimeImmutable $sendAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $sentAt;

    public function __construct()
    {
        // next monday, 6 o'clock
        $this->sendAt = new DateTimeImmutable('next monday 6:00', new DateTimeZone('CET'));

        $this->news = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
        if (!$this->news->contains($news)) {
            $this->news[] = $news;
            $news->setNewsletter($this);
        }

        return $this;
    }

    public function removeNews(News $news): self
    {
        if ($this->news->removeElement($news)) {
            // set the owning side to null (unless already changed)
            if ($news->getNewsletter() === $this) {
                $news->setNewsletter(null);
            }
        }

        return $this;
    }

    public function getSendAt(): ?DateTimeImmutable
    {
        return $this->sendAt;
    }

    public function setSendAt(?DateTimeImmutable $sendAt): self
    {
        $this->sendAt = $sendAt;

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
        $this->sentAt = $sentAt;

        return $this;
    }
}
