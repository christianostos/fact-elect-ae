<?php
/**
 * Autoloader personalizado para mPDF sin Composer
 */

// Definir función de autoload para mPDF
spl_autoload_register(function ($class) {
    // Solo procesar clases de mPDF
    if (strpos($class, 'Mpdf\\') === 0) {
        // Convertir el nombre de la clase a una ruta de archivo
        $file = __DIR__ . '/mpdf/mpdf/src/' . str_replace('\\', '/', substr($class, 5)) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    return false;
});

// Cargar constantes y funciones de mPDF
$mpdf_constants_file = __DIR__ . '/mpdf/mpdf/src/Config/ConfigVariables.php';
if (file_exists($mpdf_constants_file)) {
    require_once $mpdf_constants_file;
}

$mpdf_functions_file = __DIR__ . '/mpdf/mpdf/src/functions.php';
if (file_exists($mpdf_functions_file)) {
    require_once $mpdf_functions_file;
}