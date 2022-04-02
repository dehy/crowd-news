<?php

namespace App\Service;

use App\Entity\Newsletter;
use Twig\Environment;

class NewsletterService
{
    public function __construct(protected Environment $twig) {

    }

    public function renderHtmlNewsletter(Newsletter $newsletter) {
        $this->twig->render('newsletter/themes/default.html.twig');
    }
}