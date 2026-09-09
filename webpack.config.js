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
