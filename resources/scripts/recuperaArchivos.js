// Elementos del DOM
const $inputArchivos = document.querySelector("#inputArchivos"),
    $btnEnviar = document.querySelector("#btnEnviar"),
    $estado = document.querySelector("#estado");

    
$btnEnviar.addEventListener("click", async () => {

    const archivosParaSubir = $inputArchivos.files;
    

    if (archivosParaSubir.length <= 0) {
        // Si no hay archivos, no continuamos
        $estado.textContent = "Se requiere cargar al menos 1  archivo por carpeta";
        return true;
    }
    // Preparamos el formdata
    const formData = new FormData();
    // Agregamos cada archivo a "archivos[]". Los corchetes son importantes
    for (const archivo of archivosParaSubir) {
        formData.append("archivos[]", archivo);  
        
    }
    // Los enviamos
    $estado.textContent = "Enviando archivos...";
    const respuestaRaw = await fetch("./Engines/InitProcess.php",{
        method: "POST",
        body: formData,
    }).then(response => response.json())
    .then(response => response);

    //Se limpia input
    $inputArchivos.value = null;

    //Se construye mensaje de resultado
    if(respuestaRaw.endResult === "OK" && typeof respuestaRaw.responseData === "object" && respuestaRaw.responseData.length > 0){
        var message = "";
        respuestaRaw.responseData.forEach(fileResult => {
            message += `'${fileResult.fileName}' ${fileResult.message} \n`;
        });
        $estado.textContent = message;
    } else 
        $estado.textContent = respuestaRaw.message;
    
});