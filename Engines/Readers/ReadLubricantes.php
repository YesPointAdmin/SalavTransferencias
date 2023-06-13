<?php

namespace App\Engines\Readers;
use App\Engines\Singleton\BitacoraSingleton;
use App\Engines\Singleton\ProductosSingleton;
use mysqli;

use App\Capture\InscribeCatalogoLubricantes;
use App\Capture\InscribeCatalogoOpcion;
use App\Capture\InscribeCatalogoGrasaBaleros;
use App\Capture\InscribeCatalogoGrasaJuntas;
use App\Capture\InscribeCatalogoGrasaChasi;
use App\Capture\InscribeCatalogoAditivoGasolina;
use App\Capture\InscribeCatalogoLubricantesRoshfrans;
use App\Capture\InscribeCatalogoAditivoSI;
use App\Capture\InscribeCatalogoFluidoFrenos;
use App\Capture\InscribeCatalogoRefrigerante;
use App\Capture\InscribeCatalogoMasterLubricantes;
use Exception;

class ReadLubricantes extends ReaderImplement{
    
    protected string $bitacoraBasePath = "../logs/BD_LUBRICANTES/bitacoralubricantes";
    protected string $bitacoraPath = "../logs/BD_LUBRICANTES/bitacoralubricantes";
    protected array $processActualSequence = [1 => "marca", 2 => "modelo", 3 => "year", 4 => "motor", 5 => "viscosidad", 6 => "servicio", 7 => "homologacion"];
    protected array $lubricantesSequence = [8=>"0_60k",9=>"0_60k",10=>"61k_100k",11=>"61k_100k",12=>"101k_150k",13=>"101k_150k",14=>"151k_200k",15=>"151k_200k",16=>"200k_o_mas",17=>"200k_o_mas"];
    protected array $frenosInyeccionAndRefrigeranteSequence = [28=>"fluido_de_frenos",30=>"fluido_de_frenos",31=>"0_200k_refrigerante",32=>"0_200k_refrigerante",33=>"200k_o_mas_refrigerante",34=>"200k_o_mas_refrigerante",36=>"aditivo_sistema_inyeccion",37=>"aditivo_sistema_inyeccion"];
    protected array $gasolinaAndGrasasSequence = [38 => "aditivo_gasolina", 39 => "grasa_chasi", 40 => "grasa_juntas", 41 => "grasa_baleros"];
    protected array $processTransformation = [1, 2, 4, 5];
    protected array $processRequired = [1, 2, 5];
    protected array $processTrim = [];
    protected InscribeCatalogoLubricantes $inscribeLubricantes;
    protected InscribeCatalogoOpcion $inscribeOpcion;
    //Se agrega inscribe nuevo
    protected InscribeCatalogoGrasaBaleros $inscribeGrasaBaleros;
    protected int $countOk = 0;
    protected int $countNotExists = 0;
    protected int $countRepeats = 0;
    protected int $countErrors = 0;
    protected InscribeCatalogoGrasaJuntas $inscribeGrasaJuntas;
    protected InscribeCatalogoGrasaChasi $inscribeGrasaChasi;
    protected InscribeCatalogoAditivoGasolina $inscribeAditivoGasolina;
    protected InscribeCatalogoLubricantesRoshfrans $inscribeLubricantesRoshfrans;
    protected InscribeCatalogoRefrigerante $inscribeRefrigerante;
    protected InscribeCatalogoFluidoFrenos $inscribeFluidoFrenos;
    protected InscribeCatalogoAditivoSI $inscribeAditivoSI;
    protected InscribeCatalogoMasterLubricantes $inscribeMasterLubs;
    
    public function readData(string $fileName, mysqli $link, array $dataToProcess, array $highestRow): void
    {

        $this->outMessage("Inicia la captura de datos desde archivo Lubricantes. Registro de logs independiente... ");

        //$this->bitacoraResgistartion = new InscribeBitacora($link, "transferencia");
        BitacoraSingleton::getInstance($link)->addRowToBitacora($fileName, 'Se detecto el siguente provedor: Lubricantes', '', '', '', '0', '0');
        $this->inscribeLubricantes = new InscribeCatalogoLubricantes($link, PROCESS_NAME);
        $this->inscribeOpcion = new InscribeCatalogoOpcion($link, PROCESS_NAME);
        //Se agrega inscribe nuevo
        $this->inscribeGrasaBaleros = new InscribeCatalogoGrasaBaleros($link, PROCESS_NAME);
        $this->inscribeGrasaJuntas = new InscribeCatalogoGrasaJuntas($link, PROCESS_NAME);
        $this->inscribeGrasaChasi = new InscribeCatalogoGrasaChasi($link, PROCESS_NAME);
        $this->inscribeAditivoGasolina = new InscribeCatalogoAditivoGasolina($link, PROCESS_NAME);
        $this->inscribeLubricantesRoshfrans = new InscribeCatalogoLubricantesRoshfrans($link, PROCESS_NAME);
        $this->inscribeRefrigerante = new InscribeCatalogoRefrigerante($link, PROCESS_NAME);
        $this->inscribeFluidoFrenos = new InscribeCatalogoFluidoFrenos($link, PROCESS_NAME);
        $this->inscribeAditivoSI = new InscribeCatalogoAditivoSI($link, PROCESS_NAME);
        $this->inscribeMasterLubs = new InscribeCatalogoMasterLubricantes($link, PROCESS_NAME);

        $this->writeBitacora("--------------------------------------", $fileName);
        $this->writeBitacora("Se inicia proceso para Lurbicantes...", $fileName);
        $this->writeBitacora("--------------------------------------", $fileName);

        foreach ($dataToProcess as $rowKey => $rowValue) {
            # code...
            $readMoment = \time();
            if ((!is_array($rowValue) || gettype($rowValue) !== 'array')) {
                $typeOfRow = gettype($rowValue);
                $this->writeBitacora("time:{$readMoment}|row:{$rowKey}|status:'ERROR'|conflict:'No se puede procesar la informacion en fila'|row_type:{$typeOfRow}", $fileName);
            } else if($rowKey > 2) {
                try {
                    //code...
                    $dataStructure = $this->retrieveDataStructure($fileName, $rowKey, $rowValue) or throw new Exception("Error at retrieving data for row {$rowKey}",1);
                    $this->writeBitacora("Se procesa lubricante fila: {$rowKey}. Data: ".var_export($dataStructure,true), $fileName);
                    if($newRow = $this->resolveCaptureAndValidations($link, $dataStructure, $fileName))
                        $this->countOk += 1;
                    else throw new Exception("Error at processs row {$rowKey}",1);
                } catch (Exception $e) {
                    //throw $th;
                    $this->writeBitacora("Excepton =>".$e->getMessage(), $fileName);
                    $this->countErrors += 1;
                }

            }
        }
        BitacoraSingleton::getInstance($link)->addRowToBitacora($fileName, 'Termina el proceso de lectura', '', $this->countNotExists, $this->countRepeats, $this->countErrors, $this->countOk);

    }

    protected function resolveCaptureAndValidations(mysqli $link, array $dataStructure, string $fileName){

        $this->writeBitacora("Se buscara en catalogo lubs: ", $fileName);
        
        $getLubIfExists = $this->inscribeLubricantes->executeQuery('select', $fileName, $dataStructure["marca"],$dataStructure["modelo"],$dataStructure["anio_inicio"],$dataStructure["anio_fin"],$dataStructure["motor"],$dataStructure["viscosidad"],$dataStructure["servicio"],$dataStructure["homologacion"]);


        if($getLubIfExists !== 0){
            $this->writeBitacora("Existe en catalogo  lubricantes cond ID: {$getLubIfExists[0]['id']} por tanto no se registra", $fileName);
            $this->countRepeats += 1;
            return;
        }
        $this->countNotExists += 1;

        $this->writeBitacora("Se registrara en catalogo  lubricantes: ", $fileName);
        $insertedLubID = $this->inscribeLubricantes->executeQuery('insert', $fileName, $dataStructure["marca"],$dataStructure["modelo"],$dataStructure["anio_inicio"],$dataStructure["anio_fin"],$dataStructure["motor"],$dataStructure["viscosidad"],$dataStructure["servicio"],$dataStructure["homologacion"],'1','1') or throw new Exception("Erro at write into catalogo lubs",1);
        $this->writeBitacora("Se ha insertado en catalgo lubricantes con ID: ".var_export($insertedLubID,true), $fileName);

        $this->processToLubs( $dataStructure, $fileName) or throw new Exception("Error at process Lubs",1);

        return $this->processToVarios( $dataStructure, $fileName)or throw new Exception("Erro at process varioss",1);

    }

    protected function processToLubs(array $dataStructure, string $fileName) : void {
        $lubIDSOption = [];
        for($i = 8; $i < array_key_last($this->lubricantesSequence); $i++){
            $this->writeBitacora("Se registrara en catalogo opcion lubricante: ".$this->lubricantesSequence[$i], $fileName);
            $insertOpcionLubricantesID = $this->inscribeOpcion->executeQuery('insert', $fileName,  $dataStructure[$this->lubricantesSequence[$i]][0],$dataStructure[$this->lubricantesSequence[$i]][1]);
            $this->writeBitacora("Se ha insertado en opcion para lubricantes con ID: ".var_export($insertOpcionLubricantesID,true), $fileName);
            $lubIDSOption[$this->lubricantesSequence[$i]] = $insertOpcionLubricantesID;
            $i += 1;
        }
        $this->writeBitacora("Se registrara en catalogo lubricantes roshfrans: ", $fileName);
        $insertedLubRoshfransID = $this->inscribeLubricantesRoshfrans->executeQuery('insert', $fileName, $lubIDSOption["0_60k"]??0,$lubIDSOption["61k_100k"]??0,$lubIDSOption["101k_150k"]??0,$lubIDSOption["151k_200k"]??0,$lubIDSOption["200k_o_mas"]??0) or throw new Exception("Error at write into lubs roshfrans",1);
        $this->writeBitacora("Se ha insertado en catalgo lubs roshfrans con ID:  ".var_export($insertedLubRoshfransID,true), $fileName);
    }

    protected function processToVarios(array $dataStructure, string $fileName) : int | bool {

        $insertedMasterLubsID = false;

        $variosIDsCatalogs = $this->processToRefrigeranteFrenosAndAditivo( $dataStructure, $fileName) or throw new Exception("Error at process Refrigerante, Inyeccion y Frenos",1);
        
        $variosIDsCatalogs = $this->processToRefrigeranteFrenosAndAditivo($dataStructure, $fileName, $variosIDsCatalogs) or throw new Exception("Error at process Grasas y Gasolina",1);

        $this->writeBitacora("Se registrara en catalogo master lubs: ", $fileName);
        $insertedMasterLubsID = $this->inscribeMasterLubs->executeQuery('insert', $fileName, $insertedLubID ?? 0,$insertedLubRoshfransID ?? 0, $variosIDsCatalogs["fluido_de_frenos"] ?? 0, $variosIDsCatalogs["refrigerante"] ?? 0, $variosIDsCatalogs["aditivo_sistema_inyeccion"] ?? 0, $variosIDsCatalogs["aditivo_gasolina"] ?? 0, $variosIDsCatalogs["grasa_chasi"] ?? 0,  $variosIDsCatalogs["grasa_juntas"] ?? 0,  $variosIDsCatalogs["grasa_baleros"] ?? 0) or throw new Exception("Error at write into Master Lubs",1);;
        $this->writeBitacora("Se ha insertado en catalgo master Lubs con ID:  ".var_export($insertedMasterLubsID,true), $fileName);

        return (is_integer($insertedMasterLubsID)||is_bool($insertedMasterLubsID))?$insertedMasterLubsID:false;
    }

    protected function processToRefrigeranteFrenosAndAditivo(array $dataStructure, string $fileName) : mixed {
        $variosIDsCatalogs = [];
        $refrigeranteOpcionIDs = [];
        for($i = 28; $i < array_key_last($this->frenosInyeccionAndRefrigeranteSequence); $i++){
            $this->writeBitacora("Se registrara en catalogo opcion varios {$this->frenosInyeccionAndRefrigeranteSequence[$i]}", $fileName);
  
            switch ($this->frenosInyeccionAndRefrigeranteSequence[$i]) {
                case 'fluido_de_frenos':
                    $insertOpcionVariosID = $this->inscribeOpcion->executeQuery('insert', $fileName,  $dataStructure[$this->frenosInyeccionAndRefrigeranteSequence[$i]][0],$dataStructure[$this->frenosInyeccionAndRefrigeranteSequence[$i]][1]);
                    $this->writeBitacora("Se ha insertado en catalgo opcion para frenos con ID: ".var_export($insertOpcionVariosID,true), $fileName);
                    $this->writeBitacora("Se registrara en catalogo fluido de frenos : ", $fileName);
                    $variosIDsCatalogs[$this->frenosInyeccionAndRefrigeranteSequence[$i]] = $this->inscribeFluidoFrenos->executeQuery('insert', $fileName,  $insertOpcionVariosID ?? 0);
                    $this->writeBitacora("Se ha insertado en catalgo fluido de frenos con ID: ".var_export($variosIDsCatalogs[$this->frenosInyeccionAndRefrigeranteSequence[$i]],true), $fileName);
                    
                    # code...
                    break;
                case '0_200k_refrigerante':
                    $refrigeranteOpcionIDs[] = $this->inscribeOpcion->executeQuery('insert', $fileName,  $dataStructure[$this->frenosInyeccionAndRefrigeranteSequence[$i]][0],$dataStructure[$this->frenosInyeccionAndRefrigeranteSequence[$i]][1]);
                    $this->writeBitacora("Se ha insertado en catalgo opcion para refrigerante con ID: ".var_export($refrigeranteOpcionIDs,true), $fileName);
                    break;
                case '200k_o_mas_refrigerante':
                    $refrigeranteOpcionIDs[] = $this->inscribeOpcion->executeQuery('insert', $fileName,  $dataStructure[$this->frenosInyeccionAndRefrigeranteSequence[$i]][0],$dataStructure[$this->frenosInyeccionAndRefrigeranteSequence[$i]][1]);
                    $this->writeBitacora("Se ha insertado en catalgo opcion para refrigerante con ID: ".var_export($refrigeranteOpcionIDs,true), $fileName);
                    $this->writeBitacora("Se registrara en catalogo refrigerante: ", $fileName);
                    $variosIDsCatalogs["refrigerante"] = $this->inscribeRefrigerante->executeQuery('insert', $fileName,  $refrigeranteOpcionIDs[0] ?? 0 , $refrigeranteOpcionIDs[1] ?? 0) ;
                    $this->writeBitacora("Se ha insertado en catalgo refrigerante con ID: ".var_export($variosIDsCatalogs["refrigerante"],true), $fileName);
                    # code...
                    break;
                case 'aditivo_sistema_inyeccion':
                    $insertOpcionVariosID = $this->inscribeOpcion->executeQuery('insert', $fileName,  $dataStructure[$this->frenosInyeccionAndRefrigeranteSequence[$i]][0],$dataStructure[$this->frenosInyeccionAndRefrigeranteSequence[$i]][1]);
                    $this->writeBitacora("Se ha insertado en catalgo opcion para inyeccion con ID: ".var_export($insertOpcionVariosID,true), $fileName);
                    $this->writeBitacora("Se registrara en catalogo aditivo sistema inyeccion: ", $fileName);
                    $variosIDsCatalogs[$this->frenosInyeccionAndRefrigeranteSequence[$i]] = $this->inscribeAditivoSI->executeQuery('insert', $fileName,  $insertOpcionVariosID ?? 0);
                    $this->writeBitacora("Se ha insertado en catalgo inyeccion con ID: ".var_export($variosIDsCatalogs[$this->frenosInyeccionAndRefrigeranteSequence[$i]],true), $fileName);

                    # code...
                    break;
                
                default:
                    # code...
                    break;
            }
            
            $i = ($i === 28 || $i === 33)?$i+2:$i + 1;
        }
        $this->writeBitacora("Test inserted variosIDsCatalogs: ".var_export($variosIDsCatalogs,true), $fileName);
        return $variosIDsCatalogs;
    }

    public function processToGasolinaAndGrsas(array $dataStructure, string $fileName, array $variosIDsCatalogs) : mixed {
        
        for($i = 38; $i <= array_key_last($this->gasolinaAndGrasasSequence); $i++){
            $this->writeBitacora("Se registrara en catalogo opcion varios {$this->gasolinaAndGrasasSequence[$i]}", $fileName);
            
            switch ($this->gasolinaAndGrasasSequence[$i]) {
                case 'aditivo_gasolina':
                    $variosIDsCatalogs[$this->gasolinaAndGrasasSequence[$i]] = $this->inscribeAditivoGasolina->executeQuery('insert', $fileName,  $dataStructure[$this->gasolinaAndGrasasSequence[$i]] ?? "N/A");
                    $this->writeBitacora("Se ha insertado en catalgo gasolina con ID: ".var_export($variosIDsCatalogs[$this->gasolinaAndGrasasSequence[$i]],true), $fileName);

                    break;
                    
                case 'grasa_baleros':
                    $variosIDsCatalogs[$this->gasolinaAndGrasasSequence[$i]] = $this->inscribeGrasaBaleros->executeQuery('insert', $fileName,  $dataStructure[$this->gasolinaAndGrasasSequence[$i]] ?? "N/A");
                    $this->writeBitacora("Se ha insertado en catalgo baleros con ID: ".var_export($variosIDsCatalogs[$this->gasolinaAndGrasasSequence[$i]],true), $fileName);

                    break;

                    
                case 'grasa_juntas':
                    $variosIDsCatalogs[$this->gasolinaAndGrasasSequence[$i]] = $this->inscribeGrasaJuntas->executeQuery('insert', $fileName,  $dataStructure[$this->gasolinaAndGrasasSequence[$i]] ?? "N/A");
                    $this->writeBitacora("Se ha insertado en catalgo juntas con ID: ".var_export($variosIDsCatalogs[$this->gasolinaAndGrasasSequence[$i]],true), $fileName);

                    break;

                
                case 'grasa_chasi':
                    $variosIDsCatalogs[$this->gasolinaAndGrasasSequence[$i]] = $this->inscribeGrasaChasi->executeQuery('insert', $fileName,  $dataStructure[$this->gasolinaAndGrasasSequence[$i]] ?? "N/A");
                    $this->writeBitacora("Se ha insertado en catalgo chasis con ID: ".var_export($variosIDsCatalogs[$this->gasolinaAndGrasasSequence[$i]],true), $fileName);

                    break;
                default:
                    # code...
                    break;
            }
        }
        
        $this->writeBitacora("Test inserted variosIDsCatalogs 2: ".var_export($variosIDsCatalogs,true), $fileName);
        return $variosIDsCatalogs;
    }

    protected function transformDataIfItsNecesaryLubs(mixed $value, int $key, array $dataStructure, string $fileName = "no_filename", int $inside = 0): mixed
    {

        $value = $this->validateParticularData($key, $value);

        if ((in_array($key, $this->processTransformation) || in_array($key, $this->processTrim)) && !is_bool($value)) {

            if (!in_array($key, $this->processTrim)) {
                $value = str_replace(".", "", $value);
                $value = str_replace(",", "", $value);
                $value = str_replace("/", "", $value);
                $value = str_replace("'", "", $value);
                $value = str_replace('"', "", $value);
            }
            $value = trim($value);
            $value = ltrim($value);
            $value = rtrim($value);
        }

        if ($key === 3) {
            $dataStructure = $this->getExplodeAnio($value, $dataStructure);

        } else {
            switch ($inside) {
                case 1:
                    # code...
                    $value = (!$value) ? $value : preg_replace($this->patron, "", strtoupper($value));
                    $dataStructure[$this->lubricantesSequence[$key]][] = $value;
                    break;
                
                case 2:
                    # code...
                    $value = (!$value) ? $value : preg_replace($this->patron, "", strtoupper($value));
                    $dataStructure[$this->frenosInyeccionAndRefrigeranteSequence[$key]][] = $value;
                    break;     

                case 3:
                    # code...
                    $value = (!$value) ? $value : preg_replace($this->patron, "", strtoupper($value));
                    $dataStructure[$this->gasolinaAndGrasasSequence[$key]] = $value;
                    break;
                default:
                    # code...
                    $value = (!$value) ? $value : preg_replace($this->patron, "", strtoupper($value));
                    $dataStructure[$this->processActualSequence[$key]] = $value;
                    break;
            }
        }

        //Decode/Encode error

        return $dataStructure;
    }

    public function validateParticularData(int $key, mixed $value): mixed
    {
        if ($key === 4 && empty($value))
            $value = "SIN MOTOR";
        else if (in_array($key, $this->processRequired) && empty($value))
            $value = false;
        else if(empty($value))
            $value = "";
        return $value;
    }

    protected function retrieveDataStructure(string $fileName, int $rowKey, array $rowValue) : mixed {
        $dataRow = "|";
        $dataStructure = [];
        foreach($rowValue as $columnKey => $columnValue){

            if(array_key_exists($columnKey,$this->processActualSequence))
                $dataStructure = $this->transformDataIfItsNecesaryLubs($columnValue,$columnKey,$dataStructure, $fileName);
            else if(array_key_exists($columnKey,$this->lubricantesSequence))
                $dataStructure = $this->transformDataIfItsNecesaryLubs($columnValue,$columnKey,$dataStructure, $fileName, 1);
            else if(array_key_exists($columnKey,$this->frenosInyeccionAndRefrigeranteSequence))
                $dataStructure = $this->transformDataIfItsNecesaryLubs($columnValue,$columnKey,$dataStructure, $fileName,2);
            else if(array_key_exists($columnKey,$this->gasolinaAndGrasasSequence))
                $dataStructure = $this->transformDataIfItsNecesaryLubs($columnValue,$columnKey,$dataStructure, $fileName,3);

        }

        return $dataStructure;
    }
}

?>