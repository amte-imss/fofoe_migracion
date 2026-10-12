// assets/js/admin/convenioadmin/conveniosFunctions.js

// Aqui empieza tu código JavaScript

import Swal from "sweetalert2";
import {getSchemeAndHttpHost} from "../../utils";

//Convenios Admin
jQuery(document).ready(function(){
//    document.querySelector('#appbundle_convenio_carga_file').addEventListener('change', function() { alert('hola');});
    var urlActual = window.location.href;
    var urlNueva = urlActual.replace("conveniosadmin/new", "getconveniogral");
    if (urlActual.indexOf("edit") !== -1) {
        urlNueva = urlActual.replace("conveniosadmin/edit", "getconveniogral" );
    }
    
    var rfcRegex = /^[A-ZÑ&]{3,4}\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])[A-Z\d]{2}[A\d]$/; 
    var selected = jQuery('#appbundle_convenio_admin_carrera').val();     
    //Evento onchange de RFC Admin
    if (document.getElementById("appbundle_convenio_admin_rfc")) {
        document.getElementById("appbundle_convenio_admin_rfc").addEventListener("blur", function() {   
            if(typeof jQuery('#appbundle_convenio_admin_rfc').val() !== 'undefined'){
                if(rfcRegex.test(jQuery('#appbundle_convenio_admin_rfc').val())){
                    jQuery("#appbundle_convenio_admin_rfcEnviado").val(jQuery('#appbundle_convenio_admin_rfc').val());
                    $.ajax({
                        url: urlNueva,
                        type: "POST",
                        data: { rfc: jQuery('#appbundle_convenio_admin_rfc').val() },
                        showLoader: true,
                        success: function(response) {
                            console.log(response);
                            jQuery('#appbundle_convenio_admin_convenio_general').empty().append('<option disabled selected hidden>Selecciona Convenio General</option>');    
                            jQuery('#appbundle_convenio_admin_razon_social').val("");
                            jQuery('#appbundle_convenio_admin_institucion').empty().append('<option disabled selected hidden>Selecciona Institución</option>');    

                            if (!jQuery.isEmptyObject(response) && JSON.parse(response).status === "si") {
                                $.each(JSON.parse(response).data, function(index, opcion) {
                                    jQuery('#appbundle_convenio_admin_convenio_general').append(jQuery('<option>', {
                                        value: opcion.id,
                                        text: opcion.nombre
                                    }));	
                                });  
                                $.each(JSON.parse(response).instituciones, function(index1, opcion1) {
                                    jQuery('#appbundle_convenio_admin_institucion').append(jQuery('<option>', {
                                        value: opcion1.id,
                                        text: opcion1.nombre
                                    }));
                                    // Seleccionar la opción directamente
                                    jQuery('#appbundle_convenio_admin_institucion').val(opcion1.id);
                                });  
                               if(JSON.parse(response).razonSocial !== "ambigua"){
                                //codigo para mostrar todos los campos del formulario
                                document.getElementById('appbundle_convenio_admin_razon_social').style.display = 'block';
                                document.getElementById('appbundle_convenio_admin_institucion').style.display = 'block';
                                document.getElementById('appbundle_convenio_admin_nombre').style.display = 'block';
                                document.getElementById('appbundle_convenio_admin_tipo').style.display = 'block';
                                document.getElementById('appbundle_convenio_admin_convenio_general').style.display = 'block';
                                document.getElementById('appbundle_convenio_admin_delegacion').style.display = 'block';
                                document.getElementById('appbundle_convenio_admin_sector').style.display = 'block';
                                document.getElementById('appbundle_convenio_admin_objetivo_colaboracion').style.display = 'block';
                                document.getElementById('appbundle_convenio_admin_cicloAcademico').style.display = 'block';
                                document.getElementById('appbundle_convenio_admin_nivelAcademico').style.display = 'block';
                                document.getElementById('appbundle_convenio_admin_disciplina').style.display = 'block';
                                document.getElementById('appbundle_convenio_admin_carrera').style.display = 'block';
                                document.getElementById('appbundle_convenio_admin_nombre_programa').style.display = 'block';
                                document.getElementById('appbundle_convenio_admin_fecha_firma').style.display = 'block';
                                document.getElementById('appbundle_convenio_admin_vigencia_text').style.display = 'block';
                                document.getElementById('appbundle_convenio_admin_fecha_vence_convenio').style.display = 'block';
                                document.getElementById('appbundle_convenio_admin_fecha_ota').style.display = 'block';
                                document.getElementById('appbundle_convenio_admin_fecha_vence_ota').style.display = 'block';
                                document.getElementById('appbundle_convenio_admin_fecha_rvoe').style.display = 'block';
                                document.getElementById('appbundle_convenio_admin_fecha_vence_rvoe').style.display = 'block';
                                document.getElementById('appbundle_convenio_admin_fecha_emite_comaem').style.display = 'block';
                                document.getElementById('appbundle_convenio_admin_fecha_vence_comaem').style.display = 'block';
                                document.getElementById('appbundle_convenio_admin_cargoFirmaIdFirst').style.display = 'block';
                                document.getElementById('appbundle_convenio_admin_cargo_emite_signfirst').style.display = 'block';
                                document.getElementById('appbundle_convenio_admin_recibe_signfirst').style.display = 'block';
                                document.getElementById('appbundle_convenio_admin_nombre_emite_signfirst').style.display = 'block';
                                document.getElementById('appbundle_convenio_admin_cargoFirmaIdSecond').style.display = 'block';
                                document.getElementById('appbundle_convenio_admin_cargo_emite_signsecond').style.display = 'block';
                                document.getElementById('appbundle_convenio_admin_recibe_signsecond').style.display = 'block';
                                document.getElementById('appbundle_convenio_admin_nombre_emite_signsecond').style.display = 'block';
                                document.getElementById('sendConvenioAdmin').style.display = 'block';
                                //ocultar etiquetas de cada componenete 
                                document.getElementById('labelrazonsocial').style.display = 'block';
                                document.getElementById('nombreConvenioAdminLbl').style.display = 'block';
                                document.getElementById('convenioGralId').style.display = 'block';
                                document.getElementById('sectorAdminLblId').style.display = 'block';
                                document.getElementById('objAdminLblId').style.display = 'block';
                                document.getElementById('labeltipo').style.display = 'block';
                                document.getElementById('conveniolabel').style.display = 'block';
                                document.getElementById('motivoAdminLblId').style.display = 'block';
                                document.getElementById('appbundle_convenio_nivelAcademicoL').style.display = 'block';
                                document.getElementById('disciplinaAdminLblId').style.display = 'block';
                                document.getElementById('carreraAdminLblId').style.display = 'block';
                                document.getElementById('nomProAdminLblId').style.display = 'block';
                                document.getElementById('fechaFirmaAdminLblId').style.display = 'block';
                                document.getElementById('vigenciaAnios').style.display = 'block';
                                document.getElementById('jobSignImssAdminUno').style.display = 'block';
                                document.getElementById('jobSignInstAdminUno').style.display = 'block';
                                document.getElementById('labelooad').style.display = 'block';
                                document.getElementById('labelfecvigen1').style.display = 'block';
                                document.getElementById('labelfechaota').style.display = 'block';
                                document.getElementById('labelota2').style.display = 'block';
                                document.getElementById('labelrvoe').style.display = 'block';
                                document.getElementById('labelrvoe2').style.display = 'block';
                                document.getElementById('labelcomaem').style.display = 'block';
                                document.getElementById('labelcomaem2').style.display = 'block';
                                document.getElementById('labelimss1').style.display = 'block';
                                document.getElementById('labelfirmante1').style.display = 'block';
                                document.getElementById('labelimss2').style.display = 'block';
                                document.getElementById('labelfirmante2').style.display = 'block';
                                document.getElementById('labelfirmaimss2').style.display = 'block';
                                document.getElementById('labelnombrefir2').style.display = 'block';
                                document.getElementById('etiquetacamposreque').style.display = 'block';

                                //codigo para habilitar todos los campos del formulario nuevo convenio
                                document.querySelector('#appbundle_convenio_admin_nombre').disabled = false;
                                document.querySelector('#appbundle_convenio_admin_tipo').disabled = false;
                                document.querySelector('#appbundle_convenio_admin_convenio_general').disabled = false;
                                document.querySelector('#appbundle_convenio_admin_delegacion').disabled = false;
                                document.querySelector('#appbundle_convenio_admin_sector').disabled = false;
                                document.querySelector('#appbundle_convenio_admin_objetivo_colaboracion').disabled = false;
                                document.querySelector('#appbundle_convenio_admin_cicloAcademico').disabled = false;
                                document.querySelector('#appbundle_convenio_admin_nivelAcademico').disabled = false;
                                document.querySelector('#appbundle_convenio_admin_disciplina').disabled = false;
                                document.querySelector('#appbundle_convenio_admin_carrera').disabled = false;
                                document.querySelector('#appbundle_convenio_admin_nombre_programa').disabled = false;
                                document.querySelector('#appbundle_convenio_admin_fecha_firma').disabled = false;
                                document.querySelector('#appbundle_convenio_admin_vigencia_text').disabled = false;
                                document.querySelector('#appbundle_convenio_admin_fecha_vence_convenio').disabled = false;
                                document.querySelector('#appbundle_convenio_admin_fecha_ota').disabled = false;
                                document.querySelector('#appbundle_convenio_admin_fecha_vence_ota').disabled = false;
                                document.querySelector('#appbundle_convenio_admin_fecha_rvoe').disabled = false;
                                document.querySelector('#appbundle_convenio_admin_fecha_vence_rvoe').disabled = false;
                                document.querySelector('#appbundle_convenio_admin_fecha_emite_comaem').disabled = false;
                                document.querySelector('#appbundle_convenio_admin_fecha_vence_comaem').disabled = false;
                                document.querySelector('#appbundle_convenio_admin_cargoFirmaIdFirst').disabled = false;
                                document.querySelector('#appbundle_convenio_admin_cargo_emite_signfirst').disabled = false;
                                document.querySelector('#appbundle_convenio_admin_recibe_signfirst').disabled = false;
                                document.querySelector('#appbundle_convenio_admin_nombre_emite_signfirst').disabled = false;
                                document.querySelector('#appbundle_convenio_admin_cargoFirmaIdSecond').disabled = false;
                                document.querySelector('#appbundle_convenio_admin_cargo_emite_signsecond').disabled = false;
                                document.querySelector('#appbundle_convenio_admin_recibe_signsecond').disabled = false;
                                document.querySelector('#appbundle_convenio_admin_nombre_emite_signsecond').disabled = false;
                                document.querySelector('#sendConvenioAdmin').disabled = false;

                                jQuery('#appbundle_convenio_admin_razon_social').val(JSON.parse(response).razonSocial);
                                //jQuery('#appbundle_convenio_admin_nombre').val(JSON.parse(response).razonSocial);
                               }else{
                                    Swal.fire(
                                        "La razón social de esta institucion, es ambigua, por favor revisar los registros.",
                                        '',
                                        'warning',
                                    ).then(() => {                            

                                    });   
                                    jQuery('#appbundle_convenio_admin_convenio_general').empty().append('<option disabled selected hidden>Selecciona Convenio General</option>');    
                                    jQuery('#appbundle_convenio_admin_razon_social').val("");
                                    jQuery('#appbundle_convenio_admin_institucion').empty().append('<option disabled selected hidden>Selecciona Institución</option>');    
                               }                      
                            } else {
                                console.log("El response está vacío: "+response);
                                Swal.fire(
                                    "El R.F.C. capturado, no obtuvo registros de Convenios Generales. Debes crear el Convenio General Correspondiente.",
                                    '',
                                    'warning',
                                ).then(() => {                            

                                });   
                                jQuery('#appbundle_convenio_admin_convenio_general').empty().append('<option disabled selected hidden>Selecciona Convenio General</option>');    
                                if(jQuery.isEmptyObject(JSON.parse(response).instituciones)){
                                    jQuery('#appbundle_convenio_admin_razon_social').val("");
                                    jQuery('#appbundle_convenio_admin_institucion').empty().append('<option disabled selected hidden>Selecciona Instituicón</option>');
                                }else{
                                    jQuery('#appbundle_convenio_admin_razon_social').val(JSON.parse(response).razonSocial);
                                    //jQuery('#appbundle_convenio_admin_nombre').val(JSON.parse(response).razonSocial);
                                    $.each(JSON.parse(response).instituciones, function(index1, opcion1) {
                                    jQuery('#appbundle_convenio_admin_institucion').append(jQuery('<option>', {
                                        value: opcion1.id,
                                        text: opcion1.nombre
                                    }));
                                    // Seleccionar la opción directamente
                                    jQuery('#appbundle_convenio_admin_institucion').val(opcion1.id);
                                });  
                                } 
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error(xhr.responseText);
                            alert(xhr.responseText);
                        }
                    });  
                }else{
                    Swal.fire("RFC vacio, digita uno valido", '', 'warning',).then(() => { });
                    //limpiar el nombre que se pasa al input
                    document.querySelector('#appbundle_convenio_admin_nombre').value = "";
                    //deshabiltar todos los campos del form ya que no hay datos encontrados
                    document.querySelector('#appbundle_convenio_admin_nombre').disabled = true;
                    document.querySelector('#appbundle_convenio_admin_tipo').disabled = true;
                    document.querySelector('#appbundle_convenio_admin_convenio_general').disabled = true;
                    document.querySelector('#appbundle_convenio_admin_delegacion').disabled = true;
                    document.querySelector('#appbundle_convenio_admin_sector').disabled = true;
                    document.querySelector('#appbundle_convenio_admin_objetivo_colaboracion').disabled = true;
                    document.querySelector('#appbundle_convenio_admin_cicloAcademico').disabled = true;
                    document.querySelector('#appbundle_convenio_admin_nivelAcademico').disabled = true;
                    document.querySelector('#appbundle_convenio_admin_disciplina').disabled = true;
                    document.querySelector('#appbundle_convenio_admin_carrera').disabled = true;
                    document.querySelector('#appbundle_convenio_admin_nombre_programa').disabled = true;
                    document.querySelector('#appbundle_convenio_admin_fecha_firma').disabled = true;
                    document.querySelector('#appbundle_convenio_admin_vigencia_text').disabled = true;
                    document.querySelector('#appbundle_convenio_admin_fecha_vence_convenio').disabled = true;
                    document.querySelector('#appbundle_convenio_admin_fecha_vence_convenio').disabled = true;
                    document.querySelector('#appbundle_convenio_admin_fecha_ota').disabled = true;
                    document.querySelector('#appbundle_convenio_admin_fecha_vence_ota').disabled = true;
                    document.querySelector('#appbundle_convenio_admin_fecha_rvoe').disabled = true;
                    document.querySelector('#appbundle_convenio_admin_fecha_vence_rvoe').disabled = true;
                    document.querySelector('#appbundle_convenio_admin_fecha_emite_comaem').disabled = true;
                    document.querySelector('#appbundle_convenio_admin_fecha_vence_comaem').disabled = true;
                    document.querySelector('#appbundle_convenio_admin_cargoFirmaIdFirst').disabled = true;
                    document.querySelector('#appbundle_convenio_admin_cargo_emite_signfirst').disabled = true;
                    document.querySelector('#appbundle_convenio_admin_recibe_signfirst').disabled = true;
                    document.querySelector('#appbundle_convenio_admin_nombre_emite_signfirst').disabled = true;
                    document.querySelector('#appbundle_convenio_admin_cargoFirmaIdSecond').disabled = true;
                    document.querySelector('#appbundle_convenio_admin_cargo_emite_signsecond').disabled = true;
                    document.querySelector('#appbundle_convenio_admin_recibe_signsecond').disabled = true;
                    document.querySelector('#appbundle_convenio_admin_nombre_emite_signsecond').disabled = true;
                    document.querySelector('#sendConvenioAdmin').disabled = true;

                    jQuery('#appbundle_convenio_admin_convenio_general').empty().append('<option disabled selected hidden>Selecciona Convenio General</option>'); 
                    jQuery('#appbundle_convenio_admin_razon_social').val("");
                                jQuery('#appbundle_convenio_admin_institucion').empty().append('<option disabled selected hidden>Selecciona Institución</option>');    
                }
            }else{
                Swal.fire(
                    "RFC no encontrado",
                    '',
                    'warning',
                ).then(() => {                            

                });   
                jQuery('#appbundle_convenio_admin_convenio_general').empty().append('<option disabled selected hidden>Selecciona Convenio General</option>'); 
                jQuery('#appbundle_convenio_admin_razon_social').val("");
                jQuery('#appbundle_convenio_admin_institucion').empty().append('<option disabled selected hidden>Selecciona Institución</option>');    
            }        
        });
    }
    ///aqui termina el codigo a modificar
  
    //Evento onchange Carrera Admin
    jQuery('#appbundle_convenio_admin_carrera').change(function() {
        var urlNuevaGetCarrera = urlNueva.replace("getconveniogral", "getcarrera");
        $.ajax({
            url: urlNuevaGetCarrera,
            type: "POST",
            data: { selectedId: jQuery('#appbundle_convenio_admin_carrera').val() },
            success: function(response) {       
                if(JSON.parse(response).status === "si"){
                    Swal.fire(
                        "Has Seleccionado la Opción OTRA, se activó un enlace al Catálogo de Carreras \n\
                         al final de este formulario. Por favor añade la nueva Carrera al Catálogo.",
                        '',
                        'warning',
                    ).then(() => {                            

                    });   
                    jQuery("#linkToCatCarr").removeClass('d-none');                  
                }else{
                    jQuery("#linkToCatCarr").addClass('d-none');   
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                alert(xhr.responseText);
            }
        });  
	});
     
    //Evento onchange Convenio General Admin
    var convenioGralValue = jQuery('#appbundle_convenio_admin_convenio_general').val();
    jQuery('#appbundle_convenio_admin_convenio_general').change(function(){  
        convenioGralValue = jQuery('#appbundle_convenio_admin_convenio_general').val();
        if(jQuery("#appbundle_convenio_admin_tipo option[value='Específico']").prop("selected", true)){
            //console.log('Entro en ESPECÍFICO / ...');
            //HABILIA EL SELECT CONVENIO
            jQuery('#appbundle_convenio_admin_convenio_general').prop('disabled', false);
        }

        if(typeof(convenioGralValue) !== 'undefined' && convenioGralValue !== ''){ 
            jQuery("#appbundle_convenio_admin_tipo option[value='Específico']").prop("selected", true);       
            //jQuery('#appbundle_convenio_admin_tipo option:not(:selected)').attr('disabled',true);
            jQuery('#sendConvenioAdmin').attr('disabled', false);
            jQuery('#appbundle_convenio_admin_convenio_general').val(convenioGralValue);
            //console.log('Entro en ESPECIFICO/ SELECCIONO LA OPCION DEL SELECT');
        }else{
            jQuery("#appbundle_convenio_admin_tipo option[value='Específico']").prop("selected", false);       
            jQuery('#appbundle_convenio_admin_tipo').prop("disabled", false);
            jQuery('#sendConvenioAdmin').attr('disabled', false);
            //console.log('Entro en ESPECIFICO/ igual a vacio o indefinido');
        }
    });
    
    // Evento onchange de Sector Admin    
    jQuery('#appbundle_convenio_admin_sector').change(function(){
        if(typeof(convenioGralValue) === 'undefined' || convenioGralValue === ''){  
            Swal.fire(
                "El Campo CONVENIO GENERAL es vacío, por lo tanto el tipo de convenio debe ser GENERAL. \n\
                 Si no es así, por favor captura el convenio General o en su defecto, generalo.",
                '',
                'warning',
            ).then(() => {                            

            }); 
            jQuery("#appbundle_convenio_admin_tipo option[value='General']").prop("selected", true);       
            jQuery("#appbundle_convenio_admin_tipo").val("General");
            //jQuery('#appbundle_convenio_admin_tipo option:not(:selected)').attr('disabled',true);
            jQuery('#appbundle_convenio_admin_convenio_general').val(convenioGralValue);
            jQuery('#sendConvenioAdmin').attr('disabled', false);
            //console.log("Entro en General/...undefined");
        }else{
            jQuery("#appbundle_convenio_admin_tipo option[value='Específico']").prop("selected", true);       
            //jQuery('#appbundle_convenio_admin_tipo option:not(:selected)').attr('disabled',true);
            jQuery("#appbundle_convenio_admin_tipo").val("Específico");
            jQuery('#appbundle_convenio_admin_convenio_general').val(convenioGralValue);
            jQuery('#sendConvenioAdmin').attr('disabled', false);
            //console.log("Entro en General/...undefined/ .... else");
        }            
    });
    
    //Evento onchange de Tipo Admin
    jQuery('#appbundle_convenio_admin_tipo').change(function() {
        var convenioType = jQuery("#appbundle_convenio_admin_tipo").val();  
        if(convenioType === 'General'){
            jQuery("#appbundle_convenio_admin_is_camex").addClass('d-none');
            jQuery("#isCamexLabel").addClass('d-none');
            jQuery('#appbundle_convenio_admin_convenio_general').val(convenioGralValue);
            jQuery('#sendConvenioAdmin').attr('disabled', false);
            //console.log("Entro en General/...");
            //DESHABILIA EL SELECT CONVENIO
            jQuery('#appbundle_convenio_admin_convenio_general').prop('disabled', true);
            //QUITAMOS REQUIRED de los 4 campos del form
            jQuery('#appbundle_convenio_admin_cicloAcademico').removeAttr('required');
            jQuery('#appbundle_convenio_admin_nivelAcademico').removeAttr('required');
            jQuery('#appbundle_convenio_admin_disciplina').removeAttr('required');
            jQuery('#appbundle_convenio_admin_carrera').removeAttr('required');

            if (typeof(convenioGralValue) !== 'undefined' && convenioGralValue !== '') {
                //console.log("El valor del select no es vacío");
                //HABILITA EL SELECT
                //jQuery('#appbundle_convenio_admin_convenio_general').prop('disabled', false);
                jQuery('#appbundle_convenio_admin_convenio_general').val('');
                //jQuery('#appbundle_convenio_admin_convenio_general').prop('disabled', true);
            } else {
                //console.log("El valor del select es vacío");
            }
        } else if(jQuery("#appbundle_convenio_admin_tipo option[value='Específico']").prop("selected", true)){
                //console.log('Entro en ESPECÍFICO / ... nu');
                //HABILIA EL SELECT CONVENIO
                jQuery('#appbundle_convenio_admin_convenio_general').prop('disabled', false);
                //AGREGAMOS 4 CAMPOS OBLIGATORIOS
                jQuery('#appbundle_convenio_admin_cicloAcademico').prop('required', true);
                jQuery('#appbundle_convenio_admin_nivelAcademico').prop('required', true);
                jQuery('#appbundle_convenio_admin_disciplina').prop('required', true);
                jQuery('#appbundle_convenio_admin_carrera').prop('required', true);
            }
        else{
            if(typeof(convenioGralValue) === 'undefined' || convenioGralValue === ''){
                //HABILITA EL SELECT CONVENIO
                jQuery('#appbundle_convenio_admin_convenio_general').prop('disabled', false);
                //jQuery('#appbundle_convenio_convenio_general').val(opcion.id);
                 Swal.fire(
                    "Todo convenio de tipo Específico debe tener un CONVENIO GENERAL ASOCIADO. \n\
                        Por favor captura el convenio General o en su defecto, generalo.",
                    '',
                    'warning',
                ).then(() => { });
                //jQuery('#sendConvenioAdmin').attr('disabled', true);
            }
            jQuery("#appbundle_convenio_admin_is_camex").addClass('d-none');
            jQuery("#isCamexLabel").addClass('d-none');           
        }
    });
    
    //Evento on input sobre objetivo de la colaboracion
    $('#appbundle_convenio_admin_objetivo_colaboracion').on('input', function() {
        var maxLength = 255;
        var value = $(this).val();
        if (value.length > maxLength) {
            $(this).val(value.substring(0, maxLength));
            Swal.fire(
                "La descripción del objetivo de la colaboración no debe ser mayor a 255 caracteres, \n\
                como has excedido esa cantidad, tu texto ha sido truncado. POR FAVOR REVISA.",
                '',
                'warning',
            ).then(() => {                            

            });
        }
    });
    
    $('#appbundle_convenio_admin_descripcion_otroNA').on('input', function() {
        var maxLength = 255;
        var value = $(this).val();
        if (value.length > maxLength) {
            $(this).val(value.substring(0, maxLength));
            Swal.fire(
                "La descripción de OTRO Nivel Académico no debe ser mayor a 255 caracteres, \n\
                como has excedido esa cantidad, tu texto ha sido truncado. POR FAVOR REVISA.",
                '',
                'warning',
            ).then(() => {                            

            });
        }
    });
    
    $('#appbundle_convenio_admin_descrip_otraDisciplina').on('input', function() {
        var maxLength = 255;
        var value = $(this).val();
        if (value.length > maxLength) {
            $(this).val(value.substring(0, maxLength));
            Swal.fire(
                "La descripción de OTRA Disciplina no debe ser mayor a 255 caracteres, \n\
                como has excedido esa cantidad, tu texto ha sido truncado. POR FAVOR REVISA.",
                '',
                'warning',
            ).then(() => {                            

            });
        }
    });
    
    //evento de validacion de Motivo de Colaboracion seleccionar General   
    if(urlActual.indexOf("new") !== -1){
        var urlNuevaMotivo = urlActual.replace("conveniosadmin/new", "getmotivogeneral");
        $.ajax({
            url: urlNuevaMotivo,
            type: "POST",
            data: { },
            success: function(response) {
                console.log(JSON.parse(response));
                if(JSON.parse(response).status === "si"){
                    console.log(JSON.parse(response).data);
                    jQuery("#appbundle_convenio_admin_cicloAcademico option[value='"+JSON.parse(response).data+"']").prop("selected", true);                
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                alert(xhr.responseText);
            }
        }); 
    }
    
    //Evento onchange Nivel Academico
    jQuery('#appbundle_convenio_admin_nivelAcademico').change(function() {
        const nivelAcademico = jQuery('#appbundle_convenio_admin_nivelAcademico').val();
        if(!nivelAcademico){
            return;
        }
        var urlNuevaGetNivelAcademico = urlNueva.replace("getconveniogral", "getnivelacademico");
        $.ajax({
            url: urlNuevaGetNivelAcademico,
            type: "POST",
            data: { selectedId: jQuery('#appbundle_convenio_admin_nivelAcademico').val() },
            success: function(response) {  
                console.log(response);
                if(JSON.parse(response).status === "si"){
                    if(JSON.parse(response).data === "true"){
                        Swal.fire(
                        "Has Seleccionado la Opción OTRO, se activó un nuevo campo al formulario. \n\
                         Por favor añade la descripción de OTRO NIVEL ACADÉMICO.",
                        '',
                        'warning',
                        ).then(() => {                            

                        });   
                        jQuery("#descriptionAcademicLevel").removeClass('d-none');     
                        jQuery("#appbundle_convenio_admin_descripcion_otroNA").removeClass('d-none');   
                    }  
                }else{
                    jQuery("#descriptionAcademicLevel").addClass('d-none');     
                    jQuery("#appbundle_convenio_admin_descripcion_otroNA").addClass('d-none');   
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                alert(xhr.responseText);
            }
        });  
	});
        
    //Evento onchange Disciplina
    jQuery('#appbundle_convenio_admin_disciplina').change(function() {
        const disciplina = jQuery('#appbundle_convenio_disciplina').val();
        if(!disciplina){
            return;
        }
        var url = getSchemeAndHttpHost('/admin/conveniosadmin/getdisciplina')
        $.ajax({
            url: url,
            type: "POST",
            data: { selectedId: disciplina },
            success: function(response) {  
                console.log(response);
                if(JSON.parse(response).status === "si"){
                    if(JSON.parse(response).data === "true"){
                        Swal.fire(
                        "Has Seleccionado la Opción OTRA, se activó un nuevo campo al formulario. \n\
                         Por favor añade la descripción de OTRA DISCIPLINA.",
                        '',
                        'warning',
                        ).then(() => {                            

                        });   
                        jQuery("#descripOtraDisciplina").removeClass('d-none');     
                        jQuery("#appbundle_convenio_admin_descrip_otraDisciplina").removeClass('d-none');   
                    }  
                }else{
                    jQuery("#descripOtraDisciplina").addClass('d-none');     
                    jQuery("#appbundle_convenio_admin_descrip_otraDisciplina").addClass('d-none');   
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                alert(xhr.responseText);
            }
        });  
    });

    limitCalendarToTodayAdmin();
    
     //Boton de Limpiar Filtros
    var urlActual = window.location.href;
    if (urlActual.indexOf("search") !== -1) {
        jQuery("#clearFiltersAdmin").removeClass('d-none');       
    }
    
    //Funcion de mensajes
    jQuery("#errorMessageDiv").fadeIn();
    jQuery("#errorMessageDiv").delay(5000).fadeOut();
    setTimeout(function() {
        jQuery(".styleErrorMessage").addClass('d-none');
    }, 10000);
 
    jQuery("#errorMessageDivCoord").fadeIn();
    jQuery("#errorMessageDivCoord").delay(5000).fadeOut();
       
     if (urlActual.indexOf("edit") > -1) {
        jQuery('#appbundle_convenio_admin_numero').removeClass('d-none');
        jQuery('#appbundle_convenio_admin_fuica').removeClass('d-none');
        jQuery('#fuicaLabelId').removeClass('d-none');
        jQuery('#numeroConvenioLabelId').removeClass('d-none');        
    }
    
    //Funciones sobres el submit ADMIN
   //jQuery("#appbundleConvenioAdmin").on("submit", function(event){
    jQuery(document).on('submit','form.appbundle_convenio_admin',function(){
        var convenioType = jQuery("#appbundle_convenio_admin_tipo").val();  
        var convenioGralValue = jQuery('#appbundle_convenio_admin_convenio_general').val();
        if(typeof(convenioGralValue) === 'undefined' || convenioGralValue === ''){                      
            Swal.fire(
               "Todo convenio de tipo Espécifico debe tener un CONVENIO GENERAL ASOCIADO. \n\
                   Por favor captura el convenio General o en su defecto, genéralo.",
               '',
               'warning',
            ).then(() => {                            

            });
            return false;
        }else{
            this.submit();
        }
       
    });
    
    if(document.getElementById("appbundle_convenio_carga_file")){
        document.querySelector('#appbundle_convenio_carga_file').addEventListener('change', function() {
            var fileValue = $('input[name="appbundle_convenio_carga[file]"]').val();
            if (typeof fileValue !== "undefined") {
                $("#appbundle_convenio_carga_save").removeClass("d-none");
            } else {
                $("#appbundle_convenio_carga_save").addClass("d-none");
            }
        });
    }

//Evento calendar
    function limitCalendarToTodayAdmin(){
	const hoy = new Date();
        const picker = datepicker('#appbundle_convenio_admin_fecha_firma', {
            maxDate: hoy,
            // minDate: new Date('YYYY-MM-DD'), 
            formatter: (input, date, instance) => {
                const value = date.toLocaleDateString("dd-mm-yyyy");
                input.value = value;
            }
        });
    }

});


setTimeout(() => {
    document.querySelectorAll('option').forEach(item => {
        if(item.textContent === ''){
            item.style.display = 'none';
        }
    })
}, 1000)


// Aqui termina archivo JavaScript
