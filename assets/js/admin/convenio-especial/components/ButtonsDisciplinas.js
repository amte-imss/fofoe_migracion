import React from "react";



class ButtonsDisciplinas extends React.Component{
    render() {
        return (
            <div className="row">                
                <div className="col-md-3 mr-btn-headerConAdmin-coord mt-btn-headerConAdmin">                    
                    <a className="btn btn-primary action-new butonCoordinaciones" 
                        href="disciplina/new" target="_self">
                        Crear Disciplina
                    </a>
                </div>                            
            </div>
        )
    }
}
export default ButtonsDisciplinas;