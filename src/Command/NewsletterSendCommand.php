<?php

namespace App\Command;

use App\Entity\Newsletter;
use App\Entity\User;
use App\Repository\NewsletterRepository;
use Doctrine\ORM\EntityManagerInterface;
use IntlDateFormatter;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;
use Twig\Environment;

#[AsCommand(
    name: 'app:newsletter:send',
    description: 'Send the newsletter',
)]
class NewsletterSendCommand extends Command
{
    public function __construct(protected EntityManagerInterface $entityManager, protected Environment $twig, protected MailerInterface $mailer, string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Run like if really sending the newsletter, but don\'t.')
        ;
    }

    /**
     * @throws \Twig\Error\SyntaxError
     * @throws \Twig\Error\RuntimeError
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Twig\Error\LoaderError
     * @throws \Doctrine\ORM\NoResultException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $users = $this->entityManager->getRepository(User::class)->findAll();

        /** @var NewsletterRepository $newsletterRepository */
        $newsletterRepository = $this->entityManager->getRepository(Newsletter::class);
        $newsletter = $newsletterRepository->findNext();

        $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::NONE, 'Europe/Paris');

        // TODO use Inky https://symfony.com/doc/current/mailer.html#inky-email-templating-language
        $email = (new TemplatedEmail())
            ->from('no-reply@admds.net')
            ->subject(sprintf('📰 Infolettre des entrepreneurs, %s', $formatter->format(time())))
            ->htmlTemplate('newsletter/themes/default.html.twig')
            ->context(['newsletter' => $newsletter]);

        foreach ($users as $user) {
            $email->addTo(new Address($user->getEmail(), $user->__toString()));
        }

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $io->error('Cannot send email');
            $io->error($e->getMessage());
        }

        $io->success('Newsletter sent!');

        return Command::SUCCESS;
    }
}
