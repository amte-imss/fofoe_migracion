import * as React from 'react'
import ReactDOM from "react-dom";
import {dateFormat, getSchemeAndHttpHost, moneyFormat} from "../../utils";
import Cleave from "cleave.js/react";
import {FormValidator} from "../../components/FormValidator/FormValidator";
import Modal from 'react-modal';
import PreviewDocument from "../../components/PreviewDocument/PreviewDocument";
import Loader from "../../components/Loader/Loader";


const SI_REQUIERE_FACTURA_DEFAULT = 1;
const NO_REQUIERE_FACTURA_DEFAULT = 0;

const DatosResidencia = ({residencia}) => {

  const pagos = residencia.pagos.length > 0 ? residencia.pagos : []
  const lastPago = residencia.pagos[0]

  return (
    <div className="col-md-12">
      <div className="row">
        <div className="col-md-6">
          <p className='mb-5'>Ciclo <strong>{residencia.ciclo}</strong></p>
          {
            residencia.fechaInicio &&
            <p className='mb-5'>Período: <strong>{ dateFormat(residencia.fechaInicio)} - { dateFormat(residencia.fechaTermino)}</strong></p>
          }
          <p className='mb-5'>Folio / Núm Oficio  <strong>{residencia.folio}</strong></p>
          <p className='mb-20'>No. de referencia bancaria <strong>{lastPago.referenciaBancaria}</strong></p>
          {// <p className='mb-5'>Monto total: <strong>{moneyFormat(lastPago.monto)}</strong></p>
          }
        </div>
        <div className="col-md-6">
          <p className='mb-5'>Nacionalidad: <strong>{residencia.residente.nacionalidad}</strong></p>
          <p className='mb-5'>Sede: <strong>{residencia.sede}</strong></p>
          <p className='mb-5'>Especialidad: <strong>{residencia.especialidad}</strong></p>
        </div>
      </div>
    </div>
  )
}

const ComprobantesCargados = ({pagos, lastPago, residencia}) => {

  const pagosAnteriores = pagos.filter(pago => pago.id !== lastPago.id )

  return (
    pagosAnteriores.length > 0 &&
    <div className="col-md-12 mb-20">
      <table className='table table-condensed'>
        <thead>
        <tr>
          <th>Comprobante registrado</th>
          <th>Fecha</th>
          <th>Monto validado</th>
          <th>Observaciones</th>
        </tr>
        </thead>
        <tbody>
        {
          pagosAnteriores.length !== 0 ?
            pagosAnteriores.map((pago, index) =>
              <tr key={index}>
                <td><a href={`${getSchemeAndHttpHost()}/residente/residencia/${residencia.id}/comprobante-pago/${pago.id}/download`}
                       target='_blank' download >Descargar</a></td>
                <td>{pago.fechaPago.substring(0,10)}</td>
                <td>{moneyFormat(pago.monto)} MXN</td>
                <td>{pago.observaciones}</td>
              </tr>
            ) :
            <tr>
              <td
                className='text-center text-info'
                colSpan={4}
              >
                Aún no se ha cargado ningún comprobante de pago
              </td>
            </tr>
        }
        </tbody>
      </table>
    </div>
  )
}

const ObservacionesLastPago = ({lastPago}) => {
  return (
    lastPago.observaciones &&
    <div className="col-md-12">
      <h3 className='mb-5'>Observaciones</h3>
      <div className="alert alert-info">
        <p>{lastPago.observaciones}</p>
      </div>
    </div>
  )
}

const FormCargaComprobante = ({residencia, lastPago, errors, montoAPagar}) => {

  const { useState } = React
  const formRef = React.useRef();
  const [monto, setMonto] = useState(undefined)
  const [tipoMoneda, setTipoMoneda] = useState('')
  const [hasMontoError, setMontoError] = useState(false)
  const [requiereFactura, setRequiereFactura] = useState(undefined)
  const [showModal, setShowModal] = React.useState(false);
  const [isLoading, setIsLoading] = React.useState(false);

  const [errores, setErrores] = React.useState({});

  function handleMonto({ target }) {
    setMonto(target.value)
  }

  function handleTipoMoneda({ target }) {
    setTipoMoneda(target.value)
  }

  function handleSubmit(event) {
    event.preventDefault();
    setShowModal(true)
  }

  function  handleConfirmarDatos(event) {
    setShowModal(false);
    setIsLoading(true);
    formRef.current.submit();
  }

  return (
    <div className="col-md-12">
      <Loader show={isLoading}/>
      <h3 className='mb-5'>Registrar comprobante de pago</h3>
      <p className='mb-5'>Monto total a pagar: <strong>{moneyFormat(residencia.monto)}</strong> {residencia.tipoMoneda}</p>
      {
        residencia.tasaCambio ?
          <p className='mb-20'>Monto pendiente por pagar: <strong>{moneyFormat(montoAPagar)}</strong> MXN</p>
          : null
      }
      <FormValidator errors={errors} setErrors={setErrores}>
      <form
        ref={formRef}
        action={`${getSchemeAndHttpHost()}/residente/pagos/${lastPago.id}/carga-de-comprobante-de-pago`}
        method='post'
        className='form-horizontal'
        encType='multipart/form-data'
        onSubmit={handleSubmit}
      >
        <div className={`form-group ${errors['comprobante_pago[fechaPago]'] ? 'has-error' : ''}`}>
          <label
            htmlFor="comprobante_pago_fechaPago"
            className='control-label col-md-4'
          >
            Fecha en que se realizó el pago a registrar:
          </label>
          <div className="col-md-3">
            <input
              type="date"
              id='comprobante_pago_fechaPago'
              className='form-control'
              name='comprobante_pago[fechaPago]'
              required={true}
            />
          </div>
          {errors['comprobante_pago[fechaPago]'] ? 	<p className="help-block">{errors['comprobante_pago[fechaPago]']}</p> : null }
        </div>
        <div className={`form-group ${errors['comprobante_pago[fechaPago]'] ? 'has-error' : ''}`}>
          <label
            htmlFor="comprobante_pago_monto"
            className='control-label col-md-4'
          >
            Monto del comprobante a registrar:<br/>
            <span className='text-danger text-sm'>NOTA: El monto debe coincidir con el comprobante registrado</span>
          </label>
          <div className="col-md-3">
            <div className={`input-group ${errors['comprobante_pago[monto]'] ? 'has-error' : ''}`}>
              <div className="input-group-addon">$</div>
              <Cleave
                options={{numeral: true, numeralThousandsGroupStyle: 'thousand'}}
                className='form-control'
                required={true}
                name={'comprobante_pago[monto]'}
                onChange={handleMonto}
              />
            </div>
            {errors['comprobante_pago[monto]'] ? 	<p className="text-danger">{errors['comprobante_pago[monto]']}</p> : null }

            <div className={`form-group mt-10 ${errors['comprobante_pago[tipoMoneda_otro]'] || errors['comprobante_pago[tipoMoneda]']  ? 'has-error' : ''}`}>
              <div className="form-check form-check-inline">
                <input className="form-check-input" type="radio" name="comprobante_pago[tipoMoneda]" id="inlineRadio1"
                       onChange={handleTipoMoneda}
                       value="MXN" />
                  <label className="form-check-label" htmlFor="inlineRadio1"> MXN (Pesos Mexicanos) </label>
              </div>
              <div className="form-check form-check-inline">
                <input className="form-check-input" type="radio" name="comprobante_pago[tipoMoneda]" id="inlineRadio2"
                       onChange={handleTipoMoneda}
                       value="USD" />
                  <label className="form-check-label" htmlFor="inlineRadio2">USD (Dólares Americanos)</label>
              </div>
              <div className="form-check form-check-inline">
                <input className="form-check-input" type="radio" name="comprobante_pago[tipoMoneda]" id="inlineRadio2"
                       onChange={handleTipoMoneda}
                       value="EUR" />
                <label className="form-check-label" htmlFor="inlineRadio2">EUR (Euro)</label>
              </div>
              <div className="form-check form-check-inline">
                <input className="form-check-input" type="radio" name="comprobante_pago[tipoMoneda]" id="inlineRadio2"
                       onChange={handleTipoMoneda}
                       value="other" />
                <label className="form-check-label" htmlFor="inlineRadio2">Otro</label>
              </div>
              <p className="text-danger">{errors['comprobante_pago[tipoMoneda]']}</p>
              {tipoMoneda === 'other' ?
                <>
                <div className="form-check form-check-inline">
                  <input className="form-control" type="text" name="comprobante_pago[tipoMoneda_otro]" required id={'tipoMoneda_otro'}/>
                </div>
                  <p className="text-danger">{errors['comprobante_pago[tipoMoneda_otro]']}</p>
                </> : null}
            </div>
          </div>
        </div>
        <div className={`form-group ${errors['comprobante_pago[comprobantePagoFile]'] ? 'has-error' : ''} `}>
          <label
            htmlFor="comprobante_pago_comprobantePagoFile"
            className='control-label col-md-4'
          >
            Cargar comprobante del nuevo pago
            <p className="text-danger text-sm">Solo se admiten archivos PDF de máx 2MB</p>
          </label>
          <div className="col-md-3">
            <input
              type="file"
              accept=".pdf"
              id='comprobante_pago_comprobantePagoFile'
              name='comprobante_pago[comprobantePagoFile]'
              className='form-control'
              required={true}
            />
            <p className="text-danger">{errors['comprobante_pago[comprobantePagoFile]']}</p>
            <span className="text-danger ">{errors.comprobantePagoFile ? errors.comprobantePagoFile[0] : ''}</span>
            <span className="text-danger ">{errors.comprobante_pago_file ? errors.comprobante_pago_file[0] : ''}</span>
          </div>
        </div>

        <div className="row mt-5">
          <div className="form-group">
            <label
                htmlFor='comprobante_pago_requiere_factura'
                className="control-label col-md-4 text-right"
            >
              ¿Requiere factura?&nbsp;
            </label>
            <span className="text-danger">{errors.cedulaFile ? errors.cedulaFile[0] : ''}</span>
            <div className="col-md-3">
              <label htmlFor='comprobante_pago_requiereFactura_yes'>Si&nbsp;</label>
              <input
                  type="radio"
                  value={SI_REQUIERE_FACTURA_DEFAULT}
                  id='comprobante_pago_requiereFactura_yes'
                  name='comprobante_pago[requiereFactura]'
                  onClick={()=>setRequiereFactura(SI_REQUIERE_FACTURA_DEFAULT)}
                  required={true}
              />
              &nbsp;&nbsp;&nbsp;&nbsp;
              <label htmlFor="comprobante_pago_requiereFactura_no">No&nbsp;</label>
              <input
                  type="radio"
                  value={NO_REQUIERE_FACTURA_DEFAULT}
                  id='comprobante_pago_requiereFactura_no'
                  name='comprobante_pago[requiereFactura]'
                  onClick={()=>setRequiereFactura(NO_REQUIERE_FACTURA_DEFAULT)}
                  required={true}
              />
            </div>
          </div>
          {
            requiereFactura ?
                <div className="form-group">
                  <div className="col-md-8">
                    <label htmlFor="institucion_cedulaFile">
                      Adjunte Constancia de Situación Fiscal Actualizada (No mayor a 3 meses) <br/>
                      <span className='text-danger text-sm'>Por favor verifique que los datos que aparecen en su archivo sean correctos. <br/>
            La constancia de situación fiscal se utilizará para emitir las facturas de sus pagos.</span>
                    </label>
                  </div>

                  <div className="col-md-3">
                    <input
                        type="file"
                        id='institucion_cedulaFile'
                        name='comprobante_pago[cedulaFile]'
                        className='form-control'
                        required={true}
                    />
                  </div>
                </div> : null
          }
        </div>

        <div className="row mt-30">
          <div className="col-md-4"/>
          <div className="col-md-2">
            <a
              href={`${getSchemeAndHttpHost()}/residente/perfil`}
              className='btn btn-default btn-block'
            >Cancelar</a>
          </div>
          <div className="col-md-2">
            <button
              type='submit'
              className='btn btn-success btn-block'>
              Guardar
            </button>
          </div>
        </div>
      </form>
      </FormValidator>


      {showModal &&     <Modal
        isOpen={showModal}
        ariaHideApp={false}
        contentLabel="Confirme sus datos"
      >
        <div>
          <div className={"mb-20"}>
            <div className="col-md-12">
              <div className="row">
                <div className="col-md-12 mb-20">
                  <h2 className={'text-danger text-bold'}>Por favor confirma que todos los datos con correctos</h2>
                </div>
              </div>
            </div>
            <div className="col-md-12">
              <div className={"row"}>
                <div className="col-md-12 mb-20">
                  <h2>Datos Generales</h2>
                </div>
              </div>
            </div>
            <div className="col-md-12">
              <div className="row">
                <div className="col-md-4">
                  <div className="form-group">
                    <div><span>Nombre</span></div>
                    <div><span>{residencia.residente.usuario.nombre} {residencia.residente.usuario.apellidoPaterno} {residencia.residente.usuario.apellidoMaterno}</span></div>
                  </div>
                </div>
                <div className="col-md-4">
                  <div className="form-group">
                    <div><span>CURP</span></div>
                    <div><span>{residencia.residente.usuario.curp}</span></div>
                  </div>
                </div>
                <div className="col-md-4">
                  <div className="form-group">
                    <div><span>Nacionalidad</span></div>
                    <div><span>{residencia.residente.nacionalidad}</span></div>
                  </div>
                </div>
              </div>
            </div>
              <DatosResidencia residencia={residencia}/>
            <div className="col-md-12">
              <div className="row">
                <div className="col-md-6">
                  <p className="mb-5">
                    Fecha en que se realizó el pago a registrar <strong>{formRef && formRef.current ? formRef.current.elements['comprobante_pago[fechaPago]'].value: ''}</strong>
                  </p>
                </div>
                <div className="col-md-6">
                  <p className="mb-5">
                    Monto del comprobante a registrar <strong>{formRef && formRef.current ? formRef.current.elements['comprobante_pago[monto]'].value: ''} {formRef && formRef.current ? formRef.current.elements['comprobante_pago[tipoMoneda]'].value: ''} </strong>
                  </p>
                </div>
              </div>
              <div className="row">
                <PreviewDocument file={formRef && formRef.current ? formRef.current.elements['comprobante_pago[comprobantePagoFile]'].files[0]: {}}/>
              </div>
            </div>
            <div className={"row"}>
              <div className="col-md-6">
                <div className="form-group">
                  <button type={'button'}
                    onClick={handleConfirmarDatos}
                    className="btn btn-block btn-success">
                    Si, son correctos
                  </button>
                </div>
              </div>
              <div className="col-md-6">
                <div className="form-group">
                  <button type={'button'}
                          onClick={() => setShowModal(false)}
                          className="btn btn-block btn-danger">
                    Cancelar
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </Modal> }
    </div>
  )
}

const CargaComprobantePago = ({residencia, errors, montoAPagar}) => {

  return (
     <div className="row">
       <DatosResidencia residencia={residencia} />
       <ComprobantesCargados pagos={residencia.pagos} lastPago={residencia.lastPago} residencia={residencia} />
       <ObservacionesLastPago lastPago={residencia.lastPago} />
       <FormCargaComprobante
         residencia={residencia}
         lastPago ={residencia.lastPago}
         errors={errors}
         montoAPagar={montoAPagar}
         />
     </div>
  )
}

document.addEventListener('DOMContentLoaded', () => {
  ReactDOM.render(
    <CargaComprobantePago
      residencia={window.RESIDENCIA_PROPS}
      errors={window.ERRORS_PROPS}
      montoAPagar={window.MONTO_A_PAGAR}
    />,
    document.getElementById('cargar-de-comprobante-de-pago-component')
  )
})