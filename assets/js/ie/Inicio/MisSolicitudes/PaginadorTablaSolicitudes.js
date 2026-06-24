import * as React from 'react'
import ReactPaginate from "react-paginate";

const PaginadorTablaSolicitudes = ({
                                     pagination,
                                     setCurrentPage,
                                     currentPage,
                                     setPerPage,
                                     perPage,
                                     DEFAULT_PAGE,
                                     PER_PAGE_DEFAULT_SELECT_VALUES,
                                   }) => {

  return (
    pagination.totalCount > 0 ?
      <div className="row">
        <div className="col-md-12">
          <ReactPaginate
            previousLabel={'Anterior'}
            nextLabel={'Siguiente'}
            breakLabel={'...'}
            breakClassName={'break-me'}
            pageCount={pagination.pageCount}
            marginPagesDisplayed={5}
            pageRangeDisplayed={2}
            initialPage={currentPage - 1}
            forcePage={currentPage - 1}
            onPageChange={(currentPage) => {
              setCurrentPage(currentPage.selected + 1)
            }}
            containerClassName={'pagination'}
            subContainerClassName={'pages pagination'}
            activeClassName={'active'}
          />
        </div>
        <div className="col-md-2">
          <div className="form-group">
            <select
              name=""
              id=""
              className='form-control'
              onChange={({ target }) => {
                setCurrentPage(DEFAULT_PAGE)
                setPerPage(parseInt(target.value))
              }}
              value={perPage}
            >
              <option value={PER_PAGE_DEFAULT_SELECT_VALUES.FIRST_OPTION}>
                Mostrar {PER_PAGE_DEFAULT_SELECT_VALUES.FIRST_OPTION}
              </option>
              <option value={PER_PAGE_DEFAULT_SELECT_VALUES.SECOND_OPTION}>
                Mostrar {PER_PAGE_DEFAULT_SELECT_VALUES.SECOND_OPTION}
              </option>
              <option value={PER_PAGE_DEFAULT_SELECT_VALUES.THIRD_OPTION}>
                Mostrar {PER_PAGE_DEFAULT_SELECT_VALUES.THIRD_OPTION}
              </option>
            </select>
          </div>
        </div>
      </div>
      : null
  )
}

export default PaginadorTablaSolicitudes