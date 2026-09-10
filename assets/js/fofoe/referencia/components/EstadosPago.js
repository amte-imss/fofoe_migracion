import * as React from "react";
import {isPagoCampoClinico, isPagoPosgrado} from "../utils";

const EstadosPagoCC = ({pago}) => {
    if (pago.validado == null) {
        if(pago.validate_oficio_montos == 0 || pago.validate_formato_fofoe == 0){
            return (<span>Documentos rechazados, favor de corregir</span>);
        }
        return (<span>Pendiente Validación</span>);
    } else if ((pago.validado && !pago.requiere_factura) || (pago.validado && pago.requiere_factura && pago.factura_id)) {
        return (<span>Solicitud Pagada</span>);
    } else if (pago.validado && pago.requiere_factura && !pago.factura_id) {
        return (<span>Factura Pendiente</span>);
    } else {
        return (<span>Pago no Válido</span>);
    }
}

const EstadosPagoPosgrado = ({pago, onRegistrarFactura}) => {
    if (pago.validado == null) {
        return (<span>Pendiente Validación</span>);
    } else if ((pago.validado && !pago.requiereFactura) || (pago.validado && pago.requiereFactura && pago.factura_id)) {
        return (<span>Residencia Pagada</span>);
    } else if (pago.validado && pago.requiereFactura && !pago.factura) {
        return (<><span>Factura Pendiente</span>
            <button type={'button'}
                    onClick={onRegistrarFactura} className={'btn btn-block btn-primary'}>Registrar
                Factura
            </button>
        </>);
    } else {
        return (<span>Pago no Válido</span>);
    }
}

const EstadosPago = ({pago, onRegistrarFactura}) => {
    return isPagoCampoClinico(pago) ? <EstadosPagoCC pago={pago}/>
        : isPagoPosgrado(pago) ? <EstadosPagoPosgrado pago={pago} onRegistrarFactura={onRegistrarFactura}/>
            : null
}

export default EstadosPago