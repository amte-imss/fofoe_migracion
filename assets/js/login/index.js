import * as React from 'react'
import ReactDOM from 'react-dom'
import {getSchemeAndHttpHost} from "../utils";
import Loader from "../components/Loader/Loader";
import FormValidator from "../components/FormValidator/FormValidatorVanilla";
import  "../components/FormValidator/FormValidator.css";

const Captcha = ({captchaImgIni}) => {
	const [captchaImg, setCaptchaImg] = React.useState(captchaImgIni);
	const [isLoading, setIsLoading] = React.useState(false);

	const reloadCaptcha = (e) => {
		e.preventDefault()
		setIsLoading(true)
		const xhttp = new XMLHttpRequest();
		xhttp.open('GET', `${getSchemeAndHttpHost()}/captcha/reload`, true);
		xhttp.onreadystatechange = function () {
			if (this.readyState === 4 && this.status === 200) {
				setCaptchaImg(this.responseText)
				setIsLoading(false)
			}
		};
		xhttp.send();
	}

	return (
		isLoading ? <Loader show={isLoading}/>
			: <div className="captcha-container">
				<div className="form-group">
					<label htmlFor="_captcha">Introduce el código aquí</label>
					<input type="text" name="captcha" id="_captcha" required className="form-control"/>
				</div>
				<div className="row">
					<div className="col-md-12">
						<img src={captchaImg} id="captcha" alt="Captcha" className="img-responsive"
								 style={{
									 display: 'inline-block'
								 }}/>
						<a href={"#"} onClick={reloadCaptcha} className={"btn btn-link"}>¡Generar nuevo código!</a>
					</div>
				</div>
			</div>
	)
}

const CustomLogin = ({
											 error,
											 tipo,
											 path_login,
											 last_username,
											 url_img,
											 captcha
										 }) => {

	const [passwordShown, setPasswordShown] = React.useState(false);
	const [isLoading, setIsLoading] = React.useState(false);
	const formRef = React.useRef(null)
	const labelUsername = tipo === 'residente' ?
		'Usuario'
		: 'Nombre de Usuario o matrícula'

	const valueLastUsername = last_username && tipo === 'residente' ?
		last_username.substring(4)
		: last_username

	const title = tipo !== 'convenio' ? 'Sistema de Administración del FOFOE' : 'Sistema de Información de Colaboraciones Académicas de la Coordinación de Educación en Salud (SICACES)';

	const togglePassword = () => {
		setPasswordShown(!passwordShown);
	};

	const handleSubmit = (e) => {
		e.preventDefault()
		setIsLoading(true)
		const currentUsername = formRef.current.elements['_username'].value
		// formRef.current.elements['_username'].value = tipo === 'residente' ? 'RES_' + currentUsername : currentUsername; // ya no se va usar porque los residentes van a poder ingresar por medio de su curp
		formRef.current.submit();
	}

	React.useEffect(() => {
		const formValidator = new FormValidator(formRef.current);
		formValidator.enableValidation();
	}, [])

	return (
		<div className="row">
			{tipo !== 'convenio' ?
				<div className="col-md-8" style={{
					backgroundImage: `url(${url_img})`,
					backgroundSize: '900px 640px',
					height: '620px',
					backgroundRepeat: 'no-repeat',
					backgroundPosition: 'bottom'
				}}>
				< /div>
				: <div className={'col-md-4'}></div>}
			<div className="col-md-4">
				<div style={{
					paddingTop: '40px',
					height: '620px'
				}}>
					<h1 className="mb-20 text-center"
							style={{maxWidth: "500px", marginLeft: 'auto', marginRight: 'auto'}}>{title}</h1>
					<div className="row">
						<div className="col-md-1"></div>
						{isLoading ? <Loader show={isLoading}/> : null}
						<div className="col-md-10" style={{display: (isLoading ? 'none' : 'initial')}}>
							<form action={path_login}
										method="post"
										ref={formRef}
								        autoComplete={'off'}
										onSubmit={handleSubmit}
							>
								{error &&
									<div className="alert alert-danger">
										<p>{error}</p>
									</div>
								}
								<div className="form-group">
									<label htmlFor="_username">{labelUsername}</label>
									<input type="text"
                         id={'f-username'}
												 required className="form-control form__input" name="_username"
												 defaultValue={valueLastUsername ?? ''}
									/>
									<span className={'form__error f-username-error'}></span>
								</div>
								<div className="form-group">
									<label htmlFor="_password">Contraseña</label>
									<input type={passwordShown ? "text" : "password"}
												 id={'f-password'}
												 className="form-control  form__input" name="_password"/>
									<span className={'form__error f-password-error'}></span>
									<input type="checkbox" onClick={togglePassword}/>Mostrar
								</div>
								<Captcha captchaImgIni={captcha}/>

								<div className="row mt-20">
									<div className="col-md-12">
										{tipo === 'residente' &&
											<label htmlFor={'accept_agreement'}>
												<input className={' form__input'}
															id={'f-agreement'}
															 type={'checkbox'} style={{marginRight: '10px'}}
															 name={'accept_agreement'} required/>
												ACEPTO LAS <a href={getSchemeAndHttpHost('/aviso-privacidad')} target={'_blank'}>POLÍTICAS DE PAGO DE CUOTAS DE RECUPERACIÓN AL FOFOE</a></label>
										}
										{tipo === 'residente' &&
											<div className={'form__error f-agreement-error'}></div>
										}
									</div>
								</div>

								<div className="row mt-20">
									<div className="col-md-12">
										<button type="submit" className="btn btn-success btn-block form__submit">Ingresar</button>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	)
}

document.addEventListener('DOMContentLoaded', () => {
	const loginDom = document.getElementById('content-wrapper-login');

	if (loginDom) {
		ReactDOM.render(
			<CustomLogin
				error={window.ERROR}
				path_login={window.PATH_LOGIN}
				last_username={window.LAST_USERNAME}
				tipo={window.TIPO}
				url_img={window.URL_IMG}
				captcha={window.CAPTCHA}
			/>, loginDom
		)
	}
})