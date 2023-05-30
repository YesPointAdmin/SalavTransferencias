CREATE SCHEMA `salav_test` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin ;

CREATE TABLE if not exists `salav_test`.`bitacora` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `NombreArchivo` VARCHAR(5000),
  `MensajeProceso` VARCHAR(5000),
  `MensajeErrores` VARCHAR(5000),
  `No_NoecnotradosCatalogoProductos` VARCHAR(500),
  `No_Repetidos` VARCHAR(500),
  `Noerrores` VARCHAR(100),
  `Nocorrectos` VARCHAR(100),
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE if not exists `salav_test`.`opcion` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `opcion_1` char(255),
  `opcion_2` char(255),
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE if not exists `salav_test`.`catalogo_lubricantes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `marca` char(255),
  `modelo` char(255),
  `anio_inicio` char(255),
  `anio_fin` char(240),
  `motor` char(255),
  `viscocidad` char(255),
  `servicio` char(255),
  `homologacion` char(255),
  `inventario_id` int(11),
  `sucursal_id` int(11),
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE if not exists `salav_test`.`grasas_baleros` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `Tipo_lubricante` varchar(5000),
  `nombre` char(255),
 PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE if not exists `salav_test`.`grasa_juntas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `Tipo_lubricante` varchar(5000),
  `nombre` char(255),
PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE if not exists `salav_test`.`grasa_chasi` (
  `id` INT NOT NULL AUTO_INCREMENT,
`Tipo_lubricante`varchar(5000),
`nombre` char(255),
PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE if not exists `salav_test`.`aditivo_gasolina` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `Tipo_lubricante` varchar(5000),
  `nombre` char(255),
PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE if not exists `salav_test`.`lubricante_roshfrans` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `Tipo_lubricante` varchar(5000), 
  `0_60k_id` int(11),
  `61k_100k_id` int(11),
  `101k_150k_id` int(11),
  `151k_200k_id` int(11),
  `200k_o_mas_id` int(11),
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE if not exists `salav_test`.`fluido_de_frenos` (
`id` INT NOT NULL AUTO_INCREMENT,
`Tipo_lubricante` varchar(5000),
`opcion_id` int(11),
PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE if not exists `salav_test`.`aditivo_sistema_inyeccion` (
`id` INT NOT NULL AUTO_INCREMENT,
`Tipo_lubricante` varchar(5000),
`opcion_id` int(11),
PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE if not exists `salav_test`.`refrigerante` (
`id` INT NOT NULL AUTO_INCREMENT,
`Tipo_lubricante` varchar(5000),
`0_200k_id` int(11),
`200k_o_mas_id` int(11),
PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE if not exists `salav_test`.`catalogo_producto` (
`id` INT NOT NULL AUTO_INCREMENT,
`id_web` int(11),
`Producto_LstPrec` varchar(500),
`part_number` char(255),
`descripcion` char(255),
`tipo` varchar(255),
`url_ficha` char(255),
`imagen` varchar(5000),
`pdf` varchar(5000),
`clasificacionabc` varchar(500),
`proveedor_id` int(11),
`Precio` double,
`imglo` varchar(5000),
PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE if not exists `salav_test`.`sucursal` (
`id` INT NOT NULL AUTO_INCREMENT,
`id_web` int(11),
`descripcion` char(255),
`direccion` char(255),
`sucursal` char(255),
`ubicacion` char(255),
PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE if not exists `salav_test`.`master_lubricantes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_cat_lubricantes` int(11),
  `id_lubricante` int(11),
  `id_frenos` int(11),
  `id_refrigerante` int(11),
  `id_aditivo_inyeccion` int(11),
  `id_aditivo_gas` int(11),
  `id_grasa_chasis` int(11),
  `id_grasa_juntas` int(11),
  `id_grasa_baleros` int(11),
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE if not exists `salav_test`.`ProductosSalav` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `Marca` VARCHAR(100),
  `Modelo` VARCHAR(50),
  `Anio_inicio` VARCHAR(50),
  `Anio_fin` VARCHAR(50),
  `motor` VARCHAR(50),
  `Cil` VARCHAR(50),
  `Part_number` VARCHAR(50),
  `Position` VARCHAR(50),
  `Part_type` VARCHAR(50),
  `Id_catprod` int(11),
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE if not exists `salav_test`.`usuario` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_web` int(11),
  `nombre` char(255),
  `contrasenauser` char(255),
  `usuario` char(255),
  `sucursal_id` int(11),
 PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE if not exists `salav_test`.`proveedor` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` char(255),
  `tipo` char(255),
 PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE if not exists `salav_test`.`inventario` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_web` varchar(255),
  `producto_id` varchar(255),
  `cantidad` varchar(50),
  `sucursal_id` varchar(255),
 PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE if not exists `salav_test`.`catalogo_quimicos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` char(255),
  `descripcion` char(255),
  `division` char(255),
  `marca` char(255),
  `aplicacion` char(255),
  `catalogoprod_id` int(11),
 PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;