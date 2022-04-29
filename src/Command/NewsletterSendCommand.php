<?php

namespace App\Command;

use App\Entity\Newsletter;
use App\Entity\User;
use App\Repository\NewsletterRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use IntlDateFormatter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

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
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws NonUniqueResultException
     * @throws LoaderError
     * @throws NoResultException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $users = $this->entityManager->getRepository(User::class)->findAll();

        /** @var NewsletterRepository $newsletterRepository */
        $newsletterRepository = $this->entityManager->getRepository(Newsletter::class);
        $newsletter = $newsletterRepository->findScheduled();

        $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::NONE);

        $emailsCount = 0;

        $subject = sprintf('📰 Infolettre des entrepreneurs, %s', $formatter->format(time()));

        $referenceEmail = (new Email())
            ->to('no-reply@coopalpha.coop')
            ->subject($subject)
            ->html($newsletter->getGeneratedHtml())
            ->text($newsletter->getGeneratedTxt());

        if (!$input->getOption('dry-run')) {
            foreach ($users as $user) {
                $to = new Address($user->getEmail(), $user->getFullname());
                // TODO use Inky https://symfony.com/doc/current/mailer.html#inky-email-templating-language
                $email = (new Email())
                    ->to($to)
                    ->subject($subject)
                    ->html($newsletter->getGeneratedHtml($user))
                    ->text($newsletter->getGeneratedTxt($user));

                try {
                    $this->mailer->send($email);
                    $emailsCount += 1;
                    // TODO save to file
                } catch (TransportExceptionInterface $e) {
                    $io->error(sprintf('Cannot send email to %s', $to->toString()));
                    $io->error($e->getMessage());
                }
            }
            $newsletter->setSentAt(new DateTimeImmutable());
            $this->entityManager->flush();
        } else {
            $io->info('Dry-Run mode enabled. Email (not) sent:');
            $io->writeln($referenceEmail->toString());
            $io->info('End of email');
        }

        $io->success(sprintf('%s emails sent!', $emailsCount));

        return Command::SUCCESS;
    }
}
