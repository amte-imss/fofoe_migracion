import * as React from "react";

const Buscador = ({
                    handleSearch, handleExport, setSearch,
                  }) => {
  return (
    <div className='row'>
      <div className='col-md-9 mb-15'>
        <div className='navbar-form  '>
          <div className="form-group" style={{width : "50%"}} >
            <input
              type="text"
              placeholder='Buscar por...'
              className='input-sm form-control'
              onChange={({target}) => setSearch(target.value)}
              style={{width:"100%"}}
            />
          </div>
          <button
            type="button"
            className="btn btn-default"
            onClick={handleSearch}
          >
            Buscar
          </button>
        </div>
      </div>
      <div className='col-md-3 mb-15 navbar-form'>
        <button
          type='button'
          className='btn btn-success'
          onClick={handleExport}
        >
          Exportar CSV
        </button>
      </div>
    </div>
  );
}

export default Buscador