import * as React from 'react'
import ReactDOM from 'react-dom'
import ListConvenios from "../../components/ListConvenios";
import Swal from "sweetalert2";
import {getSchemeAndHttpHost} from "../../utils";

const ValidarInfo = (
  {
    institucion,
    errores,
    action
  }) => {
  const { useRef } = React
  const formRef = useRef(null)

  function handleForm(event) {
    event.preventDefault();

    let cedulaSelectedText = 'La información de la Cédula de Identificación Fiscal será utilizada para el proceso de facturación de sus pagos';
    if(!formRef.current.elements['institucion[cedulaFile2]'].value) {
      cedulaSelectedText = 'La cédula de identificación es necesaria para el proceso de facturación en caso de no adjuntar el archivo, o de que los datos no sean correctos, no se podrá emitir la factura correspondiente';
    }

    Swal.fire({
      title: '¿Confirma que los datos son correctos? ',
      text: cedulaSelectedText,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: '¡Si, estoy seguro!',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.value) {
        formRef.current.elements['institucion[isConfirmacionInformacion]'].defaultChecked = true;
        formRef.current.submit();
      }
    })
  }

  return(
    <form
      action={action}
      method="post"
      encType='multipart/form-data'
      onSubmit={handleForm}
      ref={formRef}
    >
      <div className='row'>
        <div className='col-md-12'>
          <div className={`form-group ${errores.razonSocial ? 'has-error has-feedback' : ''}`}>
            <label htmlFor="institucion_rfc">Razón Social</label>
            <input
              className='form-control'
              type="text"
              readOnly
              name="institucion[razonSocial]"
              id="institucion_razonSocial"
              defaultValue={institucion.razonSocial}
              required={false}
            />
            <span className="help-block">{errores.razonSocial ? errores.razonSocial[0] : ''}</span>
          </div>
        </div>
      </div>
      <div className='row'>
        <div className='col-md-6'>
          <div className={`form-group ${errores.rfc ? 'has-error has-feedback' : ''}`}>
            <label htmlFor="institucion_rfc">RFC</label>
            <input
              className='form-control'
              type="text"
              readOnly
              name="institucion[rfc]"
              id="institucion_rfc"
              defaultValue={institucion.rfc}
              required={true}
            />
            <span className="help-block">{errores.rfc ? errores.rfc[0] : ''}</span>
          </div>
        </div>
          {false &&
        <div className='col-md-6'>
          <div className={`form-group ${errores.representante ? 'has-error has-feedback' : ''}`}>
            <label htmlFor="institucion_representante">Representante</label>
            <input
              className='form-control'
              type="text"
              readOnly
              name="institucion[representante]"
              id="institucion_representante"
              defaultValue={institucion.representante}
              required={true}
            />
            <span className="help-block">{errores.representante ? errores.representante[0] : ''}</span>
          </div>
        </div>
          }
      </div>
        {false &&
      <div className='row'>
        <div className="col-md-12">
          <div className={`form-group ${errores.direccion ? 'has-error has-feedback' : ''}`}>
            <label htmlFor="institucion_direccion">Domicilio</label>
            <input
              className='form-control'
              type="text"
              readOnly
              name="institucion[direccion]"
              id="institucion_direccion"
              defaultValue={institucion.direccion}
              required={true}
            />
            <span className="help-block">{errores.direccion ? errores.direccion[0] : ''}</span>
          </div>
        </div>
      </div> }

        {false &&
      <div className='row'>
        <div className="col-md-4">
          <div className='form-group'>
            <label htmlFor="institucion_correo">Correo</label>
            <input
              className='form-control'
              type="email"
              readOnly
              name="institucion[correo]"
              id="institucion_correo"
              defaultValue={institucion.correo}
              required={true}
            />
          </div>
        </div>

        <div className="col-md-3">
          <div className={`form-group ${errores.telefono ? 'has-error has-feedback' : ''}`}>
            <label htmlFor="institucion_telefono">Teléfono</label>
            <input
              className='form-control'
              type="text"
              readOnly
              name="institucion[telefono]"
              id="institucion_telefono"
              defaultValue={institucion.telefono}
              required={true}
            />
            <span className="help-block">{errores.telefono ? errores.telefono[0] : ''}</span>
          </div>
        </div>

        <div className="col-md-2">
          <div className={`form-group ${errores.extension ? 'has-error has-feedback' : ''}`}>
            <label htmlFor="institucion_extension">Extensión</label>
            <input
              className='form-control'
              type="text"
              readOnly
              name="institucion[extension]"
              id="institucion_extension"
              defaultValue={institucion.extension}
            />
            <span className="help-block">{errores.extension ? errores.extension[0] : ''}</span>
          </div>
        </div>

        <div className="col-md-3">
          <div className='form-group'>
            <label htmlFor="institucion_fax">Fax (opcional)</label>
            <input
              className='form-control'
              type="text"
              readOnly
              name="institucion[fax]"
              id="institucion_fax"
              defaultValue={institucion.fax}
            />
          </div>
        </div>
      </div>
        }

      <div className='row'>
          {false &&
        <div className="col-md-4">
          <div className='form-group' >
            <label htmlFor="institucion_sitioWeb">Página web (opcional)</label>
            <input className='form-control'
              type="text"
              name="institucion[sitioWeb]"
              id="institucion_sitioWeb"
                   readOnly
              defaultValue={institucion.sitioWeb}
            />
          </div>
        </div>
          }
        <div className="col-md-8">
          <label htmlFor="institucion_cedulaFile">
            Adjunte Constancia de Situación Fiscal Actualizada (No mayor a 3 meses) en caso de requerir factura<br/>
            <span className='text-danger text-sm'>Por favor verifique que los datos que aparecen en su archivo sean correctos. <br/>
            La constancia de situación fiscal se utilizará para emitir las facturas de sus pagos.</span>
          </label>
          <input
            type="file"
            name="institucion[cedulaFile2]"
            id="institucion_cedulaFile"
            required={false}
          />
          <span className="help-block">{errores.cedulaFile2 ? errores.cedulaFile2[0] : ''}</span>
          {
            institucion.cedulaIdentificacion2 &&
            <a
              href={`${getSchemeAndHttpHost()}/ie/descargar-cedula-de-identificacion-fiscal2`}cedulaIdentificacion2
              download
            >
              Descargar cédula
            </a>
          }
        </div>
        <div className="col-md-12 mt-15 text-right">
          <div className='form-group'>
            <div className="hidden">
              <input
                type="checkbox"
                name='institucion[isConfirmacionInformacion]'
                id='institucion_isConfirmacionInformacion'
                value='checked'
              />
            </div>
          </div>
        </div>
      </div>

      <div className='row'>
        <div className="col-md-9"/>
        <div className='col-md-3'>
          <button
            type="submit"
            className="btn btn-success btn-block"
          >
            Guardar
          </button>
        </div>
      </div>
    </form>
  )
}

export default ValidarInfo

document.addEventListener('DOMContentLoaded', () => {
  ReactDOM.render(
    <ValidarInfo
      institucion={window.INSTITUCION_PROP}
      errores={window.ERRORS}
      action={window.ACTION}
    />,
    document.getElementById('perfil-component')
  )

  ReactDOM.render(
    <ListConvenios
      convenios={window.CONVENIOS_PROP}
    />,
    document.getElementById('lista-convenio-component')
  )
})
