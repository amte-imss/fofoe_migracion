import * as React from 'react'
import ReactDOM from "react-dom";
import {dateFormat, getSchemeAndHttpHost, moneyFormat} from "../../utils";
import Cleave from "cleave.js/react";
import {FormValidator} from "../../components/FormValidator/FormValidator";
import Modal from 'react-modal';
import Loader from "../../components/Loader/Loader";
import PreviewDocument from "../../components/PreviewDocument/PreviewDocument";
import Swal from "sweetalert2";
import api from "../utils/api";
const SI_REQUIERE_FACTURA_DEFAULT = 1;
const NO_REQUIERE_FACTURA_DEFAULT = 0;

const numberFormat = new Intl.NumberFormat('es-MX', {
	minimumFractionDigits: 2,
	maximumFractionDigits: 2
})

const EnfermeriaAlumnoCargaComprobante = ({solicitud, usuario}) => {
	return (
		<>
			<div className="row">
				<DatosSolicitud solicitud={solicitud} usuario={usuario}/>
				<FormCargaComprobante
					solicitud={solicitud}
					usuario={usuario}
				/>
			</div>
		</>
	)
}

const FormCargaComprobante = ({solicitud, usuario}) => {

	const { useState } = React
	const formRef = React.useRef();
	const [monto, setMonto] = useState(undefined)
	const [tipoMoneda, setTipoMoneda] = useState('')
	const [hasMontoError, setMontoError] = useState(false)
	const [requiereFactura, setRequiereFactura] = useState(undefined)
	const [showModal, setShowModal] = React.useState(false);
	const [isLoading, setIsLoading] = React.useState(false);

	const [errores, setErrores] = React.useState({});
	const [errors, setErrors] = React.useState({});

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
		const formData = new FormData(formRef.current);
		if(formData.get('comprobante_pago[cedulaFile]') && formData.get('comprobante_pago[cedulaFile]').size === 0){
			formData.delete('comprobante_pago[cedulaFile]');
		}
		formData.set('comprobante_pago[montoRegistrado]', monto.toString().replace(',', ''));
		api.storePago(solicitud.id, formData).then(data => {
			console.log(data);
			Swal.fire(
				'Comprobante cargado con éxito',
				'',
				'success',
			).then(() => {
				window.location.href = getSchemeAndHttpHost(`/enfermeria-alumno/`)
			})
		}).catch(errors => {
			console.error(errors);
		}).finally(() => {
			setIsLoading(false);
		})
	}

	return (
		<div className="col-md-12">
			<Loader show={isLoading}/>
			<h3 className='mb-5'>Registrar comprobante de pago</h3>

			<FormValidator errors={errors} setErrors={setErrores}>
				<form
					ref={formRef}
					method='post'
					className='form-horizontal'
					encType='multipart/form-data'
					onSubmit={handleSubmit}
				>
					<div className={`form-group ${errors['comprobante_pago[fechaPagoRegistrada]'] ? 'has-error' : ''}`}>
						<label
							htmlFor="comprobante_pago_fechaPagoRegistrada"
							className='control-label col-md-4'
						>
							Fecha en que se realizó el pago a registrar:
						</label>
						<div className="col-md-3">
							<input
								type="date"
								id='comprobante_pago_fechaPagoRegistrada'
								className='form-control'
								name='comprobante_pago[fechaPagoRegistrada]'
								required={true}
								max={new Date().toISOString().split('T')[0]} // Prevent future dates
							/>
						</div>
						{errors['comprobante_pago[fechaPagoRegistrada]'] ? 	<p className="help-block">{errors['comprobante_pago[fechaPagoRegistrada]']}</p> : null }
					</div>
					<div className={`form-group ${errors['comprobante_pago[fechaPagoRegistrada]'] ? 'has-error' : ''}`}>
						<label
							htmlFor="comprobante_pago_monto"
							className='control-label col-md-4'
						>
							Monto del comprobante a registrar:<br/>
							<span className='text-danger text-sm'>NOTA: El monto debe coincidir con el comprobante registrado</span>
						</label>
						<div className="col-md-3">
							<div className={`input-group ${errors['comprobante_pago[montoRegistrado]'] ? 'has-error' : ''}`}>
								<div className="input-group-addon">$</div>
								<Cleave
									options={{numeral: true, numeralThousandsGroupStyle: 'thousand'}}
									className='form-control'
									required={true}
									name={'comprobante_pago[montoRegistrado]'}
									onChange={handleMonto}
								/>
							</div>
							{errors['comprobante_pago[montoRegistrado]'] ? 	<p className="text-danger">{errors['comprobante_pago[montoRegistrado]']}</p> : null }
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

					<div className="row mt-30">
						<div className="col-md-4"/>
						<div className="col-md-2">
							<a
								href={`${getSchemeAndHttpHost('/')}`}
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
										<div><span>Escuela de enfermería</span></div>
										<div>{solicitud?.unidad?.nombre}</div>
									</div>
								</div>
								<div className="col-md-4">
									<div className="form-group">
										<div><span>Fecha inicio</span></div>
										<div>{solicitud?.fechaInicioFormatted}</div>
									</div>
								</div>
								<div className="col-md-4">
									<div className="form-group">
										<div><span>Fecha Fin</span></div>
										<div>{solicitud?.fechaFinFormatted}</div>
									</div>
								</div>
							</div>
							<div className="row">
								<div className="col-md-4">
									<div className="form-group">
										<div><span>Alumno</span></div>
										<div>{usuario?.nombre}</div>
									</div>
								</div>
								<div className="col-md-4">
									<div className="form-group">
										<div><span>CURP</span></div>
										<div>{usuario.curp}</div>
									</div>
								</div>
								<div className="col-md-4">
									<div className="form-group">
										<div><span></span></div>
										<div></div>
									</div>
								</div>
							</div>
						</div>
						<div className="col-md-12">
							<div className="row">
								<div className="col-md-6">
									<p className="mb-5">
										Fecha en que se realizó el pago a
										registrar <strong>{formRef && formRef.current ? formRef.current.elements['comprobante_pago[fechaPagoRegistrada]'].value : ''}</strong>
									</p>
								</div>
								<div className="col-md-6">
									<p className="mb-5">
										Monto del comprobante a
										registrar <strong>{formRef && formRef.current ? formRef.current.elements['comprobante_pago[montoRegistrado]'].value : ''} </strong>
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

const DatosSolicitud = ({solicitud, usuario}) => {

	return (
		<div className="col-md-12 mt-30 mb-30">
			<div className="row">
				<div className={'die_container'}>
					<p>
						<strong>Licenciatura en Escuelas de Enfermería del Instituto Mexicano del Seguro Social: </strong>
						{solicitud.unidad.nombreEnfermeria }
					</p>
					<p><strong>Alumno: </strong> {usuario.nombre}</p>
					<p><strong>CURP: </strong> {usuario.curp} </p>
					<p><strong>Email: </strong> {usuario.email} </p>
					<p><strong>Periodo: </strong> {solicitud.periodo} </p>
					<p><strong>Monto: </strong>$ {numberFormat.format(usuario.lastPago.monto)}</p>
					<p><strong>Fecha inicio: </strong> {solicitud.fechaInicioFormatted } </p>
					<p><strong>Fecha fin: </strong> {solicitud.fechaFinFormatted} </p>
					<p><strong>Estado del proceso: </strong>{usuario.statusFormatted}</p>
				</div>
			</div>
		</div>
	)
}


document.addEventListener('DOMContentLoaded', () => {
	ReactDOM.render(
		<EnfermeriaAlumnoCargaComprobante
			usuario={window.Usuario}
			solicitud={window.Usuario.solicitud}
		/>,
		document.getElementById('wrapper-page')
	)
})