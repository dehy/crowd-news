<?php

namespace App\Tests\Service;

use App\Entity\News;
use App\Entity\User;
use App\Service\NewsletterService;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PHPUnit\Framework\TestCase;

class NewsletterServiceTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testNextNewsletterDate(EntityManagerInterface $entityManager): void
    {
        $newsletterService = new NewsletterService($entityManager);
        $next = new DateTime('next monday 6am');

        static::assertEquals($next, $newsletterService->nextNewsletterDate());
    }

    public function testNextNewsletter(): void
    {
        $author = (new User())
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setEmail('john.doe@invalid.com');

        $news1 = (new News())
            ->setAuthor($author)
            ->setTitle('Title1')
            ->setAbstract('Abstract1')
            ->setContent('Content1');

        $news2 = (new News())
            ->setAuthor($author)
            ->setTitle('Title2')
            ->setAbstract('Abstract2')
            ->setContent('Content2');

        $newsletterService = new NewsletterService();
        $newsletter = $newsletterService->nextNewsletter();

        $nextDate = new DateTime('next monday 6am');
        static::assertEquals($nextDate, $newsletter->getScheduledFor());
        static::assertCount(2, $newsletter->getNews());
    }
}
