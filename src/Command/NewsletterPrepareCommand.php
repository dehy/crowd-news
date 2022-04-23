<?php

namespace App\Command;

use App\Entity\Newsletter;
use App\Repository\NewsletterRepository;
use App\Service\NewsletterService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[AsCommand(
    name: 'app:newsletter:prepare',
    description: 'Prepare the next newsletter',
)]
class NewsletterPrepareCommand extends Command
{
    public function __construct(protected EntityManagerInterface $entityManager, protected Environment $twig, protected NewsletterService $newsletterService, string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Run like if really preparing the newsletter, but don\'t.')
        ;
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws NonUniqueResultException
     * @throws LoaderError
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var NewsletterRepository $newsletterRepository */
        $newsletterRepository = $this->entityManager->getRepository(Newsletter::class);
        if (null !== $currentlyScheduledNewsletter = $newsletterRepository->findScheduled()) {
            foreach ($currentlyScheduledNewsletter->getNews() as $news) {
                $currentlyScheduledNewsletter->removeNews($news);
            }
            $this->entityManager->remove($currentlyScheduledNewsletter);
            $this->entityManager->flush();
        }

        $newsletter = $this->newsletterService->nextNewsletter();
        $html = $this->twig->render('newsletter/themes/default.html.twig', ['newsletter' => $newsletter]);
        $txt = $this->twig->render('newsletter/themes/default.txt.twig', ['newsletter' => $newsletter]);
        $newsletter->setGeneratedHtml($html);
        $newsletter->setGeneratedTxt($txt);

        $this->entityManager->persist($newsletter);
        $this->entityManager->flush();

        $io->success(sprintf('Newsletter prepared and ready to be sent on %s', $newsletter->getScheduledFor()->format('d/m/Y H:i')));

        return Command::SUCCESS;
    }
}
