<?php

namespace App\Service;

use App\Entity\News;
use App\Entity\Newsletter;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class NewsletterService
{
    public function __construct(protected EntityManagerInterface $entityManager) {

    }

    public function lastChanceToSubmitDate(): DateTimeImmutable {
        return new DateTimeImmutable('next sunday 6pm');
    }

    public function isTooLateForThisWeek(): bool {
        $diff = $this->nextNewsletterDate()->diff(new DateTimeImmutable('now'));
        if ($diff->y === 0 || $diff->m === 0 || $diff->d === 0 || $diff->h < 12) {
            return false;
        }
        return true;
    }

    /**
     * @throws Exception
     */
    public function nextNewsletterDate(): DateTimeImmutable {
        return new DateTimeImmutable('next monday 6am');
    }

    /**
     * @throws Exception
     */
    public function nextNewsletter(): Newsletter {
        $news = $this->entityManager->getRepository(News::class)->findBy([
            'newsletter' => null,
        ]);

        $newsletter = (new Newsletter())
            ->setScheduledFor($this->nextNewsletterDate());

        foreach ($news as $aNews) {
            $newsletter->addNews($aNews);
        }

        return $newsletter;
    }
}