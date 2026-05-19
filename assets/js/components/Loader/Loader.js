import * as React from 'react';
import './styles.scss';

const Loader = ({show}) => {
    if(show){
        return (
            <div className="loading">Cargando&#8230;</div>
        )
    }
    return <></>
}

export default Loader;