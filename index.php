<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Azure Blob Storage Integration</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f8f9fa; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .upload-form { background: #e7f3ff; padding: 25px; border-radius: 8px; margin: 25px 0; border-left: 4px solid #0078d4; }
        .file-list { background: #f0f8ff; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .security-info { background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107; }
        button { background: #0078d4; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background: #106ebe; }
        input[type="file"] { padding: 10px; margin: 10px 0; }
        .file-item { padding: 8px; margin: 5px 0; background: white; border-radius: 4px; }
        .success { color: #28a745; padding: 10px; background: #d4edda; border-radius: 4px; }
        .error { color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔗 Integración con Azure Blob Storage</h1>
        <p>Esta aplicación demuestra la integración con Azure Blob Storage usando identidad de servicio con permisos mínimos</p>
        
        <div class="upload-form">
            <h3>📤 Subir Archivo al Blob Storage</h3>
            <form method="post" enctype="multipart/form-data">
                <input type="file" name="archivo" required>
                <button type="submit" name="subir">Subir Archivo</button>
            </form>
        </div>

        <div class="file-list">
            <h3>📁 Archivos en el Contenedor "archivos-app"</h3>
            <?php
            require_once 'vendor/autoload.php';
            
            use MicrosoftAzure\Storage\Blob\BlobRestProxy;
            use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
            use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;

            // Configuración
            $accountName = "stblobpractica123";
            $containerName = "archivos-app";
            
            try {
                // Usar Managed Identity para autenticación
                $connectionString = "DefaultEndpointsProtocol=https;AccountName=$accountName;";
                $blobClient = BlobRestProxy::createBlobService($connectionString);

                // Procesar subida de archivo
                if (isset($_POST['subir']) && isset($_FILES['archivo'])) {
                    $archivo = $_FILES['archivo'];
                    $blobName = $archivo['name'];
                    $content = fopen($archivo['tmp_name'], "r");
                    
                    try {
                        $blobClient->createBlockBlob($containerName, $blobName, $content);
                        echo "<div class='success'>✅ Archivo '$blobName' subido correctamente al Blob Storage</div>";
                    } catch (ServiceException $e) {
                        $errorCode = $e->getCode();
                        echo "<div class='error'>❌ Error al subir archivo: " . $e->getMessage() . " (Código: $errorCode)</div>";
                    }
                }

                // Listar archivos en el contenedor
                $listOptions = new ListBlobsOptions();
                $blobList = $blobClient->listBlobs($containerName, $listOptions);
                $blobs = $blobList->getBlobs();

                if (count($blobs) > 0) {
                    echo "<div style='margin-top: 15px;'>";
                    foreach ($blobs as $blob) {
                        echo "<div class='file-item'>";
                        echo "📄 <strong>" . $blob->getName() . "</strong><br>";
                        echo "<small>Tamaño: " . number_format($blob->getProperties()->getContentLength() / 1024, 2) . " KB</small>";
                        echo "</div>";
                    }
                    echo "</div>";
                } else {
                    echo "<p>No hay archivos en el contenedor. Sube el primero usando el formulario arriba.</p>";
                }

            } catch (Exception $e) {
                echo "<div class='error'>⚠️ Error de conexión: " . $e->getMessage() . "</div>";
                echo "<p><small>Asegúrate de que la identidad administrada tiene los permisos correctos.</small></p>";
            }
            ?>
        </div>

        <div class="security-info">
            <h4>🔍 Validación de Seguridad - Principio de Mínimos Privilegios</h4>
            <p><strong>Rol asignado:</strong> Colaborador de datos de Storage Blob</p>
            <p><strong>Permisos concedidos:</strong> ✅ Lectura, escritura y eliminación de blobs</p>
            <p><strong>Permisos denegados:</strong> ❌ Administrar cuenta de storage, ❌ Configurar redes, ❌ Ver claves de acceso</p>
        </div>
    </div>
</body>
</html> 
