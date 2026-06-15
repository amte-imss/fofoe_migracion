import * as React from 'react'
import {getSchemeAndHttpHost} from "../../../utils";
import {SOLICITUD} from "../../../constants";

const SolicitudAccion = ({solicitud}) => {

    const Validar = () => {
        return (<a href={`${getSchemeAndHttpHost()}/came/solicitud/${solicitud.id}/validar`}>Validar</a>);
    }

    const Editar = () => {
        return (<a href={`${getSchemeAndHttpHost()}/came/solicitud/${solicitud.id}/edit`}>Editar</a>);
    }

    const DescargarCredenciales = () => {
        return (<a href={`${getSchemeAndHttpHost()}/came/solicitud/${solicitud.id}`}>Descargar Credenciales</a>);
    }

    const CargarFormatoFofoe = () => {
        return (<a href={`${getSchemeAndHttpHost()}/came/solicitud/${solicitud.id}`}>Cargar formato(s) FOFOE firmado(s)</a>);
    }

    const ValidarMontos = () => {
        return (<a href={`${getSchemeAndHttpHost()}/came/solicitud/${solicitud.id}/validar_montos`}>Validar Montos</a>);
    }

    let result = (<></>);
    let statusCargarFormatoFOFOE = [
      SOLICITUD.MONTOS_VALIDADOS_CAME,
      SOLICITUD.CARGANDO_COMPROBANTES,
      SOLICITUD.FORMATOS_DE_PAGO_GENERADOS,
      SOLICITUD.EN_VALIDACION_FOFOE,
      SOLICITUD.CREDENCIALES_GENERADAS
    ]
    if (solicitud.estatus === SOLICITUD.CREADA) {
        result =  (<Editar />)
    } else if (solicitud.estatus === SOLICITUD.REGISTRADA) {
        result =  (<Validar />)
    } else if(solicitud.estatus === SOLICITUD.EN_VALIDACION_DE_MONTOS_CAME) {
        result = (<ValidarMontos />);
    } else if (solicitud.estatus === SOLICITUD.CREDENCIALES_GENERADAS) {
        //result = (<DescargarCredenciales />);
        result = (<></>);
    } else if(!solicitud.formatosFofoeCargados &&
      statusCargarFormatoFOFOE.includes(solicitud.estatus) ) {
        result = (<CargarFormatoFofoe />);
    }
    return result;

}

export default SolicitudAccion;