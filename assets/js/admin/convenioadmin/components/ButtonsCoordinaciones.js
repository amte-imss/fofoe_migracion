import React from "react";



class ButtonsCoordinaciones extends React.Component{
    render() {
        return (
            <div className="row">                
                <div className="col-md-3 mr-btn-headerConAdmin-coord mt-btn-headerConAdmin">                    
                    <a className="btn btn-primary action-new butonCoordinaciones" 
                        href="/admin/coordfirmante/coordfirmante/new" target="_self">
                        Crear Coordinación Firmante
                    </a>
                </div>                            
            </div>
        )
    }
}
export default ButtonsCoordinaciones;