<?php

namespace App\Controller;

use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

final class CaptchaController
{
    const LENGTH           = 5;
    const PHRASE           = 'abcdefghijklmnpqrstuvwxyz123456789';
    const MAX_FRONT_LINES  = 0;
    const MAX_BEHIND_LINES = 0;
    const WIDTH            = 180;
    const HEIGHT           = 90;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly Environment $engine,
    ) {}

    #[Route('/generarCaptcha', name: 'generar_captcha')]
    public function index(): Response
    {
        $builder = $this->buildCaptcha();

        $this->requestStack->getSession()->set('captcha_phrase', $builder->getPhrase());

        return new Response($this->engine->render('captcha.html.twig', [
            'captcha' => $builder->inline(),
        ]));
    }

    #[Route('/captcha/reload', name: 'reload_captcha')]
    public function reload(): Response
    {
        $builder = $this->buildCaptcha();

        $this->requestStack->getSession()->set('captcha_phrase', $builder->getPhrase());

        return new Response($builder->inline(), 200, [
            'Access-Control-Allow-Headers' => '*',
            'Access-Control-Allow-Origin'  => '*',
        ]);
    }

    private function buildCaptcha(): CaptchaBuilder
    {
        $builder = new CaptchaBuilder(null, new PhraseBuilder(self::LENGTH, self::PHRASE));
        $builder->setMaxFrontLines(self::MAX_FRONT_LINES);
        $builder->setMaxBehindLines(self::MAX_BEHIND_LINES);
        $builder->build(self::WIDTH, self::HEIGHT);

        return $builder;
    }
}
