// assets/js/admin/convenioadmin/conveniosFunctions.js

// Aqui empieza tu código JavaScript

import Swal from "sweetalert2";
import {getSchemeAndHttpHost} from "../../utils";

jQuery(document).ready(function(){
    var urlActualCame = window.location.href;
    console.log(urlActualCame);
    let urlNuevaCame = urlActualCame.replace("convenioscame/new", "getconveniogral");
    console.log(urlNuevaCame);

    if (urlActualCame.indexOf("edit") !== -1) {
        urlNuevaCame = urlActualCame.replace("convenioscame/edit", "getconveniogral" );
    }
    var convenioGralValue = jQuery('#appbundle_convenio_convenio_general').val();
    var rfcRegex = /^[A-ZÑ&]{3,4}\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])[A-Z\d]{2}[A\d]$/;    
    //Evento onchange de RFC CAME
    if(document.getElementById("appbundle_convenio_rfc")){
        document.getElementById("appbundle_convenio_rfc").addEventListener("blur", function() {  
            if(typeof jQuery('#appbundle_convenio_rfc').val() !== 'undefined'){
                if(rfcRegex.test(jQuery('#appbundle_convenio_rfc').val())){
                    jQuery("#appbundle_convenio_rfcEnviado").val(jQuery('#appbundle_convenio_rfc').val());
                    $.ajax({
                        url: urlNuevaCame,
                        type: "POST",
                        data: { rfc: jQuery('#appbundle_convenio_rfc').val() },
                        showLoader: true,
                        success: function(response) {
                            console.log(response);
                            jQuery('#appbundle_convenio_convenio_general').empty().append();    
                            jQuery('#appbundle_convenio_razon_social').val("");
                            jQuery('#appbundle_convenio_institucion').empty().append();    

                            if (!jQuery.isEmptyObject(response) && JSON.parse(response).status === "si") {
                                $.each(JSON.parse(response).data, function(index, opcion) {
                                    jQuery('#appbundle_convenio_convenio_general').append(jQuery('<option>', {
                                        value: opcion.id,
                                        text: opcion.nombre
                                    }));	
                                });  
                                $.each(JSON.parse(response).instituciones, function(index1, opcion1) {
                                    jQuery('#appbundle_convenio_institucion').append(jQuery('<option>', {
                                        value: opcion1.id,
                                        text: opcion1.nombre
                                    }));	
                                });  
                               if(JSON.parse(response).razonSocial != "ambigua"){
                                jQuery('#appbundle_convenio_razon_social').val(JSON.parse(response).razonSocial);
                               }else{
                                    Swal.fire(
                                        "La razón social de esta institucion, es ambigua, por favor revisar los registros.",
                                        '',
                                        'warning',
                                    ).then(() => {                            

                                    });   
                                    jQuery('#appbundle_convenio_convenio_general').empty().append('<option disabled selected >Selecciona Convenio General</option>');    
                                    jQuery('#appbundle_convenio_razon_social').val("");
                                    jQuery('#appbundle_convenio_institucion').empty().append('<option disabled selected hidden>Selecciona Institucón</option>');    
                               }                      
                            } else {
                                console.log("El response está vacío: "+response);
                                Swal.fire(
                                    "El R.F.C. capturado, no obtuvo registros de Convenios Generales. Debes crear el Convenio General Correspondiente.",
                                    '',
                                    'warning',
                                ).then(() => {                            

                                });   
                                jQuery('#appbundle_convenio_convenio_general').empty().append('<option disabled selected hidden>Selecciona Convenio General</option>');    
                                if(jQuery.isEmptyObject(JSON.parse(response).instituciones)){
                                    jQuery('#appbundle_convenio_razon_social').val("");
                                    jQuery('#appbundle_convenio_institucion').empty().append('<option disabled selected hidden>Selecciona Institucón</option>');
                                }else{
                                    jQuery('#appbundle_convenio_razon_social').val(JSON.parse(response).razonSocial);
                                    $.each(JSON.parse(response).instituciones, function(index1, opcion1) {
                                        jQuery('#appbundle_convenio_institucion').append(jQuery('<option>', {
                                            value: opcion1.id,
                                            text: opcion1.nombre
                                        }));	
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
                    Swal.fire(
                        "El formato del R.F.C. no es correcto, por favor captura un valor de R.F.C. De la forma correcta.",
                        '',
                        'warning',
                    ).then(() => {                            

                    });   
                    jQuery('#appbundle_convenio_convenio_general').empty().append('<option disabled selected hidden>Selecciona Convenio General</option>'); 
                    jQuery('#appbundle_convenio_razon_social').val("");
                    jQuery('#appbundle_convenio_institucion').empty().append('<option disabled selected hidden>Selecciona Institución</option>');    
                }
            }else{            
                console.log("El valor del campo rfc esta vacio");
                Swal.fire(
                    "El R.F.C. no debe ser vacío, por favor captura un valor en el campo R.F.C.",
                    '',
                    'warning',
                ).then(() => {                                         
                });   
                jQuery('#appbundle_convenio_convenio_general').empty().append('<option disabled selected hidden>Selecciona Convenio General</option>'); 
                jQuery('#appbundle_convenio_razon_social').val("");
                jQuery('#appbundle_convenio_institucion').empty().append('<option disabled selected hidden>Selecciona Institución</option>');    
            }        
        });
    }
    
    //Evento On Change de Carrera CAME
    jQuery('#appbundle_convenio_carrera').change(function() {  
            var urlNuevaGetCarreraCame = urlNuevaCame.replace("getconveniogral", "getcarrera");
            var selected = jQuery('#appbundle_convenio_carrera').val();
            $.ajax({
                url: urlNuevaGetCarreraCame,
                type: "POST",
                data: { selectedId: selected },
                success: function(response) {       
                    if(JSON.parse(response).status === "si"){
                        jQuery("#appbundle_convenio_otraCarreraL").removeClass('d-none');
                        jQuery("#appbundle_convenio_otraCarrera").removeClass('d-none');                    
                    }else{
                        jQuery("#appbundle_convenio_otraCarreraL").addClass('d-none');
                        jQuery("#appbundle_convenio_otraCarrera").addClass('d-none');         
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    alert(xhr.responseText);
                }
            });  
	});
        
    //Para CAME la OOAD es obligatoria que se asigne la del usuario CAME
    var getCameOoadUrl =  urlNuevaCame.replace("getconveniogral", "getuserooad");
    $.ajax({
                url: getCameOoadUrl,
                type: "POST",
                data: {"":""},
                success: function(response) {  
                    console.log(response);
                    if(JSON.parse(response).status === "si"){  
                        $.each(JSON.parse(response).ooads, function(index1, opcion1) {
                            jQuery("#appbundle_convenio_delegacion option[value='"+opcion1.id+"']").prop("selected", true);	
                        });                                 
                        jQuery('#appbundle_convenio_delegacion option:not(:selected)').attr('disabled',true);  
                    }else{
                        jQuery('appbundle_convenio_delegacion').empty().append('<option disabled selected hidden></option>'); 
                        Swal.fire(
                            "El usuario actual, no está asignado a una OOAD, por favor revisalo e intenta de nuevo más tarde.",
                            '',
                            'warning',
                        ).then(() => {                            

                        }); 
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    alert(xhr.responseText);
                }
            });
        
    //Evento onchange de Convenio General CAME
    jQuery('#appbundle_convenio_convenio_general').change(function(){  
        convenioGralValue = jQuery('#appbundle_convenio_convenio_general').val(); 
        if(typeof(convenioGralValue) != 'undefined' && convenioGralValue != ''){                                          
            jQuery('#sendConvenioCame').attr('disabled', false);
            jQuery('#appbundle_convenio_convenio_general').val(convenioGralValue);
        }else{            
            jQuery('#sendConvenioCame').attr('disabled', true);
        }
    });
    
    // Evento onchange de Sector CAME
    jQuery('#appbundle_convenio_sector').change(function(){
            const convenioGralValue = jQuery('#appbundle_convenio_convenio_general').val();
            console.log('convenioGralValue', convenioGralValue);
            if(typeof(convenioGralValue) === 'undefined' || convenioGralValue === ''){  
                Swal.fire(
                    "Estás capturando un convenio de tipo CAME, por lo cual, el campo Convenio General \n\
                    es de carácter obligatorio. \n\
                    Si no es así, por favor comunicate con el Administrador de Convenios y solicita que \n\
                    genere el Convenio General, para este R.F.C. o Institución Educativa.",
                    '',
                    'warning',
                ).then(() => {                            

                });                 
                jQuery('#appbundle_convenio_convenio_general').val(convenioGralValue);
                jQuery('#sendConvenioCame').attr('disabled', true);
            }else{                
                jQuery('#appbundle_convenio_convenio_general').val(convenioGralValue);
                jQuery('#sendConvenioCame').attr('disabled', false);
            }            
        }); 
        
    //Evento onchange Nivel Academico
    jQuery('#appbundle_convenio_nivelAcademico').change(function() {
        const nivelAcademico = jQuery('#appbundle_convenio_nivelAcademico').val();
        if(!nivelAcademico){
            return;
        }
        var urlNuevaGetNivelAcademico = urlNuevaCame.replace("getconveniogral", "getnivelacademico");
        $.ajax({
            url: urlNuevaGetNivelAcademico,
            type: "POST",
            data: { selectedId: jQuery('#appbundle_convenio_nivelAcademico').val() },
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
                        jQuery("#appbundle_convenio_descripcion_otroNA").removeClass('d-none');   
                    }  
                }else{
                    jQuery("#descriptionAcademicLevel").addClass('d-none');     
                    jQuery("#appbundle_convenio_descripcion_otroNA").addClass('d-none');   
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                alert(xhr.responseText);
            }
        });  
	});
        
    //Evento onchange Nivel Academico
    jQuery('#appbundle_convenio_disciplina').change(function() {
        const disciplina = jQuery('#appbundle_convenio_disciplina').val();
        if(!disciplina){
            return;
        }
        var url = getSchemeAndHttpHost('/admin/convenioscame/getdisciplina')
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
                        jQuery("#appbundle_convenio_descrip_otraDisciplina").removeClass('d-none');   
                    }  
                }else{
                    jQuery("#descripOtraDisciplina").addClass('d-none');     
                    jQuery("#appbundle_convenio_descrip_otraDisciplina").addClass('d-none');   
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                alert(xhr.responseText);
            }
        });  
    });
    
    //Evento on input sobre objetivo de la colaboracion
    $('#appbundle_convenio_objetivo_colaboracion').on('input', function() {
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

    hideOptionByLabel();
    limitCalendarToTodayCame();
	
    //Boton de Limpiar Filtros
    var urlActual = window.location.href;
    if (urlActual.indexOf("search") !== -1) {
        jQuery("#clearFiltersCamex").removeClass('d-none');
    }    
    
    //Funcion de mensajes
    jQuery("#errorMessageDiv").fadeIn();
    jQuery("#errorMessageDiv").delay(5000).fadeOut();
    setTimeout(function() {
        jQuery(".styleErrorMessage").addClass('d-none');
    }, 10000);
    
    jQuery("#errorMessageDivCame").fadeIn();
    jQuery("#errorMessageDivCame").delay(5000).fadeOut();
    
    jQuery("#errorMessageDivCoord").fadeIn();
    jQuery("#errorMessageDivCoord").delay(5000).fadeOut();
    
     if (urlActual.indexOf("edit") > -1) {
        //convenios came
        jQuery('#appbundle_convenio_numero').removeClass('d-none');
        jQuery('#appbundle_convenio_fuica').removeClass('d-none');
        jQuery('#fuicaLabelIdCame').removeClass('d-none');
        jQuery('#numeroConvenioLabelIdCame').removeClass('d-none');
    }
    
    jQuery(document).on('submit','form.appbundle_convenio',function(){
        var convenioType = jQuery("#appbundle_convenio_tipo").val();  
        var convenioGralValue = jQuery('#appbundle_convenio_convenio_general').val(); 
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

    function hideOptionByLabel(selectId, labelToHide) {
       var select = document.getElementById("appbundle_convenio_cicloAcademico");
        var options = select.options;
        for (var i = 0; i < options.length; i++) {
            if (options[i].text === "General") {
        	options[i].style.display = "none";
                break;
            }
        };
    };

    //Evento calendar
    function limitCalendarToTodayCame(){
	const hoy = new Date();
  /*
        const picker = datepicker('#appbundle_convenio_fecha_firma', {
            maxDate: hoy, // Restringe las fechas futuras
            // minDate: new Date('YYYY-MM-DD'), 
            formatter: (input, date, instance) => {
                const value = date.toLocaleDateString("dd-mm-yyyy");
                input.value = value;
            }
        });*/
    }
	
    
});

setTimeout(() => {
    document.querySelectorAll('option').forEach(item => {
        if(item.textContent === ''){
            item.style.display = 'none';
        }
    })
    const date = new Date();
    document.querySelector('#appbundle_convenio_fecha_firma').max = `${date.getFullYear()}-${(date.getMonth()+1).toString().padStart(2, '0')}-${date.getDate()+1}`;
}, 1000)
