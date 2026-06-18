<?php

namespace App\Controller;

use App\Entity\ConfiguracionGlobal;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    #[Route('/login/{tipo}', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, string $tipo = ''): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('homepage');
        }

        $view = match($tipo) {
            'convenio'  => 'security/login_convenio.html.twig',
            default     => 'security/login.html.twig',
        };

        print_r([
            'last_username' => $authenticationUtils->getLastUsername(),
            'error'         => $authenticationUtils->getLastAuthenticationError(),
            'tipo'          => $tipo,
            'path_login'    => $this->generateUrl('app_login', ['tipo' => $tipo]),
            'showImage'     => $this->showImages(),
        ]);

        return $this->render($view, [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error'         => $authenticationUtils->getLastAuthenticationError(),
            'tipo'          => $tipo,
            'path_login'    => $this->generateUrl('app_login', ['tipo' => $tipo]),
            'showImage'     => $this->showImages(),
        ]);
    }

    private function showImages(): bool
    {
        $config = $this->em->getRepository(ConfiguracionGlobal::class)
            ->findOneBy(['clave' => 'FRONTEND_SHOW_HEADER_FOOTER']);

        return $config ? boolval($config->getValor()) : true;
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): never
    {
        // Este método nunca se ejecuta —
        // el firewall intercepta la ruta antes
        throw new \LogicException('El firewall intercepta esta ruta.');
    }
}
