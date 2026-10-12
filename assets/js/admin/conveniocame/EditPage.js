import React from 'react';
import ReactDOM from "react-dom";
import ConvenioForm from "../convenioadmin/components/ConvenioForm";
import useFormConvenio from "../convenioadmin/hooks/useFormConvenio";
import Loader from "../../components/Loader/Loader";

function EditPage({maxDateAllowed, convenio, usuario}) {

	const [isLoading, setIsLoading] = React.useState(true)
	const {
		handleSubmit, formErrors,
		setFormErrors, formRef, handleSearchByRfc,
		handleChangeTipoConvenio, showConveniosGenerales,
		handleChandeInstitucion, handleChangeNivelAcademico,
		motivosColaboracion, gradosAcademicos,
		hasConvenios, ooads, instituciones,
		disciplinas, carreras, handleChangeVigenciaText, cargos,
		canFormSubmit, setShowConveniosGenerales, conveniosGenerales, handleChangeCartaIntencion,
		showCOMAEM, selectedOoads, setSelectedOoads,
		selectedCiclosAcademicos, setSelectedCiclosAcademicos, handleChangeTodasOOADs,
		showSelectOOADs, handleChangeTodosCiclosAcademicos, showSelectCiclosAcademicos,
		handleChangeMultiSelect, handleChangeCarrera, handleChangeDisciplina
	} = useFormConvenio({setIsLoading, convenio, isCame: true, usuario});

	return (
		<>
			<Loader show={isLoading}/>
			<ConvenioForm
				useMaxDateAllowed={true}
				isCame={true}
				usuario={usuario}
				maxDateAllowed={maxDateAllowed}
				formRef={formRef}
				handleChangeTodosCiclosAcademicos={handleChangeTodosCiclosAcademicos}
				showSelectCiclosAcademicos={showSelectCiclosAcademicos}
				formErrors={formErrors}
				setFormErrors={setFormErrors}
				handleSubmit={handleSubmit}
				hasConvenios={hasConvenios}
				instituciones={instituciones}
				ooads={ooads}
				motivosColaboracion={motivosColaboracion}
				gradosAcademicos={gradosAcademicos}
				handleChangeTipoConvenio={handleChangeTipoConvenio}
				handleChangeNivelAcademico={handleChangeNivelAcademico}
				handleChandeInstitucion={handleChandeInstitucion}
				handleChangeCarrera={handleChangeCarrera}
				handleChangeDisciplina={handleChangeDisciplina}
				showConveniosGenerales={showConveniosGenerales}
				handleChangeVigenciaText={handleChangeVigenciaText}
				setShowConveniosGenerales={setShowConveniosGenerales}
				handleChangeCartaIntencion={handleChangeCartaIntencion}
				conveniosGenerales={conveniosGenerales}
				disciplinas={disciplinas}
				carreras={carreras}
				cargos={cargos}
				canFormSubmit={canFormSubmit}
				showCOMAEM={showCOMAEM}
				selectedOoads={selectedOoads}
				setSelectedOoads={setSelectedOoads}
				setSelectedCiclosAcademicos={setSelectedCiclosAcademicos}
				selectedCiclosAcademicos={selectedCiclosAcademicos}
				handleChangeTodasOOADs={handleChangeTodasOOADs}
				showSelectOOADs={showSelectOOADs}
				handleChangeMultiSelect={handleChangeMultiSelect}
				isEdit={true}
				handleSearchByRfc={handleSearchByRfc}/>
		</>
	)
}

document.addEventListener('DOMContentLoaded', () => {
	ReactDOM.render(
		<EditPage
			usuario={window.Usuario}
			maxDateAllowed={window.MAX_DATE_ALLOWED}
			convenio={window.Convenio}/>,
		document.querySelector('.react')
	)
});