-- #FAY-44 migrate course info to mvc
UPDATE `learning_module`
SET `mvc_path` = 'lms/course/infocourse'
WHERE `learning_module`.`module_name` = "course"
  AND `learning_module`.`default_op` = "infocourse";

-- #19687 Languages - Increase text_key field lenght to 255
ALTER TABLE `core_lang_text`
    MODIFY COLUMN `text_key` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER `id_text`;



CREATE TABLE IF NOT EXISTS `dashboard_block_config`
(
    `id`           bigint(20)   NOT NULL AUTO_INCREMENT,
    `block_class`  varchar(255) NOT NULL,
    `block_config` text         NOT NULL,
    `position`     bigint(20)   NOT NULL DEFAULT '999',
    PRIMARY KEY (`id`),
    KEY `block_class_idx` (`block_class`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;


CREATE TABLE IF NOT EXISTS `dashboard_blocks`
(
    `id`          bigint(20)   NOT NULL AUTO_INCREMENT,
    `block_class` varchar(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `block_class_unique` (`block_class`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;


INSERT INTO `dashboard_blocks` (`id`, `block_class`)
VALUES (7, 'DashboardBlockCalendarLms'),
       (3, 'DashboardBlockCertificatesLms'),
       (6, 'DashboardBlockAnnouncementsLms'),
       (5, 'DashboardBlockCoursesLms'),
       (4, 'DashboardBlockMessagesLms'),
       (8, 'DashboardBlockBannerLms');


INSERT INTO learning_middlearea (`obj_index`, `disabled`, `idst_list`, `sequence`)
VALUES ('tb_dashboard', '1', 'a:0:{}', '0');


-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes)
VALUES ('_DASHBOARD', 'middlearea', '');

INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text)
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_DASHBOARD ' and text_module = ' middlearea '),
        'english', 'Dashboard');
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text)
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_DASHBOARD ' and text_module = ' middlearea '),
        'italian', 'Dashboard');

INSERT INTO `learning_module`
VALUES (47, 'dashboard', 'show', '_DASHBOARD', 'view', '', '', 'all', 'lms/dashboard/show');

SET @max = (SELECT MAX(idMenu) + 1
            FROM `core_menu`);

INSERT INTO `core_menu`(`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`)
VALUES (@max, '_DASHBOARD', '', 4, 'true', 'true', NULL, NULL, 'lms');

INSERT INTO `core_menu_under`(`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`,
                              `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`)
VALUES (@max, @max, 'course', '_DASHBOARD', NULL, 'view', 'lms', 4, NULL, NULL, 'lms/dashboard/show');

SET @max = (SELECT MAX(idMenu) + 1
            FROM `core_menu`);

INSERT INTO `core_menu`(`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`)
VALUES (@max, '_DASHBOARD_CONFIGURATION', '', 4, 'true', 'true', '5', NULL, 'framework');

INSERT INTO `core_menu_under`(`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`,
                              `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`)
VALUES (@max, @max, 'dashboardsettings', '_DASHBOARD_CONFIGURATION', '', 'view', 'framework', 1, '', '',
        'adm/dashboardsettings/show');

--
-- Aggregated certificate refactoring MVC
-- 
UPDATE core_menu_under 
SET  	default_op = '',
		class_file = '',
		class_name = '',
		mvc_path = 'alms/aggregatedcertificate/show'
WHERE module_name = 'meta_certificate';



CREATE TABLE IF NOT EXISTS `learning_aggregated_cert_metadata` (
  `idAssociation` int(11) NOT NULL AUTO_INCREMENT,
  `idCertificate` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` longtext NOT NULL,
   PRIMARY KEY (`idAssociation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `learning_aggregated_cert_metadata`
  MODIFY `idAssociation` int(11) NOT NULL AUTO_INCREMENT;
  
INSERT INTO `learning_aggregated_cert_metadata` (`idCertificate`, `title`, `description` ) 
SELECT `idCertificate`, `title`, `description` from `learning_certificate_meta`;


CREATE TABLE IF NOT EXISTS `learning_aggregated_cert_assign` (
  `idUser` int(11) NOT NULL DEFAULT 0,
  `idCertificate` int(11) NOT NULL DEFAULT 0,
  `idAssociation` int(11) NOT NULL,
  `on_date` datetime DEFAULT NULL,
  `cert_file` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`idUser`,`idCertificate`,`idAssociation`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `learning_aggregated_cert_assign` (`idUser`, `idAssociation`, `idCertificate`, `on_date`, `cert_file`  ) 
SELECT `idUser`, `idMetaCertificate`, `idCertificate`, `on_date`, `cert_file` from learning_certificate_meta`;


  
CREATE TABLE IF NOT EXISTS `learning_aggregated_cert_course` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idAssociation` int(11) NOT NULL DEFAULT 0,
  `idUser` int(11) NOT NULL DEFAULT '0',
  `idCourse` int(11) NOT NULL DEFAULT '0',
  `idCourseEdition` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idAssociation` (`idAssociation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  
INSERT INTO `learning_aggregated_cert_course` (`idAssociation`, `idUser`,  `idCourse`, `idCourseEdition`  ) 
SELECT `idMetaCertificate`, `idUser`, `idCourse`, `idCourseEdition`  from learning_meta_course`;    
  
DELETE FROM learning_aggregated_cert_course WHERE idUser = 0; 
INSERT INTO `learning_aggregated_cert_course` (idAssociation, idUser, idCourse, idCourseEdition)  
SELECT idAssociation, 0 as idUser, idCourse, idCourseEdition  FROM `learning_aggregated_cert_course`;


    

CREATE TABLE IF NOT EXISTS `learning_aggregated_cert_coursepath` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idAssociation` int(11) NOT NULL DEFAULT '0',
  `idUser` int(11) NOT NULL DEFAULT '0',
  `idCoursePath` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idAssociation` (`idAssociation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

