import {getSchemeAndHttpHost, moneyFormat} from "../../../utils";
import * as React from "react";
import EstadosPago from "../../referencia/components/EstadosPago";
import AccionFofoe from "../../referencia/components/AccionFofoe";

const DatosSolicitudPosgrado = ({residencia, pago, show_button=true, onRegistrarFactura}) => {

  const montoTotal = getMontoTotal()

  function getMontoTotal() {
    return residencia.monto;
  }


  function getMontoTotalTitle() {
    return 'Monto total de la residencia:';
  }

  return (
    <div className="col-md-12 mb-20">
      <div className="row">
        <div className="col-md-4">
          <p className='mb-5'><strong>Residencia - {residencia.tipo}</strong></p>
          <p className='mb-5'>Folio / No. de Oficio: <strong>{residencia.folio}</strong></p>
          <p className='mb-5'>Ciclo: <strong>{residencia.ciclo}</strong></p>
          <p className='mb-5'>{getMontoTotalTitle()} {moneyFormat(montoTotal)} {residencia.tipoMoneda}</p>
          {
            residencia.tasaCambio ?
              <>
                <p className='mb-5'>Tipo de Cambio (USD a MXN): {moneyFormat(residencia.tasaCambio)}</p>
                <p className='mb-20'>Monto total en pesos mexicanos (MXN): <strong>{moneyFormat(montoTotal*residencia.tasaCambio)}</strong></p>
              </>
              : null
          }
        </div>
        <div className="col-md-4">
          <p className='mb-5'><strong>Residente</strong></p>
          <p className='mb-5'>Nombre: <strong>{residencia.residente.usuario.nombre} {residencia.residente.usuario.apellidoPaterno} {residencia.residente.usuario.apellidoMaterno}</strong></p>
          { residencia.residente.usuario.rfc ?
            <p className='mb-5'>RFC: <strong>{residencia.residente.usuario.rfc}</strong></p>
            : null
          }
          <p className='mb-5'> OOAD / UMAE: <strong>{residencia.delegacion} </strong> { residencia.tipoDelegacionUmae }</p>
          {
            'UMAE' === residencia.tipoDelegacionUmae ?
              <p className='mb-5'>UMAE: <strong>{residencia.sede}</strong></p>
              : null
          }
        </div>
      </div>
      <div className="row mt-15">
        <div className="col-md-6">
          <p>Referencia Bancaria: <strong>{pago.referenciaBancaria}</strong></p>
          <p>Estado de la referencia:<strong> <EstadosPago pago={pago} onRegistrarFactura={onRegistrarFactura} /> </strong> </p>
        </div>
        {
          show_button && <AccionFofoe pago={pago} asButton={true}/>
        }
      </div>
    </div>
  )
}

export default DatosSolicitudPosgrado