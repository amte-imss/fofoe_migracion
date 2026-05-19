import React from "react";
import './Popup.css';

export default function Popup({body, show, title, footer, closePopup = () => {}, size = 'medium', canClose = true}) {

	const handleOutClick = (event) => {
		if (event.target.classList.contains('popup__overlay')) {
			closePopup();
		}
	}

	return (
		<>
			{show &&
				<div className="popup popup_show" onClick={handleOutClick}>
					<div className="popup__overlay"></div>
					<div className={`popup__content popup__content_size_${size}`}>
						<div className="popup__header">
							<h3 className={'popup__title'}>{title}</h3>
							{canClose && <button type="button" className={'popup__close'} onClick={closePopup}>&times;</button> }
						</div>
						<div className="popup__body">
							{body}
						</div>
						<div className="popup__footer">
							{footer ? footer :
							<>
								<div className="popup__footer-actions">
									<input type="button" className={'btn btn-secondary btn-block'} onClick={closePopup} value={'Aceptar'}/>
								</div>
							</>
							}
						</div>
					</div>
				</div>
			}
		</>
	)
}