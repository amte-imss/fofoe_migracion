import React from "react";


class ButtonsMenu extends React.Component {
	render() {
		return (
			<>
				<div className="row">
					<div className="col-md-12">
						<form className="form-inline text-right" method="post"
									action="/admin/conveniosadmin/conveniosadmin/search">
							<div className="form-group">
								<input type="text" className="form-control" name="convenioToSearch" id="convenioToSearch"
											 placeholder="Buscar..."/>
							</div>
							<button className="btn btn-default" type="submit" target="_self">
								<i className="fa fa-search"></i>
								<span className="hidden-xs hidden-sm">Buscar</span>
							</button>
						</form>
					</div>
				</div>
				<br/>
				<div className="row">
					<div className="col-md-3  mr-btn-headerConAdmin-admin ">
						<a className="btn btn-primary action-new butonConve"
							 href="/admin/conveniosadmin/conveniosadmin/new" target="_self">
							Crear convenio
						</a>
					</div>
					<div className="col-md-3 mr-btn-headerConAdmin-admin ">
						<a className="btn btn-primary action-new butonConve"
							 href="/admin/conveniosadmin/conveniosadmin/exportar" target="_self">
							Exportar Convenios
						</a>
					</div>
					<div className="col-md-3 mr-btn-headerConAdmin-admin ">
						<a className="btn btn-primary action-new butonConve"
							 href="/admin/conveniosadmin/conveniosadmin/carga">
							Carga masiva
						</a>
					</div>
					<div id="clearFiltersAdmin" className="col-md-3 mr-btn-headerConAdmin-admin  d-none">
						<a className="btn btn-primary action-new butonConve"
							 href="/admin/conveniosadmin/conveniosadmin" target="_self">
							Limpiar Filtros
						</a>
					</div>
				</div>
			</>
		)
	}
}

export default ButtonsMenu;