USE `gestick`;

SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO `usuario` (`id_usuario`, `nombre`, `apellido`, `correo`, `contrasena`, `rol`, `activo`, `fecha_registro`) VALUES (1,'Docente','Uno','docente1@gestick.test','$2y$10$qm1hlGo5D8/c8ZuDRTBL0eKqHJ.e109xtUzdIuBtQAU/Lf5eL5c4.','Docente',1,'2026-08-17 03:49:07');
INSERT INTO `usuario` (`id_usuario`, `nombre`, `apellido`, `correo`, `contrasena`, `rol`, `activo`, `fecha_registro`) VALUES (2,'Docente','Dos','docente2@gestick.test','$2y$10$qm1hlGo5D8/c8ZuDRTBL0eKqHJ.e109xtUzdIuBtQAU/Lf5eL5c4.','Docente',1,'2026-08-17 03:49:07');
INSERT INTO `usuario` (`id_usuario`, `nombre`, `apellido`, `correo`, `contrasena`, `rol`, `activo`, `fecha_registro`) VALUES (3,'Docente','Tres','docente3@gestick.test','$2y$10$qm1hlGo5D8/c8ZuDRTBL0eKqHJ.e109xtUzdIuBtQAU/Lf5eL5c4.','Docente',1,'2026-08-17 03:49:07');
INSERT INTO `usuario` (`id_usuario`, `nombre`, `apellido`, `correo`, `contrasena`, `rol`, `activo`, `fecha_registro`) VALUES (4,'Docente','Cuatro','docente4@gestick.test','$2y$10$qm1hlGo5D8/c8ZuDRTBL0eKqHJ.e109xtUzdIuBtQAU/Lf5eL5c4.','Docente',1,'2026-08-17 03:49:07');
INSERT INTO `usuario` (`id_usuario`, `nombre`, `apellido`, `correo`, `contrasena`, `rol`, `activo`, `fecha_registro`) VALUES (5,'Tecnico','Uno','tecnico1@gestick.test','$2y$10$qm1hlGo5D8/c8ZuDRTBL0eKqHJ.e109xtUzdIuBtQAU/Lf5eL5c4.','Tecnico',1,'2026-08-17 03:49:07');
INSERT INTO `usuario` (`id_usuario`, `nombre`, `apellido`, `correo`, `contrasena`, `rol`, `activo`, `fecha_registro`) VALUES (6,'Tecnico','Dos','tecnico2@gestick.test','$2y$10$qm1hlGo5D8/c8ZuDRTBL0eKqHJ.e109xtUzdIuBtQAU/Lf5eL5c4.','Tecnico',1,'2026-08-17 03:49:07');
INSERT INTO `usuario` (`id_usuario`, `nombre`, `apellido`, `correo`, `contrasena`, `rol`, `activo`, `fecha_registro`) VALUES (7,'Tecnico','Tres','tecnico3@gestick.test','$2y$10$qm1hlGo5D8/c8ZuDRTBL0eKqHJ.e109xtUzdIuBtQAU/Lf5eL5c4.','Tecnico',1,'2026-08-17 03:49:07');
INSERT INTO `usuario` (`id_usuario`, `nombre`, `apellido`, `correo`, `contrasena`, `rol`, `activo`, `fecha_registro`) VALUES (8,'Administrador','Principal','admin@gestick.test','$2y$10$qm1hlGo5D8/c8ZuDRTBL0eKqHJ.e109xtUzdIuBtQAU/Lf5eL5c4.','Administrador',1,'2026-08-17 03:49:07');
INSERT INTO `usuario` (`id_usuario`, `nombre`, `apellido`, `correo`, `contrasena`, `rol`, `activo`, `fecha_registro`) VALUES (10,'Administrador','Secundario','admin2@gestick.test','$2y$10$qm1hlGo5D8/c8ZuDRTBL0eKqHJ.e109xtUzdIuBtQAU/Lf5eL5c4.','Administrador',1,'2026-08-23 00:43:03');
INSERT INTO `usuario` (`id_usuario`, `nombre`, `apellido`, `correo`, `contrasena`, `rol`, `activo`, `fecha_registro`) VALUES (11,'Tecnico','Cuatro','tecnico4@gestick.test','$2y$10$qm1hlGo5D8/c8ZuDRTBL0eKqHJ.e109xtUzdIuBtQAU/Lf5eL5c4.','Tecnico',1,'2026-08-23 05:39:01');

INSERT INTO `administrador` (`id_usuario`) VALUES (8);
INSERT INTO `administrador` (`id_usuario`) VALUES (10);

INSERT INTO `alumno` (`id_alumno`, `nombre`, `apellido`, `id_grupo`) VALUES (1,'Samara','González',1);
INSERT INTO `alumno` (`id_alumno`, `nombre`, `apellido`, `id_grupo`) VALUES (2,'Roberto','Marisquirena',1);
INSERT INTO `alumno` (`id_alumno`, `nombre`, `apellido`, `id_grupo`) VALUES (3,'Victoria','Garderes',1);
INSERT INTO `alumno` (`id_alumno`, `nombre`, `apellido`, `id_grupo`) VALUES (4,'Rodrigo','Rodríguez',1);
INSERT INTO `alumno` (`id_alumno`, `nombre`, `apellido`, `id_grupo`) VALUES (5,'Mateo','Silva',2);
INSERT INTO `alumno` (`id_alumno`, `nombre`, `apellido`, `id_grupo`) VALUES (6,'Camila','Pereira',2);
INSERT INTO `alumno` (`id_alumno`, `nombre`, `apellido`, `id_grupo`) VALUES (7,'Lucas','Fernández',2);
INSERT INTO `alumno` (`id_alumno`, `nombre`, `apellido`, `id_grupo`) VALUES (8,'Agustina','Rodríguez',3);
INSERT INTO `alumno` (`id_alumno`, `nombre`, `apellido`, `id_grupo`) VALUES (9,'Nicolás','Martínez',3);
INSERT INTO `alumno` (`id_alumno`, `nombre`, `apellido`, `id_grupo`) VALUES (10,'Valentina','Suárez',3);
INSERT INTO `alumno` (`id_alumno`, `nombre`, `apellido`, `id_grupo`) VALUES (11,'Samara','González',1);
INSERT INTO `alumno` (`id_alumno`, `nombre`, `apellido`, `id_grupo`) VALUES (12,'Roberto','Marisquirena',1);
INSERT INTO `alumno` (`id_alumno`, `nombre`, `apellido`, `id_grupo`) VALUES (13,'Victoria','Garderes',1);
INSERT INTO `alumno` (`id_alumno`, `nombre`, `apellido`, `id_grupo`) VALUES (14,'Rodrigo','Rodríguez',1);
INSERT INTO `alumno` (`id_alumno`, `nombre`, `apellido`, `id_grupo`) VALUES (15,'Mateo','Silva',2);
INSERT INTO `alumno` (`id_alumno`, `nombre`, `apellido`, `id_grupo`) VALUES (16,'Camila','Pereira',2);
INSERT INTO `alumno` (`id_alumno`, `nombre`, `apellido`, `id_grupo`) VALUES (17,'Lucas','Fernández',2);
INSERT INTO `alumno` (`id_alumno`, `nombre`, `apellido`, `id_grupo`) VALUES (18,'Agustina','Rodríguez',3);
INSERT INTO `alumno` (`id_alumno`, `nombre`, `apellido`, `id_grupo`) VALUES (19,'Nicolás','Martínez',3);
INSERT INTO `alumno` (`id_alumno`, `nombre`, `apellido`, `id_grupo`) VALUES (20,'Valentina','Suárez',3);

INSERT INTO `asignatura` (`id_asignatura`, `nombre_asignatura`) VALUES (4,'Administración de Sistemas Operativos');
INSERT INTO `asignatura` (`id_asignatura`, `nombre_asignatura`) VALUES (2,'Ingeniería de Software');
INSERT INTO `asignatura` (`id_asignatura`, `nombre_asignatura`) VALUES (3,'Programación Full Stack');
INSERT INTO `asignatura` (`id_asignatura`, `nombre_asignatura`) VALUES (1,'Tutoría de Proyecto UTULAB');

INSERT INTO `docente` (`id_usuario`) VALUES (1);
INSERT INTO `docente` (`id_usuario`) VALUES (2);
INSERT INTO `docente` (`id_usuario`) VALUES (3);
INSERT INTO `docente` (`id_usuario`) VALUES (4);

INSERT INTO `docente_asignatura` (`id_docente`, `id_asignatura`) VALUES (1,1);
INSERT INTO `docente_asignatura` (`id_docente`, `id_asignatura`) VALUES (2,2);
INSERT INTO `docente_asignatura` (`id_docente`, `id_asignatura`) VALUES (3,3);
INSERT INTO `docente_asignatura` (`id_docente`, `id_asignatura`) VALUES (4,4);

INSERT INTO `docente_grupo` (`id_docente`, `id_grupo`) VALUES (1,1);
INSERT INTO `docente_grupo` (`id_docente`, `id_grupo`) VALUES (2,1);
INSERT INTO `docente_grupo` (`id_docente`, `id_grupo`) VALUES (3,1);
INSERT INTO `docente_grupo` (`id_docente`, `id_grupo`) VALUES (4,1);
INSERT INTO `docente_grupo` (`id_docente`, `id_grupo`) VALUES (1,2);
INSERT INTO `docente_grupo` (`id_docente`, `id_grupo`) VALUES (2,2);
INSERT INTO `docente_grupo` (`id_docente`, `id_grupo`) VALUES (3,2);
INSERT INTO `docente_grupo` (`id_docente`, `id_grupo`) VALUES (4,2);
INSERT INTO `docente_grupo` (`id_docente`, `id_grupo`) VALUES (1,3);
INSERT INTO `docente_grupo` (`id_docente`, `id_grupo`) VALUES (2,3);
INSERT INTO `docente_grupo` (`id_docente`, `id_grupo`) VALUES (3,3);
INSERT INTO `docente_grupo` (`id_docente`, `id_grupo`) VALUES (4,3);

INSERT INTO `docente_turno` (`id_docente`, `id_turno`) VALUES (1,3);
INSERT INTO `docente_turno` (`id_docente`, `id_turno`) VALUES (2,3);
INSERT INTO `docente_turno` (`id_docente`, `id_turno`) VALUES (3,3);
INSERT INTO `docente_turno` (`id_docente`, `id_turno`) VALUES (4,3);

INSERT INTO `equipo` (`id_equipo`, `codigo`, `numero_serie`, `modelo`, `id_tipo_equipo`, `id_estado_equipo`, `id_ubicacion`, `es_prestable`) VALUES (1,'LAB1-PC01','PC-L1-001','Dell OptiPlex',1,1,1,0);
INSERT INTO `equipo` (`id_equipo`, `codigo`, `numero_serie`, `modelo`, `id_tipo_equipo`, `id_estado_equipo`, `id_ubicacion`, `es_prestable`) VALUES (2,'LAB1-PC02','PC-L1-002','Dell OptiPlex',1,5,1,0);
INSERT INTO `equipo` (`id_equipo`, `codigo`, `numero_serie`, `modelo`, `id_tipo_equipo`, `id_estado_equipo`, `id_ubicacion`, `es_prestable`) VALUES (3,'LAB1-PC03','PC-L1-003','Dell OptiPlex',1,2,1,0);
INSERT INTO `equipo` (`id_equipo`, `codigo`, `numero_serie`, `modelo`, `id_tipo_equipo`, `id_estado_equipo`, `id_ubicacion`, `es_prestable`) VALUES (4,'LAB1-PC04','PC-L1-004','Dell OptiPlex',1,1,1,0);
INSERT INTO `equipo` (`id_equipo`, `codigo`, `numero_serie`, `modelo`, `id_tipo_equipo`, `id_estado_equipo`, `id_ubicacion`, `es_prestable`) VALUES (5,'LAB2-PC01','PC-L2-001','Dell OptiPlex',1,1,2,0);
INSERT INTO `equipo` (`id_equipo`, `codigo`, `numero_serie`, `modelo`, `id_tipo_equipo`, `id_estado_equipo`, `id_ubicacion`, `es_prestable`) VALUES (6,'LAB2-PC02','PC-L2-002','Dell OptiPlex',1,2,2,0);
INSERT INTO `equipo` (`id_equipo`, `codigo`, `numero_serie`, `modelo`, `id_tipo_equipo`, `id_estado_equipo`, `id_ubicacion`, `es_prestable`) VALUES (7,'LAB2-PC03','PC-L2-003','Dell OptiPlex',1,2,2,0);
INSERT INTO `equipo` (`id_equipo`, `codigo`, `numero_serie`, `modelo`, `id_tipo_equipo`, `id_estado_equipo`, `id_ubicacion`, `es_prestable`) VALUES (8,'LAB2-PC04','PC-L2-004','Dell OptiPlex',1,1,2,0);
INSERT INTO `equipo` (`id_equipo`, `codigo`, `numero_serie`, `modelo`, `id_tipo_equipo`, `id_estado_equipo`, `id_ubicacion`, `es_prestable`) VALUES (9,'LAB3-PC01','PC-L3-001','Dell OptiPlex',1,1,3,0);
INSERT INTO `equipo` (`id_equipo`, `codigo`, `numero_serie`, `modelo`, `id_tipo_equipo`, `id_estado_equipo`, `id_ubicacion`, `es_prestable`) VALUES (10,'LAB3-PC02','PC-L3-002','Dell OptiPlex',1,1,3,0);
INSERT INTO `equipo` (`id_equipo`, `codigo`, `numero_serie`, `modelo`, `id_tipo_equipo`, `id_estado_equipo`, `id_ubicacion`, `es_prestable`) VALUES (11,'LAB3-PC03','PC-L3-003','Dell OptiPlex',1,1,3,0);
INSERT INTO `equipo` (`id_equipo`, `codigo`, `numero_serie`, `modelo`, `id_tipo_equipo`, `id_estado_equipo`, `id_ubicacion`, `es_prestable`) VALUES (12,'LAB3-PC04','PC-L3-004','Dell OptiPlex',1,1,3,0);
INSERT INTO `equipo` (`id_equipo`, `codigo`, `numero_serie`, `modelo`, `id_tipo_equipo`, `id_estado_equipo`, `id_ubicacion`, `es_prestable`) VALUES (13,'LAB4-PC01','PC-L4-001','Dell OptiPlex',1,1,4,0);
INSERT INTO `equipo` (`id_equipo`, `codigo`, `numero_serie`, `modelo`, `id_tipo_equipo`, `id_estado_equipo`, `id_ubicacion`, `es_prestable`) VALUES (14,'LAB4-PC02','PC-L4-002','Dell OptiPlex',1,1,4,0);
INSERT INTO `equipo` (`id_equipo`, `codigo`, `numero_serie`, `modelo`, `id_tipo_equipo`, `id_estado_equipo`, `id_ubicacion`, `es_prestable`) VALUES (15,'LAB4-PC03','PC-L4-003','Dell OptiPlex',1,1,4,0);
INSERT INTO `equipo` (`id_equipo`, `codigo`, `numero_serie`, `modelo`, `id_tipo_equipo`, `id_estado_equipo`, `id_ubicacion`, `es_prestable`) VALUES (16,'LAB4-PC04','PC-L4-004','Dell OptiPlex',1,1,4,0);
INSERT INTO `equipo` (`id_equipo`, `codigo`, `numero_serie`, `modelo`, `id_tipo_equipo`, `id_estado_equipo`, `id_ubicacion`, `es_prestable`) VALUES (17,'LAB5-PC01','PC-L5-001','Dell OptiPlex',1,1,5,0);
INSERT INTO `equipo` (`id_equipo`, `codigo`, `numero_serie`, `modelo`, `id_tipo_equipo`, `id_estado_equipo`, `id_ubicacion`, `es_prestable`) VALUES (18,'LAB5-PC02','PC-L5-002','Dell OptiPlex',1,1,5,0);
INSERT INTO `equipo` (`id_equipo`, `codigo`, `numero_serie`, `modelo`, `id_tipo_equipo`, `id_estado_equipo`, `id_ubicacion`, `es_prestable`) VALUES (19,'LAB5-PC03','PC-L5-003','Dell OptiPlex',1,1,5,0);
INSERT INTO `equipo` (`id_equipo`, `codigo`, `numero_serie`, `modelo`, `id_tipo_equipo`, `id_estado_equipo`, `id_ubicacion`, `es_prestable`) VALUES (20,'LAB5-PC04','PC-L5-004','Dell OptiPlex',1,1,5,0);
INSERT INTO `equipo` (`id_equipo`, `codigo`, `numero_serie`, `modelo`, `id_tipo_equipo`, `id_estado_equipo`, `id_ubicacion`, `es_prestable`) VALUES (21,'CEIB-001','CEI-001','Magallanes',2,1,NULL,1);
INSERT INTO `equipo` (`id_equipo`, `codigo`, `numero_serie`, `modelo`, `id_tipo_equipo`, `id_estado_equipo`, `id_ubicacion`, `es_prestable`) VALUES (22,'CEIB-002','CEI-002','Positivo BGH',2,5,NULL,1);
INSERT INTO `equipo` (`id_equipo`, `codigo`, `numero_serie`, `modelo`, `id_tipo_equipo`, `id_estado_equipo`, `id_ubicacion`, `es_prestable`) VALUES (23,'CEIB-003','CEI-003','Clamshell',2,1,NULL,1);
INSERT INTO `equipo` (`id_equipo`, `codigo`, `numero_serie`, `modelo`, `id_tipo_equipo`, `id_estado_equipo`, `id_ubicacion`, `es_prestable`) VALUES (24,'CEIB-004','CEI-004','JP.IK',2,1,NULL,1);
INSERT INTO `equipo` (`id_equipo`, `codigo`, `numero_serie`, `modelo`, `id_tipo_equipo`, `id_estado_equipo`, `id_ubicacion`, `es_prestable`) VALUES (46,'PRUEBA','1213','hola',1,1,1,0);

INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (1,1,'Torre',1,'TOR-L1-01','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (2,1,'Monitor',2,'MON-L1-01','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (3,1,'Teclado',3,'TEC-L1-01','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (4,1,'Mouse',3,'MOU-L1-01','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (5,2,'Torre',1,'TOR-L1-02','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (6,2,'Monitor',2,'MON-L1-02','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (7,2,'Teclado',3,'TEC-L1-02','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (8,2,'Mouse',3,'MOU-L1-02','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (9,3,'Torre',1,'TOR-L1-03','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (10,3,'Monitor',2,'MON-L1-03','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (11,3,'Teclado',3,'TEC-L1-03','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (12,3,'Mouse',3,'MOU-L1-03','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (13,4,'Torre',1,'TOR-L1-04','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (14,4,'Monitor',2,'MON-L1-04','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (15,4,'Teclado',3,'TEC-L1-04','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (16,4,'Mouse',3,'MOU-L1-04','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (17,5,'Torre',1,'TOR-L2-01','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (18,5,'Monitor',2,'MON-L2-01','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (19,5,'Teclado',3,'TEC-L2-01','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (20,5,'Mouse',3,'MOU-L2-01','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (21,6,'Torre',1,'TOR-L2-02','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (22,6,'Monitor',2,'MON-L2-02','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (23,6,'Teclado',3,'TEC-L2-02','Dañado');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (24,6,'Mouse',3,'MOU-L2-02','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (25,7,'Torre',1,'TOR-L2-03','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (26,7,'Monitor',2,'MON-L2-03','Dañado');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (27,7,'Teclado',3,'TEC-L2-03','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (28,7,'Mouse',3,'MOU-L2-03','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (29,8,'Torre',1,'TOR-L2-04','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (30,8,'Monitor',2,'MON-L2-04','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (31,8,'Teclado',3,'TEC-L2-04','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (32,8,'Mouse',3,'MOU-L2-04','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (33,9,'Torre',1,'TOR-L3-01','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (34,9,'Monitor',2,'MON-L3-01','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (35,9,'Teclado',3,'TEC-L3-01','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (36,9,'Mouse',3,'MOU-L3-01','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (37,10,'Torre',1,'TOR-L3-02','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (38,10,'Monitor',2,'MON-L3-02','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (39,10,'Teclado',3,'TEC-L3-02','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (40,10,'Mouse',3,'MOU-L3-02','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (41,11,'Torre',1,'TOR-L3-03','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (42,11,'Monitor',2,'MON-L3-03','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (43,11,'Teclado',3,'TEC-L3-03','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (44,11,'Mouse',3,'MOU-L3-03','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (45,12,'Torre',1,'TOR-L3-04','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (46,12,'Monitor',2,'MON-L3-04','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (47,12,'Teclado',3,'TEC-L3-04','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (48,12,'Mouse',3,'MOU-L3-04','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (49,13,'Torre',1,'TOR-L4-01','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (50,13,'Monitor',2,'MON-L4-01','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (51,13,'Teclado',3,'TEC-L4-01','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (52,13,'Mouse',3,'MOU-L4-01','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (53,14,'Torre',1,'TOR-L4-02','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (54,14,'Monitor',2,'MON-L4-02','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (55,14,'Teclado',3,'TEC-L4-02','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (56,14,'Mouse',3,'MOU-L4-02','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (57,15,'Torre',1,'TOR-L4-03','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (58,15,'Monitor',2,'MON-L4-03','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (59,15,'Teclado',3,'TEC-L4-03','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (60,15,'Mouse',3,'MOU-L4-03','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (61,16,'Torre',1,'TOR-L4-04','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (62,16,'Monitor',2,'MON-L4-04','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (63,16,'Teclado',3,'TEC-L4-04','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (64,16,'Mouse',3,'MOU-L4-04','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (65,17,'Torre',1,'TOR-L5-01','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (66,17,'Monitor',2,'MON-L5-01','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (67,17,'Teclado',3,'TEC-L5-01','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (68,17,'Mouse',3,'MOU-L5-01','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (69,18,'Torre',1,'TOR-L5-02','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (70,18,'Monitor',2,'MON-L5-02','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (71,18,'Teclado',3,'TEC-L5-02','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (72,18,'Mouse',3,'MOU-L5-02','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (73,19,'Torre',1,'TOR-L5-03','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (74,19,'Monitor',2,'MON-L5-03','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (75,19,'Teclado',3,'TEC-L5-03','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (76,19,'Mouse',3,'MOU-L5-03','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (77,20,'Torre',1,'TOR-L5-04','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (78,20,'Monitor',2,'MON-L5-04','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (79,20,'Teclado',3,'TEC-L5-04','Correcto');
INSERT INTO `equipo_componente` (`id_componente`, `id_equipo`, `tipo_componente`, `id_marca`, `numero_serie`, `estado`) VALUES (80,20,'Mouse',3,'MOU-L5-04','Correcto');

INSERT INTO `estado_equipo` (`id_estado_equipo`, `nombre_estado`) VALUES (1,'Correcto');
INSERT INTO `estado_equipo` (`id_estado_equipo`, `nombre_estado`) VALUES (3,'Dañado');
INSERT INTO `estado_equipo` (`id_estado_equipo`, `nombre_estado`) VALUES (2,'En reparación');
INSERT INTO `estado_equipo` (`id_estado_equipo`, `nombre_estado`) VALUES (4,'Faltante');
INSERT INTO `estado_equipo` (`id_estado_equipo`, `nombre_estado`) VALUES (5,'Prestado');

INSERT INTO `estado_prestamo` (`id_estado_prestamo`, `nombre_estado`) VALUES (1,'Activo');
INSERT INTO `estado_prestamo` (`id_estado_prestamo`, `nombre_estado`) VALUES (3,'Atrasado');
INSERT INTO `estado_prestamo` (`id_estado_prestamo`, `nombre_estado`) VALUES (2,'Devuelto');

INSERT INTO `estado_solicitud` (`id_estado_solicitud`, `nombre_estado`) VALUES (5,'Cancelada');
INSERT INTO `estado_solicitud` (`id_estado_solicitud`, `nombre_estado`) VALUES (3,'Completada');
INSERT INTO `estado_solicitud` (`id_estado_solicitud`, `nombre_estado`) VALUES (2,'En proceso');
INSERT INTO `estado_solicitud` (`id_estado_solicitud`, `nombre_estado`) VALUES (1,'Pendiente');

INSERT INTO `estado_ticket` (`id_estado_ticket`, `nombre_estado`) VALUES (2,'En proceso');
INSERT INTO `estado_ticket` (`id_estado_ticket`, `nombre_estado`) VALUES (1,'Pendiente');
INSERT INTO `estado_ticket` (`id_estado_ticket`, `nombre_estado`) VALUES (3,'Resuelto');

INSERT INTO `grupo` (`id_grupo`, `nombre_grupo`) VALUES (1,'3°MF');
INSERT INTO `grupo` (`id_grupo`, `nombre_grupo`) VALUES (3,'3°MH');
INSERT INTO `grupo` (`id_grupo`, `nombre_grupo`) VALUES (2,'3°MI');

INSERT INTO `marca` (`id_marca`, `nombre_marca`) VALUES (1,'Dell');
INSERT INTO `marca` (`id_marca`, `nombre_marca`) VALUES (3,'Kolke');
INSERT INTO `marca` (`id_marca`, `nombre_marca`) VALUES (2,'Samsung');

INSERT INTO `planilla` (`id_planilla`, `id_docente`, `id_grupo`, `id_asignatura`, `id_turno`, `id_ubicacion`, `fecha`, `hora_inicio`, `hora_fin`) VALUES (1,3,1,3,3,2,'2026-08-14','19:00:00','21:30:00');
INSERT INTO `planilla` (`id_planilla`, `id_docente`, `id_grupo`, `id_asignatura`, `id_turno`, `id_ubicacion`, `fecha`, `hora_inicio`, `hora_fin`) VALUES (2,3,1,3,3,2,'2026-08-14','19:00:00','21:30:00');
INSERT INTO `planilla` (`id_planilla`, `id_docente`, `id_grupo`, `id_asignatura`, `id_turno`, `id_ubicacion`, `fecha`, `hora_inicio`, `hora_fin`) VALUES (3,1,1,1,3,1,'2026-08-22','23:00:00','23:45:00');

INSERT INTO `planilla_detalle` (`id_planilla_detalle`, `id_planilla`, `id_alumno`, `id_equipo`, `estado_registrado`, `observacion`) VALUES (1,1,1,5,'Correcto',NULL);
INSERT INTO `planilla_detalle` (`id_planilla_detalle`, `id_planilla`, `id_alumno`, `id_equipo`, `estado_registrado`, `observacion`) VALUES (2,1,2,6,'Dañado','El teclado no responde correctamente.');
INSERT INTO `planilla_detalle` (`id_planilla_detalle`, `id_planilla`, `id_alumno`, `id_equipo`, `estado_registrado`, `observacion`) VALUES (3,1,3,7,'Dañado','El monitor no enciende.');
INSERT INTO `planilla_detalle` (`id_planilla_detalle`, `id_planilla`, `id_alumno`, `id_equipo`, `estado_registrado`, `observacion`) VALUES (4,1,4,8,'Correcto',NULL);
INSERT INTO `planilla_detalle` (`id_planilla_detalle`, `id_planilla`, `id_alumno`, `id_equipo`, `estado_registrado`, `observacion`) VALUES (5,1,1,5,'Correcto',NULL);
INSERT INTO `planilla_detalle` (`id_planilla_detalle`, `id_planilla`, `id_alumno`, `id_equipo`, `estado_registrado`, `observacion`) VALUES (6,1,2,6,'Dañado','El teclado no responde correctamente.');
INSERT INTO `planilla_detalle` (`id_planilla_detalle`, `id_planilla`, `id_alumno`, `id_equipo`, `estado_registrado`, `observacion`) VALUES (7,1,3,7,'Dañado','El monitor no enciende.');
INSERT INTO `planilla_detalle` (`id_planilla_detalle`, `id_planilla`, `id_alumno`, `id_equipo`, `estado_registrado`, `observacion`) VALUES (8,1,4,8,'Correcto',NULL);
INSERT INTO `planilla_detalle` (`id_planilla_detalle`, `id_planilla`, `id_alumno`, `id_equipo`, `estado_registrado`, `observacion`) VALUES (9,3,1,1,'Faltante','Se la robo');
INSERT INTO `planilla_detalle` (`id_planilla_detalle`, `id_planilla`, `id_alumno`, `id_equipo`, `estado_registrado`, `observacion`) VALUES (10,3,NULL,2,'Correcto',NULL);
INSERT INTO `planilla_detalle` (`id_planilla_detalle`, `id_planilla`, `id_alumno`, `id_equipo`, `estado_registrado`, `observacion`) VALUES (11,3,NULL,3,'Correcto',NULL);
INSERT INTO `planilla_detalle` (`id_planilla_detalle`, `id_planilla`, `id_alumno`, `id_equipo`, `estado_registrado`, `observacion`) VALUES (12,3,NULL,4,'Correcto',NULL);
INSERT INTO `planilla_detalle` (`id_planilla_detalle`, `id_planilla`, `id_alumno`, `id_equipo`, `estado_registrado`, `observacion`) VALUES (13,3,NULL,46,'Correcto',NULL);

INSERT INTO `prestamo` (`id_prestamo`, `id_alumno`, `fecha_prestamo`, `fecha_devolucion`, `id_estado_prestamo`) VALUES (1,1,'2026-08-10',NULL,1);
INSERT INTO `prestamo` (`id_prestamo`, `id_alumno`, `fecha_prestamo`, `fecha_devolucion`, `id_estado_prestamo`) VALUES (2,3,'2026-08-10',NULL,1);

INSERT INTO `prestamo_equipo` (`id_prestamo`, `id_equipo`) VALUES (1,21);
INSERT INTO `prestamo_equipo` (`id_prestamo`, `id_equipo`) VALUES (2,22);

INSERT INTO `prioridad` (`id_prioridad`, `nombre_prioridad`) VALUES (3,'Alta');
INSERT INTO `prioridad` (`id_prioridad`, `nombre_prioridad`) VALUES (1,'Baja');
INSERT INTO `prioridad` (`id_prioridad`, `nombre_prioridad`) VALUES (2,'Media');

INSERT INTO `solicitud` (`id_solicitud`, `id_docente`, `id_estado_solicitud`, `asunto`, `descripcion`, `fecha_envio`) VALUES (1,1,5,'Solicitud de cable HDMI','Se solicita un cable HDMI para utilizar el proyector durante una clase.','2026-08-15 19:10:00');
INSERT INTO `solicitud` (`id_solicitud`, `id_docente`, `id_estado_solicitud`, `asunto`, `descripcion`, `fecha_envio`) VALUES (2,1,1,'Solicitud de cable HDMI','Se solicita un cable HDMI para utilizar el proyector durante una clase.','2026-08-15 19:10:00');
INSERT INTO `solicitud` (`id_solicitud`, `id_docente`, `id_tecnico`, `id_estado_solicitud`, `asunto`, `descripcion`, `trabajo_realizado`, `fecha_envio`, `fecha_inicio`, `fecha_fin`) VALUES (3,1,5,2,'Revisión del proyector','El proyector del salón no muestra imagen.','Se inició el diagnóstico del cableado.','2026-08-23 09:05:26','2026-08-23 09:30:00',NULL);

INSERT INTO `tecnico` (`id_usuario`, `id_turno`) VALUES (5,1);
INSERT INTO `tecnico` (`id_usuario`, `id_turno`) VALUES (6,2);
INSERT INTO `tecnico` (`id_usuario`, `id_turno`) VALUES (7,3);
INSERT INTO `tecnico` (`id_usuario`, `id_turno`) VALUES (11,3);

INSERT INTO `ticket` (`id_ticket`, `id_planilla_detalle`, `id_equipo`, `id_tecnico`, `id_prioridad`, `id_estado_ticket`, `descripcion`, `solucion`, `fecha_generado`, `fecha_inicio`, `fecha_fin`) VALUES (1,2,6,5,2,2,'El teclado de LAB2-PC02 no responde correctamente. Incidencia detectada en una planilla de laboratorio.',NULL,'2026-08-14 21:35:00','2026-08-15 08:00:00',NULL);
INSERT INTO `ticket` (`id_ticket`, `id_planilla_detalle`, `id_equipo`, `id_tecnico`, `id_prioridad`, `id_estado_ticket`, `descripcion`, `solucion`, `fecha_generado`, `fecha_inicio`, `fecha_fin`) VALUES (2,3,7,7,3,1,'El monitor de LAB2-PC03 no enciende. Incidencia detectada en una planilla de laboratorio.',NULL,'2026-08-14 21:36:00',NULL,NULL);
INSERT INTO `ticket` (`id_ticket`, `id_planilla_detalle`, `id_equipo`, `id_tecnico`, `id_prioridad`, `id_estado_ticket`, `descripcion`, `solucion`, `fecha_generado`, `fecha_inicio`, `fecha_fin`) VALUES (3,2,6,5,2,2,'El teclado de LAB2-PC02 no responde correctamente. Incidencia detectada en una planilla de laboratorio.',NULL,'2026-08-14 21:35:00','2026-08-15 08:00:00',NULL);
INSERT INTO `ticket` (`id_ticket`, `id_planilla_detalle`, `id_equipo`, `id_tecnico`, `id_prioridad`, `id_estado_ticket`, `descripcion`, `solucion`, `fecha_generado`, `fecha_inicio`, `fecha_fin`) VALUES (4,3,7,7,3,1,'El monitor de LAB2-PC03 no enciende. Incidencia detectada en una planilla de laboratorio.',NULL,'2026-08-14 21:36:00',NULL,NULL);
INSERT INTO `ticket` (`id_ticket`, `id_planilla_detalle`, `id_equipo`, `id_tecnico`, `id_prioridad`, `id_estado_ticket`, `descripcion`, `solucion`, `fecha_generado`, `fecha_inicio`, `fecha_fin`) VALUES (5,9,1,11,2,1,'Equipo faltante LAB1-PC01: Se la robo. Incidencia registrada en la planilla #3.',NULL,'2026-08-23 08:34:41',NULL,NULL);

INSERT INTO `tipo_equipo` (`id_tipo_equipo`, `nombre_tipo`) VALUES (2,'Ceibalita');
INSERT INTO `tipo_equipo` (`id_tipo_equipo`, `nombre_tipo`) VALUES (1,'PC');

INSERT INTO `turno` (`id_turno`, `nombre_turno`) VALUES (1,'Matutino');
INSERT INTO `turno` (`id_turno`, `nombre_turno`) VALUES (3,'Nocturno');
INSERT INTO `turno` (`id_turno`, `nombre_turno`) VALUES (2,'Vespertino');

INSERT INTO `ubicacion` (`id_ubicacion`, `nombre_ubicacion`) VALUES (1,'Laboratorio 1');
INSERT INTO `ubicacion` (`id_ubicacion`, `nombre_ubicacion`) VALUES (2,'Laboratorio 2');
INSERT INTO `ubicacion` (`id_ubicacion`, `nombre_ubicacion`) VALUES (3,'Laboratorio 3');
INSERT INTO `ubicacion` (`id_ubicacion`, `nombre_ubicacion`) VALUES (4,'Laboratorio 4');
INSERT INTO `ubicacion` (`id_ubicacion`, `nombre_ubicacion`) VALUES (5,'Laboratorio 5');

SET FOREIGN_KEY_CHECKS = 1;
