<?php

namespace App\Controller;

use App\Entity\Newsletter;
use App\Exception\NonUpdatableException;
use App\Form\NewsletterType;
use App\Repository\NewsletterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/newsletter')]
class NewsletterController extends AbstractController
{
    #[Route('/', name: 'newsletter_index', methods: ['GET'])]
    public function index(NewsletterRepository $newsletterRepository): Response
    {
        return $this->render('newsletter/index.html.twig', [
            'newsletters' => $newsletterRepository->findSent(),
        ]);
    }

    #[Route('/next', name: 'newsletter_next', methods: ['GET'])]
    public function next(EntityManagerInterface $entityManager): Response
    {
        $newsletter = $entityManager->getRepository(Newsletter::class)->findNext();

        return $this->show($newsletter);
    }

    #[Route('/{id}', name: 'newsletter_show', methods: ['GET'])]
    public function show(Newsletter $newsletter): Response
    {
        return $this->render('newsletter/show.html.twig', [
            'newsletter' => $newsletter,
        ]);
    }

    #[Route('/{id}/preview', name: 'newsletter_preview_html', methods: ['GET'])]
    public function renderHtml(Newsletter $newsletter): Response
    {
        return $this->render('newsletter/themes/default.html.twig', [
            'newsletter' => $newsletter
        ]);
    }

    #[Route('/{id}/edit', name: 'newsletter_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Newsletter $newsletter, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(NewsletterType::class, $newsletter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$newsletter->isEditable()) {
                throw new NonUpdatableException();
            }
            $entityManager->flush();

            return $this->redirectToRoute('newsletter_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('newsletter/edit.html.twig', [
            'newsletter' => $newsletter,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'newsletter_delete', methods: ['POST'])]
    public function delete(Request $request, Newsletter $newsletter, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$newsletter->getId(), $request->request->get('_token'))) {
            foreach ($newsletter->getNews() as $news) {
                $newsletter->removeNews($news);
            }
            $entityManager->remove($newsletter);
            $entityManager->flush();
        }

        return $this->redirectToRoute('newsletter_index', [], Response::HTTP_SEE_OTHER);
    }
}
