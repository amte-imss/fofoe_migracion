<?php

namespace App\Service\EduPer;

use App\Entity\EduPer\Solicitud;
use App\Entity\Pago;

/** Shapes kept compatible with the original Educación Permanente screens. */
class Presenter
{
    public function solicitud(Solicitud $s, bool $includePago = false): array
    {
        $d = $s->getDuracion();
        $m = $d?->getModalidad();
        $t = $s->getTipoSolicitud();
        $c = $s->getCurso();
        $data = [
            'id' => $s->getId(), 'idFormatted' => $s->getIdFormatted(),
            'amount' => $s->getAmount(),
            'tipoSolicitud' => $t ? ['id' => $t->getId(), 'name' => $t->getName(), 'code' => $t->getCode()] : null,
            'duracion' => $d ? ['id' => $d->getId(), 'name' => $d->getName(), 'code' => $d->getCode(),
                'modalidad' => $m ? ['id' => $m->getId(), 'name' => $m->getName(), 'code' => $m->getCode()] : null] : null,
            'curso' => $c ? ['id' => $c->getId(), 'name' => $c->getName(), 'code' => $c->getCode()] : null,
            'participant' => $s->getParticipant(), 'email' => $s->getEmail(),
            'quantity' => $s->getQuantity(), 'hasDiscount' => $s->getHasDiscount(),
            'status' => $s->getStatus(), 'statusFormatted' => $s->getStatusFormatted(),
            'createdAtFormatted' => $s->getCreatedAtFormatted(),
            'createdAt' => $s->getCreatedAt()?->format(DATE_ATOM),
        ];
        if ($includePago) {
            $pago = $s->getLastPago();
            $data['lastPago'] = $pago ? $this->pago($pago, false) : null;
        }
        return $data;
    }

    public function pago(Pago $p, bool $includeSolicitud = true): array
    {
        $f = $p->getFactura();
        $data = [
            'id' => $p->getId(), 'monto' => $p->getMonto(),
            'montoRegistrado' => $p->getMontoRegistrado(),
            'fechaPagoFormatted' => $p->getFechaPagoFormatted(),
            'referenciaBancaria' => $p->getReferenciaBancaria(),
            'tipoMoneda' => $p->getTipoMoneda(), 'statusFormatted' => $p->getStatusFormatted(),
            'observaciones' => $p->getObservaciones(), 'validado' => $p->getValidado(),
            'requiereFactura' => $p->isRequiereFactura(),
            'factura' => $f ? ['id' => $f->getId(), 'folio' => $f->getFolio(),
                'fechaFacturacionFormatted' => $f->getFechaFacturacionFormatted(), 'monto' => $f->getMonto()] : null,
        ];
        if ($includeSolicitud) { $data['eduPerSolicitud'] = $this->solicitud($p->getEduPerSolicitud()); }
        return $data;
    }
}
