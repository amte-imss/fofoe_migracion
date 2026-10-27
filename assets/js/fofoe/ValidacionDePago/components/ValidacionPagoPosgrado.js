import * as React from 'react';
import DatosResidenciaPosgrado from "../../DetalleReferencia/components/DatosResidenciaPosgrado";
import HistorialPagos from "../../DetalleReferencia/components/HistorialPagos";
import ValidarComprobantePosgrado from "./ValidarComprobantePosgrado";

const ValidacionPagoPosgrado = ({pago, pagos, montoAPagar}) => {

  let historicoPagos = pagos.filter((item) => item.id !== pago.id )

  return(
    <div className='row mt-20'>
      <div className="col-md-12 mb-20">
        <DatosResidenciaPosgrado
          residencia={pago.residencia}
          pago={pago}
          show_button={false}
        />
        <HistorialPagos pagos={historicoPagos} />
        <ValidarComprobantePosgrado pago={pago} montoAPagar={montoAPagar} />
      </div>
    </div>
  )
}

export default ValidacionPagoPosgrado