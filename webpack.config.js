const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addStyleEntry('layout', './assets/css/layout.scss')

    .addEntry('login', './assets/js/login/index.js')

    // IE
    .addEntry('ie.perfil', './assets/js/ie/Perfil/index.js')
    .addEntry('ie.registrar.solicitud', './assets/js/ie/RegistrarSolicitud/index.js')
    .addEntry('ie.detalle.solicitud', './assets/js/ie/DetalleSolicitud/index.js')
    .addEntry('ie.registrar.montos', './assets/js/ie/RegistrarMontos/index.js')
    .addEntry('ie.inicio', './assets/js/ie/Inicio/index.js')
    .addEntry('ie.detalle.forma.pago', './assets/js/ie/DetalleFormaPago/index.js')
    .addEntry('ie.detalle.solicitud.multiple', './assets/js/ie/DetalleSolicitudMultiple/index.js')
    .addEntry('ie.cargar.comprobante', './assets/js/ie/RegistrarPago/index.js')
    .addEntry('ie.seleccionar.forma.pago', './assets/js/ie/SeleccionarFormaPago/index.js')
    .addEntry('ie.correccion.pago.fofoe', './assets/js/ie/EditarPagoFofoe/index.js')
    .addEntry('ie.carga.comprobante.pago', './assets/js/ie/CargaDeComprobanteDePago/index.js')

    // CAME
    .addEntry('came_solicitud_index', './assets/js/came/solicitud/index.js')
    .addEntry('came_solicitud_validar', './assets/js/came/solicitud/ValidarSolicitud/index.js')
//    .addEntry('pregrado_reporte', './assets/js/pregrado/reporte/index.js')
//    .addEntry('enfermeria_reporte-ciclos', './assets/js/enfermeria/reporte_ciclos/index.js')

    // FOFOE
    .addEntry('fofoe.inicio', './assets/js/fofoe/Inicio/index.js')
    .addEntry('fofoe_referencia', './assets/js/fofoe/referencia/index.js')
    .addEntry('fofoe_detalle_referencia', './assets/js/fofoe/DetalleReferencia/index.js')
    .addEntry('fofoe.registrar.factura', './assets/js/fofoe/RegistrarFactura/index.js')
    .addEntry('fofoe_reportes-inicio', './assets/js/fofoe/reportes-inicio/index.js')
    .addEntry('fofoe_reporte-oportunidad', './assets/js/fofoe/reporte_oportunidad/index.js')
    .addEntry('fofoe_reporte-ingresos', './assets/js/fofoe/reporte_ingresos/index.js')
    .addEntry('validacion.de.pago', './assets/js/fofoe/ValidacionDePago/index.js')
    .addEntry('ie.detalle.ie', './assets/js/fofoe/DetalleIE/index.js')
    .addEntry('fofoe.ciclo-educativo.index', './assets/js/fofoe/CicloEducativo/IndexPage.js')
    .addEntry('fofoe.ciclo-educativo.show', './assets/js/fofoe/CicloEducativo/ShowPage.js')

    // ── Enfermería (escuela) ──────────────────────────────────────────────────
    .addEntry('enfermeria.index',  './assets/js/enfermeria/pages/index.js')
    .addEntry('enfermeria.show',   './assets/js/enfermeria/pages/show.js')
    .addEntry('enfermeria.create', './assets/js/enfermeria/pages/create.js')

    // ── Enfermería alumno ────────────────────────────────────────────────────
    .addStyleEntry('enfermeria.base',               './assets/css/enfermeria/create.scss')
    .addEntry('enfermeria-alumno.login',            './assets/js/enfermeria-alumno/pages/login.js')
    .addEntry('enfermeria-alumno.index',            './assets/js/enfermeria-alumno/pages/index.js')
    .addEntry('enfermeria-alumno.cargaComprobante', './assets/js/enfermeria-alumno/pages/cargaComprobante.js')
    .addStyleEntry('enfermeria-alumno',             './assets/js/enfermeria-alumno/styles/enfermeria-alumno.css')

    // ── FOFOE / Enfermería ───────────────────────────────────────────────────
    .addEntry('fofoe.enfermeria.index', './assets/js/enfermeria/pages/fofoe/index.js')
    .addEntry('fofoe.enfermeria.show',  './assets/js/enfermeria/pages/fofoe/show.js')
    .addStyleEntry('layout-formato-fofoe', './assets/css/formatos/fofoe/layout.scss')

    // ── Estilos compartidos ──────────────────────────────────────────────────
    .addStyleEntry('loader',        './assets/js/components/Loader/styles.scss')
    .addStyleEntry('popup',         './assets/js/components/Popup/Popup.css')
    .addStyleEntry('input-file',    './assets/js/components/InputFile/InputFile.css')
    .addStyleEntry('progress-bar',  './assets/js/components/ProgressBar/ProgressBar.css')
    .addStyleEntry('enfermeria.create.styles', './assets/css/enfermeria/create.scss')

    // POSGRADO
    .addEntry('posgrado.residentes-imss.index', './assets/js/posgrado/ResidentesExIMSS/ListaResidentes/index.js')
    .addEntry('posgrado.residentes-imss.detalle', './assets/js/posgrado/ResidentesExIMSS/DetalleResidente/index.js')
    .addEntry('posgrado.residentes-noimss.index', './assets/js/posgrado/ResidentesExNoIMSS/ListaResidentes/index.js')
    .addEntry('posgrado.residentes-noimss.detalle', './assets/js/posgrado/ResidentesExNoIMSS/DetalleResidente/index.js')
    .addEntry('posgrado.residentes-imss.create', './assets/js/posgrado/ResidentesExIMSS/NuevoResidente/create.js')
    .addEntry('posgrado.residentes-noimss.create', './assets/js/posgrado/ResidentesExNoIMSS/NuevoResidente/create.js')
    .addEntry('residente.perfil', './assets/js/residente/Inicio/index.js')
    .addEntry('residente.carga-comprobante-pago', './assets/js/residente/CargaComprobantePago/index.js')

    //CONVENIOS
    .addEntry('admin.buttonsmenu','./assets/js/admin/buttonsmenu.js')
    .addEntry('admin.buttonscamexmenu','./assets/js/admin/buttonscamexmenu.js')
    .addEntry('admin.buttonscoordinaciones','./assets/js/admin/buttonscoordinaciones.js')
    .addEntry('admin.conveniosFunctions','./assets/js/admin/convenioadmin/conveniosFunctions.js')
    .addEntry('admin.conveniosOnLoadFunctions','./assets/js/admin/convenioadmin/conveniosOnLoadFunctions.js')
    .addEntry('admin.conveniosCameFunctions','./assets/js/admin/convenioadmin/conveniosCameFunctions.js')
    .addEntry('convenios.jquery','./node_modules/jquery/src/jquery.js')
    .addStyleEntry('only-convenios-styling', './assets/css/convenioadmin.css')
    .addEntry('admin.buttonsdisciplinas','./assets/js/admin/buttonsdisciplinas.js')

    .addEntry('admin.convenio.prueba','./assets/js/admin/convenioadmin/prueba.js')
    .addEntry('admin.convenio.create','./assets/js/admin/convenioadmin/CreatePage.js')
    .addEntry('admin.convenio.edit','./assets/js/admin/convenioadmin/EditPage.js')
    .addEntry('admin.convenio.index','./assets/js/admin/convenioadmin/IndexPage.js')

    .addEntry('admin.convenio-especial.create','./assets/js/admin/convenio-especial/CreatePage.js')
    .addEntry('admin.convenio-especial.edit','./assets/js/admin/convenio-especial/EditPage.js')
    .addEntry('admin.convenio-especial.index','./assets/js/admin/convenio-especial/IndexPage.js')

    .addEntry('admin.came.convenio.create','./assets/js/admin/conveniocame/CreatePage.js')
    .addEntry('admin.came.convenio.edit','./assets/js/admin/conveniocame/EditPage.js')
    .addEntry('admin.came.convenio.index','./assets/js/admin/conveniocame/IndexPage.js')

    .addEntry('admin.institucion.index','./assets/js/admin/institucion/IndexPage.js')
    .addEntry('admin.institucion.create','./assets/js/admin/institucion/CreatePage.js')
    .addEntry('admin.institucion.edit','./assets/js/admin/institucion/EditPage.js')

    .addEntry('admin.came.institucion.index','./assets/js/admin/conveniocame/institucion/IndexPage.js')

    // ── Imágenes ─────────────────────────────────────────────────────────────
    .copyFiles({ from: './assets/images', to: 'images/[path][name].[ext]' })

    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .enableReactPreset()
    .enableSassLoader()
;

module.exports = Encore.getWebpackConfig();
