CREATE DATABASE IF NOT EXISTS `gestick`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `gestick`;

SET FOREIGN_KEY_CHECKS = 0;

DROP VIEW IF EXISTS `vista_componentes_pc`;
DROP VIEW IF EXISTS `vista_planilla_detalle`;
DROP VIEW IF EXISTS `vista_tickets`;

DROP TABLE IF EXISTS `administrador`;
CREATE TABLE `administrador` (
  `id_usuario` int(11) NOT NULL,
  PRIMARY KEY (`id_usuario`),
  CONSTRAINT `administrador_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `alumno`;
CREATE TABLE `alumno` (
  `id_alumno` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(60) NOT NULL,
  `apellido` varchar(60) NOT NULL,
  `id_grupo` int(11) NOT NULL,
  PRIMARY KEY (`id_alumno`),
  KEY `id_grupo` (`id_grupo`),
  CONSTRAINT `alumno_ibfk_1` FOREIGN KEY (`id_grupo`) REFERENCES `grupo` (`id_grupo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `asignatura`;
CREATE TABLE `asignatura` (
  `id_asignatura` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_asignatura` varchar(100) NOT NULL,
  PRIMARY KEY (`id_asignatura`),
  UNIQUE KEY `nombre_asignatura` (`nombre_asignatura`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `docente`;
CREATE TABLE `docente` (
  `id_usuario` int(11) NOT NULL,
  PRIMARY KEY (`id_usuario`),
  CONSTRAINT `docente_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `docente_asignatura`;
CREATE TABLE `docente_asignatura` (
  `id_docente` int(11) NOT NULL,
  `id_asignatura` int(11) NOT NULL,
  PRIMARY KEY (`id_docente`,`id_asignatura`),
  KEY `id_asignatura` (`id_asignatura`),
  CONSTRAINT `docente_asignatura_ibfk_1` FOREIGN KEY (`id_docente`) REFERENCES `docente` (`id_usuario`),
  CONSTRAINT `docente_asignatura_ibfk_2` FOREIGN KEY (`id_asignatura`) REFERENCES `asignatura` (`id_asignatura`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `docente_grupo`;
CREATE TABLE `docente_grupo` (
  `id_docente` int(11) NOT NULL,
  `id_grupo` int(11) NOT NULL,
  PRIMARY KEY (`id_docente`,`id_grupo`),
  KEY `id_grupo` (`id_grupo`),
  CONSTRAINT `docente_grupo_ibfk_1` FOREIGN KEY (`id_docente`) REFERENCES `docente` (`id_usuario`),
  CONSTRAINT `docente_grupo_ibfk_2` FOREIGN KEY (`id_grupo`) REFERENCES `grupo` (`id_grupo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `docente_turno`;
CREATE TABLE `docente_turno` (
  `id_docente` int(11) NOT NULL,
  `id_turno` int(11) NOT NULL,
  PRIMARY KEY (`id_docente`,`id_turno`),
  KEY `id_turno` (`id_turno`),
  CONSTRAINT `docente_turno_ibfk_1` FOREIGN KEY (`id_docente`) REFERENCES `docente` (`id_usuario`),
  CONSTRAINT `docente_turno_ibfk_2` FOREIGN KEY (`id_turno`) REFERENCES `turno` (`id_turno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `equipo`;
CREATE TABLE `equipo` (
  `id_equipo` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) NOT NULL,
  `numero_serie` varchar(80) DEFAULT NULL,
  `modelo` varchar(80) DEFAULT NULL,
  `id_tipo_equipo` int(11) NOT NULL,
  `id_estado_equipo` int(11) NOT NULL,
  `id_ubicacion` int(11) DEFAULT NULL,
  `es_prestable` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_equipo`),
  UNIQUE KEY `codigo` (`codigo`),
  UNIQUE KEY `numero_serie` (`numero_serie`),
  KEY `id_tipo_equipo` (`id_tipo_equipo`),
  KEY `id_estado_equipo` (`id_estado_equipo`),
  KEY `id_ubicacion` (`id_ubicacion`),
  CONSTRAINT `equipo_ibfk_1` FOREIGN KEY (`id_tipo_equipo`) REFERENCES `tipo_equipo` (`id_tipo_equipo`),
  CONSTRAINT `equipo_ibfk_2` FOREIGN KEY (`id_estado_equipo`) REFERENCES `estado_equipo` (`id_estado_equipo`),
  CONSTRAINT `equipo_ibfk_3` FOREIGN KEY (`id_ubicacion`) REFERENCES `ubicacion` (`id_ubicacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `equipo_componente`;
CREATE TABLE `equipo_componente` (
  `id_componente` int(11) NOT NULL AUTO_INCREMENT,
  `id_equipo` int(11) NOT NULL,
  `tipo_componente` enum('Torre','Monitor','Teclado','Mouse') NOT NULL,
  `id_marca` int(11) NOT NULL,
  `numero_serie` varchar(80) DEFAULT NULL,
  `estado` enum('Correcto','Faltante','Dañado') NOT NULL DEFAULT 'Correcto',
  PRIMARY KEY (`id_componente`),
  UNIQUE KEY `numero_serie` (`numero_serie`),
  KEY `id_equipo` (`id_equipo`),
  KEY `id_marca` (`id_marca`),
  CONSTRAINT `equipo_componente_ibfk_1` FOREIGN KEY (`id_equipo`) REFERENCES `equipo` (`id_equipo`),
  CONSTRAINT `equipo_componente_ibfk_2` FOREIGN KEY (`id_marca`) REFERENCES `marca` (`id_marca`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `estado_equipo`;
CREATE TABLE `estado_equipo` (
  `id_estado_equipo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_estado` varchar(30) NOT NULL,
  PRIMARY KEY (`id_estado_equipo`),
  UNIQUE KEY `nombre_estado` (`nombre_estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `estado_prestamo`;
CREATE TABLE `estado_prestamo` (
  `id_estado_prestamo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_estado` varchar(30) NOT NULL,
  PRIMARY KEY (`id_estado_prestamo`),
  UNIQUE KEY `nombre_estado` (`nombre_estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `estado_solicitud`;
CREATE TABLE `estado_solicitud` (
  `id_estado_solicitud` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_estado` varchar(30) NOT NULL,
  PRIMARY KEY (`id_estado_solicitud`),
  UNIQUE KEY `nombre_estado` (`nombre_estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `estado_ticket`;
CREATE TABLE `estado_ticket` (
  `id_estado_ticket` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_estado` varchar(30) NOT NULL,
  PRIMARY KEY (`id_estado_ticket`),
  UNIQUE KEY `nombre_estado` (`nombre_estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `grupo`;
CREATE TABLE `grupo` (
  `id_grupo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_grupo` varchar(20) NOT NULL,
  PRIMARY KEY (`id_grupo`),
  UNIQUE KEY `nombre_grupo` (`nombre_grupo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `marca`;
CREATE TABLE `marca` (
  `id_marca` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_marca` varchar(50) NOT NULL,
  PRIMARY KEY (`id_marca`),
  UNIQUE KEY `nombre_marca` (`nombre_marca`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `planilla`;
CREATE TABLE `planilla` (
  `id_planilla` int(11) NOT NULL AUTO_INCREMENT,
  `id_docente` int(11) NOT NULL,
  `id_grupo` int(11) NOT NULL,
  `id_asignatura` int(11) NOT NULL,
  `id_turno` int(11) NOT NULL,
  `id_ubicacion` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time DEFAULT NULL,
  PRIMARY KEY (`id_planilla`),
  KEY `id_docente` (`id_docente`),
  KEY `id_grupo` (`id_grupo`),
  KEY `id_asignatura` (`id_asignatura`),
  KEY `id_turno` (`id_turno`),
  KEY `id_ubicacion` (`id_ubicacion`),
  CONSTRAINT `planilla_ibfk_1` FOREIGN KEY (`id_docente`) REFERENCES `docente` (`id_usuario`),
  CONSTRAINT `planilla_ibfk_2` FOREIGN KEY (`id_grupo`) REFERENCES `grupo` (`id_grupo`),
  CONSTRAINT `planilla_ibfk_3` FOREIGN KEY (`id_asignatura`) REFERENCES `asignatura` (`id_asignatura`),
  CONSTRAINT `planilla_ibfk_4` FOREIGN KEY (`id_turno`) REFERENCES `turno` (`id_turno`),
  CONSTRAINT `planilla_ibfk_5` FOREIGN KEY (`id_ubicacion`) REFERENCES `ubicacion` (`id_ubicacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `planilla_detalle`;
CREATE TABLE `planilla_detalle` (
  `id_planilla_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `id_planilla` int(11) NOT NULL,
  `id_alumno` int(11) DEFAULT NULL,
  `id_equipo` int(11) NOT NULL,
  `estado_registrado` enum('Correcto','Faltante','Dañado') NOT NULL DEFAULT 'Correcto',
  `observacion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_planilla_detalle`),
  KEY `id_planilla` (`id_planilla`),
  KEY `id_alumno` (`id_alumno`),
  KEY `id_equipo` (`id_equipo`),
  CONSTRAINT `planilla_detalle_ibfk_1` FOREIGN KEY (`id_planilla`) REFERENCES `planilla` (`id_planilla`),
  CONSTRAINT `planilla_detalle_ibfk_2` FOREIGN KEY (`id_alumno`) REFERENCES `alumno` (`id_alumno`),
  CONSTRAINT `planilla_detalle_ibfk_3` FOREIGN KEY (`id_equipo`) REFERENCES `equipo` (`id_equipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `prestamo`;
CREATE TABLE `prestamo` (
  `id_prestamo` int(11) NOT NULL AUTO_INCREMENT,
  `id_alumno` int(11) NOT NULL,
  `fecha_prestamo` date NOT NULL,
  `fecha_devolucion` date DEFAULT NULL,
  `id_estado_prestamo` int(11) NOT NULL,
  PRIMARY KEY (`id_prestamo`),
  KEY `id_alumno` (`id_alumno`),
  KEY `id_estado_prestamo` (`id_estado_prestamo`),
  CONSTRAINT `prestamo_ibfk_1` FOREIGN KEY (`id_alumno`) REFERENCES `alumno` (`id_alumno`),
  CONSTRAINT `prestamo_ibfk_2` FOREIGN KEY (`id_estado_prestamo`) REFERENCES `estado_prestamo` (`id_estado_prestamo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `prestamo_equipo`;
CREATE TABLE `prestamo_equipo` (
  `id_prestamo` int(11) NOT NULL,
  `id_equipo` int(11) NOT NULL,
  PRIMARY KEY (`id_prestamo`,`id_equipo`),
  KEY `id_equipo` (`id_equipo`),
  CONSTRAINT `prestamo_equipo_ibfk_1` FOREIGN KEY (`id_prestamo`) REFERENCES `prestamo` (`id_prestamo`),
  CONSTRAINT `prestamo_equipo_ibfk_2` FOREIGN KEY (`id_equipo`) REFERENCES `equipo` (`id_equipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `prioridad`;
CREATE TABLE `prioridad` (
  `id_prioridad` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_prioridad` varchar(20) NOT NULL,
  PRIMARY KEY (`id_prioridad`),
  UNIQUE KEY `nombre_prioridad` (`nombre_prioridad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `solicitud`;
CREATE TABLE `solicitud` (
  `id_solicitud` int(11) NOT NULL AUTO_INCREMENT,
  `id_docente` int(11) NOT NULL,
  `id_tecnico` int(11) DEFAULT NULL,
  `id_estado_solicitud` int(11) NOT NULL,
  `asunto` varchar(120) NOT NULL,
  `descripcion` text NOT NULL,
  `trabajo_realizado` text DEFAULT NULL,
  `fecha_envio` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_inicio` datetime DEFAULT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  PRIMARY KEY (`id_solicitud`),
  KEY `id_docente` (`id_docente`),
  KEY `id_tecnico` (`id_tecnico`),
  KEY `id_estado_solicitud` (`id_estado_solicitud`),
  CONSTRAINT `solicitud_ibfk_1` FOREIGN KEY (`id_docente`) REFERENCES `docente` (`id_usuario`),
  CONSTRAINT `solicitud_ibfk_2` FOREIGN KEY (`id_estado_solicitud`) REFERENCES `estado_solicitud` (`id_estado_solicitud`),
  CONSTRAINT `solicitud_ibfk_3` FOREIGN KEY (`id_tecnico`) REFERENCES `tecnico` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `tecnico`;
CREATE TABLE `tecnico` (
  `id_usuario` int(11) NOT NULL,
  `id_turno` int(11) NOT NULL,
  PRIMARY KEY (`id_usuario`),
  KEY `id_turno` (`id_turno`),
  CONSTRAINT `tecnico_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`),
  CONSTRAINT `tecnico_ibfk_2` FOREIGN KEY (`id_turno`) REFERENCES `turno` (`id_turno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `ticket`;
CREATE TABLE `ticket` (
  `id_ticket` int(11) NOT NULL AUTO_INCREMENT,
  `id_planilla_detalle` int(11) DEFAULT NULL,
  `id_equipo` int(11) NOT NULL,
  `id_tecnico` int(11) DEFAULT NULL,
  `id_prioridad` int(11) NOT NULL,
  `id_estado_ticket` int(11) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `solucion` text DEFAULT NULL,
  `fecha_generado` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_inicio` datetime DEFAULT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  PRIMARY KEY (`id_ticket`),
  KEY `id_planilla_detalle` (`id_planilla_detalle`),
  KEY `id_equipo` (`id_equipo`),
  KEY `id_tecnico` (`id_tecnico`),
  KEY `id_prioridad` (`id_prioridad`),
  KEY `id_estado_ticket` (`id_estado_ticket`),
  CONSTRAINT `ticket_ibfk_1` FOREIGN KEY (`id_planilla_detalle`) REFERENCES `planilla_detalle` (`id_planilla_detalle`),
  CONSTRAINT `ticket_ibfk_2` FOREIGN KEY (`id_equipo`) REFERENCES `equipo` (`id_equipo`),
  CONSTRAINT `ticket_ibfk_3` FOREIGN KEY (`id_tecnico`) REFERENCES `tecnico` (`id_usuario`),
  CONSTRAINT `ticket_ibfk_4` FOREIGN KEY (`id_prioridad`) REFERENCES `prioridad` (`id_prioridad`),
  CONSTRAINT `ticket_ibfk_5` FOREIGN KEY (`id_estado_ticket`) REFERENCES `estado_ticket` (`id_estado_ticket`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `tipo_equipo`;
CREATE TABLE `tipo_equipo` (
  `id_tipo_equipo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_tipo` varchar(30) NOT NULL,
  PRIMARY KEY (`id_tipo_equipo`),
  UNIQUE KEY `nombre_tipo` (`nombre_tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `turno`;
CREATE TABLE `turno` (
  `id_turno` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_turno` varchar(20) NOT NULL,
  PRIMARY KEY (`id_turno`),
  UNIQUE KEY `nombre_turno` (`nombre_turno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `ubicacion`;
CREATE TABLE `ubicacion` (
  `id_ubicacion` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_ubicacion` varchar(60) NOT NULL,
  PRIMARY KEY (`id_ubicacion`),
  UNIQUE KEY `nombre_ubicacion` (`nombre_ubicacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `usuario`;
CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(60) NOT NULL,
  `apellido` varchar(60) NOT NULL,
  `correo` varchar(120) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `rol` enum('Administrador','Tecnico','Docente') NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE VIEW `vista_componentes_pc` AS SELECT `e`.`codigo` AS `pc`,`ub`.`nombre_ubicacion` AS `laboratorio`,`ec`.`tipo_componente` AS `tipo_componente`,`m`.`nombre_marca` AS `marca`,`ec`.`numero_serie` AS `numero_serie`,`ec`.`estado` AS `estado` from (((`equipo_componente` `ec` join `equipo` `e` on(`e`.`id_equipo` = `ec`.`id_equipo`)) join `marca` `m` on(`m`.`id_marca` = `ec`.`id_marca`)) left join `ubicacion` `ub` on(`ub`.`id_ubicacion` = `e`.`id_ubicacion`));

CREATE VIEW `vista_planilla_detalle` AS SELECT `pd`.`id_planilla_detalle` AS `id_planilla_detalle`,`pd`.`id_planilla` AS `id_planilla`,`pl`.`fecha` AS `fecha`,`u`.`nombre` AS `nombre_docente`,`u`.`apellido` AS `apellido_docente`,`g`.`nombre_grupo` AS `nombre_grupo`,`a`.`nombre_asignatura` AS `nombre_asignatura`,`ub`.`nombre_ubicacion` AS `laboratorio`,`e`.`codigo` AS `equipo`,concat(`al`.`nombre`,' ',`al`.`apellido`) AS `alumno`,`pd`.`estado_registrado` AS `estado_registrado`,`pd`.`observacion` AS `observacion` from (((((((`planilla_detalle` `pd` join `planilla` `pl` on(`pl`.`id_planilla` = `pd`.`id_planilla`)) join `usuario` `u` on(`u`.`id_usuario` = `pl`.`id_docente`)) join `grupo` `g` on(`g`.`id_grupo` = `pl`.`id_grupo`)) join `asignatura` `a` on(`a`.`id_asignatura` = `pl`.`id_asignatura`)) join `ubicacion` `ub` on(`ub`.`id_ubicacion` = `pl`.`id_ubicacion`)) join `equipo` `e` on(`e`.`id_equipo` = `pd`.`id_equipo`)) left join `alumno` `al` on(`al`.`id_alumno` = `pd`.`id_alumno`));

CREATE VIEW `vista_tickets` AS SELECT `t`.`id_ticket` AS `id_ticket`,`e`.`codigo` AS `equipo`,`t`.`descripcion` AS `descripcion`,concat(`ut`.`nombre`,' ',`ut`.`apellido`) AS `tecnico`,`p`.`nombre_prioridad` AS `prioridad`,`et`.`nombre_estado` AS `estado`,`t`.`fecha_generado` AS `fecha_generado`,`t`.`fecha_inicio` AS `fecha_inicio`,`t`.`fecha_fin` AS `fecha_fin`,`t`.`solucion` AS `solucion` from ((((`ticket` `t` join `equipo` `e` on(`e`.`id_equipo` = `t`.`id_equipo`)) left join `usuario` `ut` on(`ut`.`id_usuario` = `t`.`id_tecnico`)) join `prioridad` `p` on(`p`.`id_prioridad` = `t`.`id_prioridad`)) join `estado_ticket` `et` on(`et`.`id_estado_ticket` = `t`.`id_estado_ticket`));

SET FOREIGN_KEY_CHECKS = 1;
