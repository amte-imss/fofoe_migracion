import React from "react";

function getUrl(file) {
	return URL.createObjectURL(file);
}

export default function PreviewDocument({file}) {
	return (
		<>
			{file && file.type ?
				<>
					{file.type === 'application/pdf' ?
					<>
						<iframe src={getUrl(file)} className={'iframe'}></iframe>
					</> :
						<img src={getUrl(file)} alt="Documento comprobante"/>
					}
				</>
			: null}
		</>
	)
}