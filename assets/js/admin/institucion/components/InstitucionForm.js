import React, {useState, useEffect} from 'react';
import {FormValidator} from "../../../components/FormValidator/FormValidator";
import Popup from "../../../components/Popup/Popup";
import {getSchemeAndHttpHost} from "../../../utils";

export default function InstitucionForm({
																					formRef, formErrors,
																					handleSubmit, setFormErrors,
																					canFormSubmit, ooads,
																					institucion = null, hasCedula=false
																				}) {
	// Estado para manejar el nombre del archivo PDF
	const [pdfFileName, setPdfFileName] = useState('');
	const [isPopupOpen, setIsPopupOpen] = useState(false);

	useEffect(() => {
		setTimeout(() => {
			formRef.current.elements['institucion[delegacion]'].value = institucion && institucion.delegacion ? institucion.delegacion.id.toString() : '';
			formRef.current.querySelector('[type="submit"]').disabled = !canFormSubmit();
		}, 1000);
	}, []);

	const handleFileChange = (event) => {
		const file = event.target.files[0];
		if (file) {
			setPdfFileName(file.name);
		}
	};

	return (
		<>
			{institucion &&
				<Popup show={isPopupOpen}
							 closePopup={() => {setIsPopupOpen(false);}}
							 size={'large'}
							 body={<>
								 <iframe width={'100%'} height={'450px'} src={getSchemeAndHttpHost(`/admin/institucion/${institucion.id}/cedula?inline`)} frameborder="0"></iframe>
							 </>}
				/>}
			<FormValidator errors={formErrors} setErrors={setFormErrors}>
				<form onSubmit={handleSubmit} ref={formRef} noValidate autoComplete={'off'} encType="multipart/form-data">
					<div className="container">
						<div className="row mb-3">
							<div className="col-md-6">
								<label htmlFor="rfc"><strong>(*)RFC</strong></label>
								<input type={'text'} id="rfc"
											 className="form-control"
											 name="institucion[rfc]"
											 defaultValue={institucion ? institucion.rfc : ''}
											 required={true}
								/>
								<small className="text-danger">{formErrors['institucion[rfc]']}</small>
							</div>
							<div className="col-md-6">
								<label htmlFor="razonSocial"><strong>(*)Razón Social</strong></label>
								<input type={'text'} id="razonSocial"
											 className="form-control"
											 name="institucion[razonSocial]"
											 defaultValue={institucion ? institucion.razonSocial : ''}
											 required={true}
								/>
								<small className="text-danger">{formErrors['institucion[razonSocial]']}</small>
							</div>
						</div>

						<div className="row mb-3">
							<div className="col-md-6">
								<label htmlFor="nombre"><strong>(*)Nombre Comercial</strong></label>
								<input type={'text'} id="nombre"
											 className="form-control"
											 name="institucion[nombre]"
											 defaultValue={institucion ? institucion.nombre : ''}
											 required={true}
								/>
								<small className="text-danger">{formErrors['institucion[nombre]']}</small>
							</div>
							<div className="col-md-6">
								<label htmlFor="ooad"><strong>(*)OOAD</strong></label>
								<select id="ooad"
												className="form-control"
												name="institucion[delegacion]"
												required={true}
								>
									<option value="">Seleccionar</option>
									{ooads.map((item, index) => {
										return (<option key={index} value={item.id}>{item.nombre}</option>)
									})}
								</select>
								<small className="text-danger">{formErrors['institucion[delegacion]']}</small>
							</div>
						</div>

						<div className="row mb-3">
							<div className="col-md-12">
								<label htmlFor="email"><strong>(*)Correo</strong></label>
								<input type={'email'} id="email"
											 className="form-control"
											 name="institucion[correo]"
											 defaultValue={institucion ? institucion.correo : ''}
											 required={true}
								/>
								<small className="text-danger">{formErrors['institucion[correo]']}</small>
							</div>
						</div>

						{/* Nuevos Campos Opcionales */}
						<div className="row mb-3">
							<div className="col-md-12">
								<label htmlFor="sitioWeb">Sitio web</label>
								<input type={'url'} id="sitioWeb"
											 className="form-control"
											 name="institucion[sitioWeb]"
											 defaultValue={institucion ? institucion.sitioWeb : ''}
											 pattern="https?://.+"  // Patrón opcional para reforzar que comience con http o https
								/>
								<small className="text-danger">{formErrors['institucion[sitioWeb]']}</small>
							</div>
						</div>

						<div className="row mb-3">
							<div className="col-md-12">
								<label htmlFor="direccion">Dirección</label>
								<input type={'text'} id="direccion"
											 className="form-control"
											 name="institucion[direccion]"
											 defaultValue={institucion ? institucion.direccion : ''}
								/>
								<small className="text-danger">{formErrors['institucion[direccion]']}</small>
							</div>
						</div>

						<div className="row mb-3">
							<div className="col-md-6">
								<label htmlFor="fax">Fax</label>
								<input type={'text'} id="fax"
											 className="form-control"
											 name="institucion[fax]"
											 defaultValue={institucion ? institucion.fax : ''}
											 pattern="\d{10,15}"  // Permite entre 10 y 15 dígitos
											 minLength="10"  // Asegura un mínimo de 10 caracteres
											 maxLength="15"  // Limita el campo a un máximo de 15 caracteres
											 onInput={(e) => e.target.value = e.target.value.replace(/\D/g, '')}  // Permite solo números
								/>
								<small className="text-danger">{formErrors['institucion[fax]']}</small>
							</div>

							<div className="col-md-6">
								<div className='row'>
									<div className="col-md-4">
										<label htmlFor="extension">Extensión</label>
										<input type={'text'} id="extension"
													 className="form-control"
													 name="institucion[extension]"
													 maxLength="5"
													 defaultValue={institucion ? institucion.extension : ''}
										/>
										<small className="text-danger">{formErrors['institucion[extension]']}</small>
									</div>

									<div className="col-md-8">
										<label htmlFor="telefono">Teléfono</label>
										<input type={'text'} id="telefono"
													 className="form-control"
													 name="institucion[telefono]"
													 defaultValue={institucion ? institucion.telefono : ''}
													 pattern="\d{10}"  // Asegura que sean exactamente 10 dígitos
													 maxLength="10"  // Limita el campo a un máximo de 10 caracteres
													 onInput={(e) => e.target.value = e.target.value.replace(/\D/g, '')}  // Permite solo números
											// removed required={true}
										/>
										<small className="text-danger">{formErrors['institucion[telefono]']}</small>
									</div>
								</div>
							</div>
						</div>

						{institucion && hasCedula &&
						<div className="row mb-3">
							<div className="col-md-12">
								<button
									onClick={() => {setIsPopupOpen(true)}}
									type={'button'}
									className="btn btn-secondary btn-block">Ver Cedula Fiscal
								</button>
							</div>
						</div>}

						<div className="row mb-3">
							<div className="col-md-12">
								<label htmlFor="cedulaFile">Cargar Cédula Fiscal (PDF)</label>
								<input type="file" id="cedulaFile"
											 className="form-control"
											 name="institucion[cedulaFile]"
											 accept=".pdf"
											 onChange={handleFileChange}
								/>
								<small className="text-danger">{formErrors['institucion[cedulaFile]']}</small>
							</div>
						</div>


						<div className="row mb-3">
							<div className="col-md-12">
								<input type="text" id="cedulaIdentificacion"
											 className={`form-control ${pdfFileName ? 'bg-success text-white' : 'bg-muted'}`}
											 name="institucion[cedulaIdentificacion]"
											 value={pdfFileName}
											 disabled
								/>
								<small className="text-danger">{formErrors['institucion[cedulaIdentificacion]']}</small>
							</div>
						</div>

						<div className="row mb-3">
							<div className='col-md-3'></div>
							<div className="col-md-6">
								<label htmlFor="submit">&nbsp;</label>
								<button
									id={'submit'}
									type={'submit'}
									disabled={!canFormSubmit()}
									className="btn btn-success btn-block">Guardar
								</button>
							</div>
							<div className='col-md-3'></div>
						</div>
					</div>
				</form>
			</FormValidator>
		</>
	)
}