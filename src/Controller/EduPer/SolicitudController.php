<?php

namespace App\Controller\EduPer;

use App\Controller\DIEControllerController;
use App\Entity\EduPer\{Curso, Duracion, Modalidad, Solicitud, TipoSolicitud};
use App\Form\Type\EduPer\RequestType;
use App\Service\EduPer\Presenter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/edu-per')]
class SolicitudController extends DIEControllerController
{
    #[Route('/', name: 'edu-per.index', methods: ['GET'])]
    public function index(): Response { return $this->render('edu_per/index.html.twig'); }

    #[Route('/request', name: 'edu-per.request.index', methods: ['GET'])]
    public function requests(Presenter $presenter): JsonResponse
    {
        $items = $this->em->getRepository(Solicitud::class)->findBy([], ['createdAt' => 'DESC']);
        return $this->json(['data' => array_map($presenter->solicitud(...), $items)]);
    }

    #[Route('/request/create', name: 'edu-per.request.create', methods: ['GET'])]
    public function create(): Response { return $this->render('edu_per/create.html.twig'); }

    #[Route('/request/login/{solicitudId}', name: 'edu-per.login', methods: ['GET', 'POST'])]
    public function login(int $solicitudId, Request $request, Presenter $presenter): Response
    {
        $solicitud = $this->em->getRepository(Solicitud::class)->find($solicitudId);
        if (!$solicitud) { throw $this->createNotFoundException('Solicitud no encontrada.'); }
        $error = $request->getSession()->remove('_edu_per_login_error');
        return $this->render('edu_per/login.html.twig', [
            'solicitud' => $presenter->solicitud($solicitud), 'errors' => [],
            'error_captcha' => $error === 'El código no coincide.', 'error_login' => $error ?? '',
        ]);
    }

    #[Route('/request/store', name: 'edu-per.request.store', methods: ['POST'])]
    public function store(Request $request, MailerInterface $mailer, Presenter $presenter,
        #[Autowire('%mailer_sender%')] string $mailerSender): JsonResponse
    {
        $form = $this->createForm(RequestType::class);
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->json(['status' => false, 'message' => 'Error', 'errors' => $this->getFormErrors($form, true)], Response::HTTP_BAD_REQUEST);
        }
        $data = $form->getData();
        $type = $this->em->getRepository(TipoSolicitud::class)->findOneBy(['code' => $data['type'], 'active' => true]);
        $modality = $this->em->getRepository(Modalidad::class)->findOneBy(['code' => $data['modality'], 'active' => true]);
        $duration = $this->em->getRepository(Duracion::class)->findOneBy(['code' => $data['duration'], 'active' => true]);
        $course = $this->em->getRepository(Curso::class)->findOneBy(['code' => $data['course'], 'active' => true]);
        if (!$type || !$modality || !$duration || !$course || $modality->getTipoSolicitudId() !== $type->getId() ||
            $duration->getModalidadId() !== $modality->getId()) {
            return $this->json(['status' => false, 'message' => 'Seleccione catálogos válidos.'], Response::HTTP_BAD_REQUEST);
        }
        $quantity = (int) ($data['quantity'] ?? 0);
        $amount = (float) $duration->getAmount();
        if (in_array($duration->getPeriod(), ['person', 'hours'], true)) {
            if ($quantity < 1) { return $this->json(['status' => false, 'message' => 'Indique una cantidad válida.'], Response::HTTP_BAD_REQUEST); }
            $amount *= $quantity;
        }
        $discount = filter_var($data['discount'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if ($discount) { $amount = round($amount / 2, 2); }
        $password = substr(bin2hex(random_bytes(8)), 0, 8);
        $solicitud = new Solicitud();
        $solicitud->setTipoSolicitud($type);
        $solicitud->setDuracion($duration);
        $solicitud->setCurso($course);
        $solicitud->setAmount($amount);
        $solicitud->setParticipant($data['name']);
        $solicitud->setEmail($data['email']);
        $solicitud->setQuantity($quantity);
        $solicitud->setHasDiscount($discount);
        $solicitud->setPassword(hash('sha512', $password));
        $solicitud->setPlainPassword($password);
        $this->em->persist($solicitud);
        $this->em->flush();
        try {
            $mailer->send((new Email())->from($mailerSender)->to($solicitud->getEmail())
                ->subject('Sistema de Administración del FOFOE - Credenciales de acceso')
                ->html($this->renderView('emails/edu_per/participante_bienvenida.html.twig', ['solicitud' => $solicitud])));
        } catch (\Throwable $e) { /* Keep the saved registration available to staff if mail is unavailable. */ }
        return $this->json(['status' => true, 'message' => 'Creado con éxito',
            'data' => ['id' => $solicitud->getId(), 'password' => $password]]);
    }
}
