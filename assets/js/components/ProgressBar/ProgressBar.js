import React from "react";
import './ProgressBar.css';
export default function ProgressBar({total, current}){

	const percent = ((current * 100) / total)+'%';

	return (
		<>
			<div className="progress">
				<div className="progress__bar" style={{width: percent}}></div>
			</div>
			<p>Enviando {current} de {total}</p>
		</>
	)
}