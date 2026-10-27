import React from "react";


class ButtonsCamexMenu extends React.Component {
	render() {
		return (
			<>
				<div className="row">
					<div className="col-md-8">
						<form className="form-inline text-right" method="post" action="/admin/convenioscame/convenioscame/search">
							<div className="form-group">
								<input type="text" className="form-control" name="convenioCamexToSearch" id="convenioCamexToSearch"
											 placeholder="Buscar..."/>
							</div>
							<button type="submit" className="btn btn-default">
								<i className="fa fa-search"></i>
								<span className="hidden-xs hidden-sm">Buscar</span>
							</button>
						</form>
					</div>
				</div>
				<br/>
				<div className="row">
					<div className="col-md-3  ">
						<a className="btn btn-primary action-new butonConve"
							 href="/admin/convenioscame/convenioscame/new" target="_self">
							Crear convenio
						</a>
					</div>
					<div className="col-md-3  ">
						<a className="btn btn-primary action-new butonConve"
							 href="/admin/convenioscame/convenioscame/exportar" target="_self">
							Exportar Convenios
						</a>
					</div>
					<div id="clearFiltersCamex" className="col-md-3   d-none">
						<a className="btn btn-primary action-new butonConve"
							 href="/admin/convenioscame/convenioscame" target="_self">
							Limpiar Filtros
						</a>
					</div>
				</div>
			</>
		)
	}
}

export default ButtonsCamexMenu;