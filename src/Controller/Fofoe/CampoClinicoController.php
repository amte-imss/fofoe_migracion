<?php

namespace App\Controller\Fofoe;

use App\Controller\DIEControllerController;
use App\Entity\CampoClinico;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment as TwigEnvironment;

#[Route('/fofoe/campo-clinico')]
class CampoClinicoController extends DIEControllerController
{
    public function __construct(
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Doctrine\ORM\EntityManagerInterface $em,
        \Symfony\Component\Serializer\SerializerInterface $serializer,
        private readonly MailerInterface $mailer,
        private readonly TwigEnvironment $twig,
        private readonly string $mailerSender = 'no_contestar@educacionensalud.imss.gob.mx',
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/{id}/validate-formato-fofoe', name: 'fofoe.api.validate_formato_fofoe')]
    public function validateFormatoFOFOE(Request $request, int $id): Response
    {
        /** @var CampoClinico $campoClinico */
        $campoClinico = $this->em->getRepository(CampoClinico::class)->find($id);

        if (!$campoClinico) {
            return new JsonResponse(['status' => 'error', 'message' => 'Campo clinico no encontrado']);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['validate_formato_fofoe'])) {
            return new JsonResponse(['status' => 'error', 'message' => 'Datos incompletos']);
        }

        $validateFormatoFofoe = $data['validate_formato_fofoe'];

        if ($validateFormatoFofoe == 0) {
            $campoClinico->setMotiveFormatoFofoe($data['motive_formato_fofoe'] ?? '');
        }

        $campoClinico->setValidateFormatoFofoe($validateFormatoFofoe);
        $this->em->persist($campoClinico);
        $this->em->flush();

        if ($campoClinico->getValidateFormatoFofoe() == 0) {
            $this->sendMailOficioNoValido($campoClinico);
        }

        return new JsonResponse(['status' => 'success']);
    }

    private function sendMailOficioNoValido(CampoClinico $campoClinico): void
    {
        $to = array_filter(
            array_map(
                fn($user) => $user->getCorreo(),
                $campoClinico->getSolicitud()->getDelegacion()->getUsuarios()->toArray()
            )
        );

        $email = (new Email())
            ->from($this->mailerSender)
            ->to(...$to)
            ->subject('Sistema de Administración del FOFOE - Información sobre sus documentos')
            ->html($this->twig->render('emails/fofoe/campo_clinico/documentos_invalidos.html.twig', [
                'solicitud' => $campoClinico,
            ]));

        $this->mailer->send($email);
    }
}
