import ReactDOM from "react-dom";
import React from "react";
import ValidarSolicitudForm from "./ValidarSolicitudForm";
import Institucion from "../components/Institucion";
import ListConvenios from "../../../components/ListConvenios";
import {getSchemeAndHttpHost} from "../../../utils";
import Loader from "../../../components/Loader/Loader";

const HeaderSubTitle = ({titulo}) => {
  return (
    <div className="row">
      <div className="col-md-12">
        <h2>{titulo}</h2>
      </div>
    </div>
  )
}

const ValidarSolicitud = ({solicitud}) => {
  const [isLoading, setIsLoading] = React.useState(false);
  const [convenios, setConvenios] = React.useState([]);

  React.useEffect(() => {
    setIsLoading(true);
    fetch(`${getSchemeAndHttpHost()}/came/api/convenio/${solicitud.institucion.id}`)
      .then(response => {
        return response.json()}, error => {
        console.error(error)})
      .then(json => {
        setConvenios(json.data);
      }).finally(() => {
      setIsLoading(false);
    })
  }, []);

  return (
    <>
      <Loader show={isLoading} />
      <HeaderSubTitle
        titulo='Información de contacto de la Institución'
      />
      <Institucion
        instituciones={[solicitud.institucion]}
        disableSelect={true}
        callbackIsLoading={setIsLoading}
        institucion={solicitud.institucion}
        parentCallback={()=> {}}
      />
      <HeaderSubTitle
        titulo='Convenios'
      />
      <ListConvenios convenios={convenios} />
      <HeaderSubTitle
        titulo='Campos Solicitados'
      />
      <ValidarSolicitudForm
        solicitud={solicitud}
        setIsLoading={setIsLoading}
      />
    </>
  )
}

document.addEventListener('DOMContentLoaded', () => {
  ReactDOM.render(
    <ValidarSolicitud
      solicitud={window.SOLICITUD}
    />,
    document.getElementById('solicitud-validate-wrapper')
  )
})
