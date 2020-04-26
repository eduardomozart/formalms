<?php defined("IN_FORMA") or die("Direct access is forbidden");
 
define('COURSE', 0);
define('COURSE_PATH', 1); 
  
class AggregatedCertificate {

    
   
    private $db;
    private $_id_cert;
    private $_title;
    private $_description;
    private $_idAssoc;
    private $_type_assoc;

    public function __construct(){

        $this->db = DbConn::getInstance();
        $this->table_cert = $GLOBALS['prefix_lms'].'_certificate';  // TODO: remove, inserting appropriate libraries
        $this->table_cert_tags = $GLOBALS['prefix_lms'].'_certificate_tags';

        $this->table_cert_meta_association = $GLOBALS['prefix_lms'].'_aggregated_cert_metadata';
        $this->table_cert_meta_association_courses = $GLOBALS['prefix_lms'].'_aggregated_cert_course';
        $this->table_cert_meta_association_coursepath = $GLOBALS['prefix_lms'].'_aggregated_cert_coursepath';
        $this->table_assign_agg_cert = $GLOBALS['prefix_lms'].'_aggregated_cert_assign';
    
    
    
        $this->assocTypesArr = array(
            COURSE => $this->table_cert_meta_association_courses,
            COURSE_PATH =>  $this->table_cert_meta_association_coursepath
        );
    }
    
    /**
    * Get all Aggregate certificates (or meta cert)
    * 
    * @param mixed $ini  limit rows
    * @param mixed $count if the query has to return only the count of the rows
    *
    * $aggCertArr['id_certificate']         Defines the id of the cert.
    * $aggCertArr['code']                   Defines the code of the cert.
    * $aggCertArr['name']                   Defines the name of the cert.
    * $aggCertArr['description']            Defines the description of the cert.
    *  
    * @return array $aggCertArr
    *           
    */
    function getAllAggregatedCerts($ini = 0, $count = false, $filter = []) {
        //search query of certificates 
        $query_certificate = "SELECT id_certificate, code, name, description 
                            FROM ".$this->table_cert . " WHERE meta = 1";

        if (!empty($filter)) { // Generalize with a filter variable
            if (isset($filter['filter_text']))
                $query_certificate .=    " AND ( name LIKE '%".$filter['filter_text']."%'"
                                        ." OR code LIKE '%".$filter['filter_text']."%' )";
        }  
        
       if(!$count){
            $query_certificate .= " ORDER BY id_certificate"
                . " LIMIT $ini,".Get::sett('visuItem');  
       }
       
        
        $rs = sql_query($query_certificate);
        
        $k = 0;
        while($rows = sql_fetch_assoc($rs)) {
            $aggCertArr[$k]['id_certificate'] = (int) $rows['id_certificate'];
            $aggCertArr[$k]['code'] = $rows['code'];
            $aggCertArr[$k]['name'] = $rows['name'];
            $aggCertArr[$k]['description'] = $rows['description'];
            $k++;
        }
                
        return $aggCertArr;
    }
      
    /**
        * Returns all metadata of aggr. cert
        *    
        * @param mixed $id_cert Certificate id (can be found in (prefix_lms . $this->table_cert) table
        * 
        * 
        * $arr_cert['code']             Defines the code of the cert.
        * $arr_cert['name']             Defines the name of the cert. 
        * $arr_cert['base_language']    Defines the language of the cert. 
        * $arr_cert['description']      Defines the description of the cert.
        * $arr_cert['user_release']     Defines if the cert. will be released to the user
        *  
        * @return array $arr_cert 
        * 
        */
    function getMetadata($id_cert){
            
            $query =      "SELECT "
                        . "id_certificate, "
                        . "code, "
                        . "name, "
                        . "base_language, "
                        . "description, "
                        . "user_release "
                        . "FROM ".$this->table_cert
                        . " WHERE id_certificate ". ( is_array($id_cert) ? "IN (" . implode(", ", $id_cert) . ") ": " = " . $id_cert);
        
            $rs = sql_query($query);
          
            return  sql_fetch_assoc($rs); 
        }  
        
    /**
    * Returns all the metadata on associations related to a cert.
    * If i'm passing the id of the cert., i will get all associations metadata associated on the cert.
    * Instead, if i'm passing the id of the association, i will get the only object with the metadata 
    * 
    * 
    * @param mixed $id_cert
    * @param mixed $id_association
    * 
    * $associationsArr['idAssociation']
    * $associationsArr['title']
    * $associationsArr['description']
    * 
    * @return array $associationsMetadataArr 
    */
    function getAssociationsMetadata($id_cert = 0, $id_association = 0, $ini = -1) {
        
        $query = "SELECT idAssociation, title, description"
                ." FROM ".$this->table_cert_meta_association
                . ($id_cert != 0 ? " WHERE idCertificate = ".$id_cert : '')
                . ($id_association != 0 ? " WHERE idAssociation = ".$id_association : '');

        if($ini != -1) { // Setting offset for pagination 
            
             $query .= " ORDER BY idAssociation"
                     . " LIMIT $ini,".Get::sett('visuItem');  
            
        }        
                
        $rs = sql_query($query);
        
        $k = 0;
        while($rows = sql_fetch_assoc($rs)) {
            $associationsArr[$k]['idAssociation'] = (int) $rows['idAssociation'];
            $associationsArr[$k]['title'] = $rows['title'];
            $associationsArr[$k]['description'] = $rows['description'];
            $k++;
        }
       
        return $associationsArr; 
    }



    /**
     * Return all technical data about certificate
     *
     * @param mixed $id_cert
     */
    function getLayoutMetacert($id_cert) {

        $query = "SELECT "
            ."cert_structure, "
            ."orientation, "
            ."bgimage "
            ."FROM ".$this->table_cert
            ." WHERE id_certificate = ".$id_cert."";

        return sql_fetch_assoc(sql_query($query));

    }

    
    /**
    * Return an array of 1 or more certs id depending on the idAssociation passed
    * 
    * @param int | array $idAssociation
    */
    
    function getIdCertificate($idAssociation) {
        
        $q = "SELECT idCertificate "
            ." FROM ".$this->table_cert_meta_association
            ." WHERE idAssociation " . (is_array($idAssociation) ? "IN (" . implode(", ", $idAssociation) .")" : " = " . $idAssociation);
        
            $rs = $this->db->query($q);
                    
            while($row = $this->db->fetch_assoc($rs)) {
            
                $idCertsArr[] = (int) $row['idCertificate'];

            }
            
            return $idCertsArr;
            
        
    }

    /**
     * Returns an array of id/s associations (object)
     *
     * @param int $idCert
     */
    function getIdAssociations($idCert) {

        $query = "SELECT idAssociation FROM ".$this->table_cert_meta_association
            ." WHERE idCertificate = ".$idCert;

        $rs = sql_query($query);


        while($rows = sql_fetch_assoc($rs)) {

            $idsArr[] = (int) $rows['idAssociation'];
            
            
        }

        return $idsArr;

    }
    
    

    /**
     * Returns an array of id/s associations (object) with the type of the assoc.
     *
     * @param int $idCert
     */
    function getIdAssociationsWithType($idCert) {

        $query = "SELECT idAssociation FROM ".$this->table_cert_meta_association
            ." WHERE idCertificate = ".$idCert;

        $rs = sql_query($query);

        $k = 0;
        while($rows = sql_fetch_assoc($rs)) {

            $idsArr[$k]["id"] = (int) $rows['idAssociation'];
            $idsArr[$k]["type"] = $this->getTypeAssoc((int) $rows['idAssociation']);
            
            $k += 1;
        }

        return $idsArr;

    }

    /**
     * Returning all associations between idAssociation or,
     * if i pass an array of user, the ids of the link to whom the users belong
     *
     * @param array | int $id_association
     * @param mixed $type_assoc
     * @param mixed $userIdsArr optional (for filtering query with users)
     *
     * @return array $linksArr an array of 0 or more rows with the link ids
     */

    function getAssociationLink($id_association = -1, $type_assoc, $userIdsArr = [], $distinct = false ){

        switch($type_assoc) {
            case COURSE:
                $table = $this->table_cert_meta_association_courses;
                $field_link = 'idCourse';
                break;
            case COURSE_PATH:
                $table = $this->table_cert_meta_association_coursepath;
                $field_link = 'idCoursePath';
                break;
            default:
                return;
        }

        $q =      "SELECT "
                . ($distinct ? "DISTINCT " : '')
                . $field_link
                . " FROM " . $table
                . " WHERE 1 = 1 " 
                . ($id_association != -1 ? " AND idAssociation ". ( is_array($id_association) ? " IN (" . implode(", ", $id_association) . ")" : " = " . $id_association) : "") 
                . (!empty($userIdsArr) ? " AND idUser ". ( is_array($userIdsArr) ? " IN (" . implode( ", ", $userIdsArr) . ") " : " = " . $userIdsArr) : "");

        $rs = sql_query($q);

        $assocArr = array();


        while($row = sql_fetch_array($rs))
            $assocArr[] = (int) $row[$field_link];


        return $assocArr;
    }

    /**
    * Returns in each type assoc all the assoc. ids that belongs to user
    * 
    * @param int
    * 
    * @return array $idsAssocArr[$association_type] = $idAssociation
    */
    function getIdsAssociationUser($user) {
        
        if(!isset($user)) return;

        $idsAssocArr = array();

        foreach($this->assocTypesArr as $association_type => $table_name){
            
            $q =      "SELECT DISTINCT idAssociation"
                    . " FROM " . $table_name
                    . " WHERE 1 = 1 "
                    . " AND idUser = ".$user;
                            
                    $rs = $this->db->query($q);
                    
            while($row = $this->db->fetch_assoc($rs)){
             
                $idsAssocArr[$association_type][] = (int) $row['idAssociation'];
            
            }
        }   

        return $idsAssocArr; 
              
    }
    
    function getIdsAssociationCertUser($user) {
        
        if(!isset($user)) return;

        // Will contain id cert -> id assocs. -> idCourse
        $idsAssocArr = array();

        foreach($this->assocTypesArr as $association_type => $table_name){
            
            $q =      "SELECT DISTINCT idAssociation"
                    . " FROM ". $table_name
                    . " WHERE 1 = 1 "
                    . " AND idUser = ".$user;
                            
                    $rs = $this->db->query($q);
                    
            while($row = $this->db->fetch_assoc($rs)){
             
                $idsAssocArr[$association_type][] = (int) $row['idAssociation'];
            
            }
        }   

        return $idsAssocArr; 
              
        
    }
    
    /**
     * @param array | int $id_assoc
     * @param $type_assoc
     *
     * @return array|void
     */
    function getAllUsersFromIdAssoc($id_assoc, $type_assoc) {

        switch($type_assoc) {
            case COURSE:
                $table = $this->table_cert_meta_association_courses;
                break;
            case COURSE_PATH:
                $table = $this->table_cert_meta_association_coursepath;
                break;
            default:
                return;
        }

        $q =    "SELECT "
            .   "DISTINCT idUser" // If i'm passing array of id user and array of links, i want to check if there are any assoc
            .   " FROM ".$table
            .   " WHERE idAssociation ". ( is_array($id_assoc) ? " IN (" . implode(", ", $id_assoc) . ")" : " = " . $id_assoc) 
            .   " AND idUser != 0 "; // skip course / path placeholder 


        $rs = sql_query($q);

        while($rows = sql_fetch_assoc($rs)) {
            $id_users[] = (int) $rows['idUser'];
        }
        return $id_users;

    }
    


    function getAllLinksFromIdAssoc($id_assoc, $type_assoc) {

        switch($type_assoc) {
            case COURSE:
                $table = $this->table_cert_meta_association_courses;
                $field_link = 'idCourse';
                break;
            case COURSE_PATH:
                $table = $this->table_cert_meta_association_coursepath;
                $field_link = 'idCoursePath';
                break;
            default:
                return;
        }


        $q =    "SELECT "
            .   "{$field_link}" // If i'm passing array of id user and array of links, i want to check if there are any assoc
            .   " FROM ".$table
            .   " WHERE idAssociation = ".$id_assoc
            .   " AND idUser = 0";


        $rs = sql_query($q);

        while($rows = sql_fetch_assoc($rs)) {
            $idLinkField[] = (int) $rows[$field_link];
        }
        return $idLinkField;

    }


    /**
     * Getting all ids of the course from a metacertificate
     * Func. called by 'associationCourse' op.
     *
     * @param mixed $id_association
     */
    public function getIdsCourse($id_association){

        $q = "SELECT idCourse FROM "
            .$this->table_cert_meta_association_courses
            . " WHERE idAssociation = " . $id_association
            . " and idUser = 0";

        $rs = sql_query($q);

        $idsCourseArr = array();
        while($row = sql_fetch_array($rs)){

            $idsCourseArr[] = (int) $row['idCourse'];

        }

        return $idsCourseArr;
    }


    function getUserAndCourseFromIdAssoc($idAssoc, $type_assoc){

        switch($type_assoc) {
            case COURSE:
                $table = $this->table_cert_meta_association_courses;
                $field = 'idCourse';
                break;
            case COURSE_PATH:
                $table = $this->table_cert_meta_association_coursepath;
                $field = 'idCoursePath';
                break;
            default:
                return;
        }
        if (!is_array($idAssoc)) {
            $query =    "SELECT idUser, {$field}"
                ." FROM ".$table
                ." WHERE idAssociation = '".$idAssoc."' and idUser <> 0 order by idCourse";
        } else {
            $idAssoc_str = implode(',',$idAssoc);
            $query =    "SELECT idUser, {$field}"
                ." FROM ".$table
                ." WHERE idAssociation in (".$idAssoc_str.") and idUser <> 0 order by idCourse";
        }                        

        $rs = sql_query($query);

        while($rows = sql_fetch_assoc($rs)) {
            $status[$rows['idUser']][] = $rows[$field];
        }
        return $status;
    }

    function getCountCoursesCompleted($coursesIdsArr, $id_user){

        $query =    "SELECT COUNT(*)"
            ." FROM %lms_courseuser"
            ." WHERE idCourse " . ( is_array($coursesIdsArr) ? " IN (" . implode(", ", $coursesIdsArr) . ")" : " = " . $coursesIdsArr) 
            ." AND idUser = ".$id_user
            ." AND status = "._CUS_END;

        $rs = sql_query($query);

        while($rows = sql_fetch_row($rs)) {

            $count = $rows[0];

        }

        return $count;
    }

    function hasUserAggCertsReleased($id_user, $id_cert, $id_association){

            $q = "SELECT * "
                ." FROM ".$this->table_assign_agg_cert
                ." WHERE idUser = ".$id_user
                ." AND idCertificate = ".$id_cert
                ." AND idAssociation = ".$id_association;

           return sql_num_rows(sql_query($q));


        }
        
    /**
    * 
    *     $filter['id_certificate'] int
    *     $filter['id_course'] int
    *     $filter['id_user'] int
    * 
    * @param array $filter
    */
    function getAssignedAggCerts($filter) {
       
        /* Steps:
        
            query for all type assoc.
                type course
                if is set filter user, then get all courses associated to the user
                type course_path
            then query if the user has some course path associated, then get if the user has completed all the courses in the coursepath
        
        
        */
        
        
        $query = " SELECT   cma.idCertificate AS id_certificate "
                ."         ,cma.idMetaCertificate AS id_meta "
                ."         ,cmc.idUser AS id_user "
                ."         ,ce.code AS cert_code "
                ."         ,ce.name AS cert_name "
                ."         ,cma.on_date"
                ." GROUP_CONCAT(DISTINCT CONCAT( '(', co.code, ') - ', co.name) SEPARATOR '<br>') AS courses"
                ." FROM %lms_certificate_meta_course as cmc"  //??
                ." JOIN " . $this->table_assign_agg_cert . " as cma ON cmc.idAssociation = cma.idMetaCertificate"
                ." JOIN %lms_certificate AS ce ON cma.idCertificate = ce.id_certificate"
                ." JOIN %lms_course AS co ON cmc.idCourse = co.idCourse"
                ." WHERE 1 = 1";
        
        if (isset($filter['id_certificate'])) {
            $query .= " AND cma.idCertificate = " . $filter['id_certificate'];
        }
        if (isset($filter['id_course'])) {
            $query .= " AND cmc.idCourse = " . $filter['id_course'];
        }
        if (isset($filter['id_user'])) {
            $query .= " AND cma.idUser = " . $filter['id_user'];
        }
        if (!isset($filter['id_user'])) {
            if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
                require_once(_base_ . '/lib/lib.preference.php');
                $adminManager = new AdminPreference();
                $query .= " AND " . $adminManager->getAdminUsersQuery(Docebo::user()->getIdSt(), 'idUser');
            }
        }
        $query .= " GROUP BY cmc.idMetaCertificate";

        $res = sql_query($query);
        
        while ($row = sql_fetch_assoc($res)) {
            $metaAssigned[] = $row;
        }
        
        return $metaAssigned;
    }
    
    

    /**
     *
     *
     */
    function getIdsCoursePath($id_association){

        $q = "SELECT idCoursePath FROM "
            .$this->table_cert_meta_association_coursepath
            . " WHERE idAssociation = " . $id_association
            . " and idUser = 0";

        $rs = sql_query($q);

        $idsCoursePathArr = array();
        while($row = sql_fetch_array($rs)){

            $idsCoursePathArr[] = (int) $row['idCoursePath'];

        }

        return $idsCoursePathArr;
    }


    function getCoursePathList() {

        $q = " SELECT id_path, path_name, path_descr
             FROM %lms_coursepath ";
        $rs = sql_query($q);

        $coursepathArr['data'] = [];
        $i = 0;
        while($rows = sql_fetch_assoc($rs)) {

            $coursepathArr['data'][$i]["idCoursePath"]    = $rows["id_path"];
            $coursepathArr['data'][$i]["nameCoursePath"]  = $rows["path_name"];
            $coursepathArr['data'][$i]["descriptionCoursePath"]  = $rows["path_descr"];
            $i += 1;
        }
        $coursepathArr["recordsTotal"] = count($coursepathArr['data']);
        $coursepathArr["recordsFiltered"] = count($coursepathArr['data']);
        $coursepathArr["draw"] = 1;


        return $coursepathArr;
    }


    // TODO: Generalize in lib.course.php
    function getCoursesArrFromId($idsArr) {

        $idsStr = implode(",", $idsArr);
      
        $q = "SELECT  %lms_course.code, %lms_course.name, if(%lms_category.path IS NULL, '/root/', path) as path
              from %lms_course 
              LEFT JOIN  %lms_category on %lms_course.idCategory = %lms_category.idCategory
              WHERE %lms_course.idCourse IN(".$idsStr.")";
        $i = 0;
        $rs = sql_query($q);
        while($rows = sql_fetch_assoc($rs)) {
            $coursesList[$i]["codeCourse"]  = $rows["code"];
            $coursesList[$i]["nameCourse"]  = $rows["name"];
            $coursesList[$i]["pathCourse"]  = substr($rows["path"], 6); // deleting '/root/' string part
            $i++;
        }

        return $coursesList;

    }

    function getAggregatedCertFileName($idUser, $idCertificate){

        $query = "SELECT cert_file"
                ." FROM ".$this->table_assign_agg_cert
                ." WHERE idUser = ".$idUser
                ." AND idCertificate = ".$idCertificate;

        return sql_fetch_row(sql_query($query));

    }

    function getCourseListFromIdCat($idCategoryArr) {

        // Need to know if i'm requesting courses from the root category (idParent 0)
        $req_root = ($idCategoryArr == "0");

        $q = "SELECT cour.name, cour.idCourse, cour.code, cour.description, cour.status "
            . (($req_root) ? '' : ", cat.path ")
            . "FROM %lms_course AS cour "
            . (($req_root) ? '' : "JOIN %lms_category AS cat ")
            . "WHERE cour.idCategory IN (" . str_replace(array( "[", "]" ) , "" , $idCategoryArr) . ")"
            . (($req_root) ? '' : " AND cour.idCategory = cat.idCategory ");


        $rs = sql_query($q);

        $coursesList = array();

        $i = 0;
        while($rows = sql_fetch_assoc($rs)) {

            $coursesList[$i]["idCourse"]    = $rows["idCourse"];
            $coursesList[$i]["codeCourse"]  = $rows["code"];
            $coursesList[$i]["nameCourse"]  = $rows["name"];
            $coursesList[$i]["pathCourse"]  = ($req_root ? Lang::t('_ALT_ROOT') : substr($rows["path"], 6)); // deleting '/root/' string part

            switch ($rows["status"]) {  // SOSTITUIRE ASSOLUTAMENTE CON qualche
                // riferimento al db o con un unico entry point
                case 0:
                    $coursesList[$i]["stateCourse"] = Lang::t("_CST_PREPARATION","course");
                    break;
                case 1:
                    $coursesList[$i]["stateCourse"] = Lang::t("_CST_AVAILABLE","course");
                    break;
                case 2:
                    $coursesList[$i]["stateCourse"] = Lang::t("_CST_CONFIRMED","course");
                    break;
                case 3:
                    $coursesList[$i]["stateCourse"] = Lang::t("_CST_CONCLUDED","course");
                    break;
                case 4:
                    $coursesList[$i]["stateCourse"] = Lang::t("_CST_CANCELLED","course");
                    break;
            };
            $i += 1;
        }

        return $coursesList;

    }

    /**
     *  Return type of association (if the assoc. is btw courses, coursepath...
     *
     * @param $id_assoc
     *
     * @return int|string
     */
    function getTypeAssoc($id_assoc){

        $type_assoc = -1; // Assoc. not found.

        foreach($this->assocTypesArr as $key => $table) {

            $q = "SELECT * 
                FROM ".$table ."
                WHERE idAssociation = ".$id_assoc. " AND idUser = 0";

            if(sql_num_rows(sql_query($q)))
                $type_assoc = $key;

        }

        return $type_assoc;

    }


    function getCertificateTags(){

        $query = "SELECT file_name, class_name FROM ".$this->table_cert_tags;

        $rs = sql_query($query);

        while($rows = sql_fetch_assoc($rs)) {
            $certificate_tags[] = $rows;
        }
        return $certificate_tags;
    }



    function getLastInsertedIdCertificate() {

        return sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()FROM ".$this->table_cert ))[0];

    }
    
    function getLastInsertedIdAggregatedCert() {

        return sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()FROM ".$this->table_cert_meta_association ))[0];

    }

    function getLastInsertedAssociationId() {

        return sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()FROM ".$this->table_cert_meta_association ))[0];

    }


    
    // ------------------------ Inserting queries ------------------------------
    
    
    /**
    * Inserting a new aggregate certificate
    *  
    * 
    * $metaDataArr['id_certificate']
    * $metaDataArr['code']
    * $metaDataArr['name']
    * $metaDataArr['base_language']
    * $metaDataArr['description']
    * $metaDataArr['meta']
    * $metaDataArr['user_release']
    * 
    * @param mixed $metaDataArr
    */
    function insertMetaDataCert($metaDataArr) {
    
        $fields = array();
        $values = array();
        
        if(empty($metaDataArr)) return false; // You never know...
                   
        if(isset($metaDataArr['code'])){
           
           $fields[] = 'code'; 
           $values[] =  "'" . $metaDataArr['code'] . "'" ;

        }
        
        if(isset($metaDataArr['name'])){
        
            $fields[] = 'name';             
            $values[] =  "'" . $metaDataArr['name'] . "'" ;

        }
        
        if(isset($metaDataArr['base_language'])){
            
            $fields[] = 'base_language';
            $values[] =  "'" . $metaDataArr['base_language'] .  "'";

        }
                         
        
        if(isset($metaDataArr['descr'])){
            
            $fields[] = 'description';
            $values[] = "'" . $metaDataArr['descr'] . "'";
            
        }
                         
        
        if(isset($metaDataArr['meta'])){
            
            $fields[] = 'meta';            
            $values[] = $metaDataArr['meta'];             
            
        }
            
                     
        if(isset($metaDataArr['user_release'])){
            
            $fields[] = 'user_release'; 
            $values[] = $metaDataArr['user_release'];             

        }
          
        if(isset($metaDataArr['id_certificate'])){
           
           $fields[] = 'id_certificate'; 
           $values[] = $metaDataArr['id_certificate'];
           
            
        }

        
       $query = "INSERT INTO "
                .$this->table_cert 
                ." ("
                .implode(", ", $fields)
                ." )"
                ." VALUES ("
                .implode(", ", $values)
                . ")";
       
       if(isset($metaDataArr['id_certificate'])){
           $query .= " ON DUPLICATE KEY UPDATE ";
          
           foreach($fields as $key => $field){
                $query .= $field . " = " . $values[$key] . ',';
           
           }

           $query = substr($query, 0, -1); //Removing last comma
     
       }

       $rs = sql_query($query);
       return $rs;

    }
     
    /**
    *  updating an existent association
    * (Table learning_certificate_meta_association)
    * 
    * $metaDataAssocArr['id_certificate']
    * $metaDataAssocArr['idAssociation']
    * $metaDataAssocArr['title']
    * $metaDataAssocArr['description']
    * @param mixed $metaDataArr
    */
    function updateMetaDataAssoc($metaDataAssocArr) {
        
        if(empty($metaDataAssocArr)) return false; // You never know...
        

        $query = "UPDATE  ".$this->table_cert_meta_association 
                ." SET description = '".$metaDataAssocArr['description']."', title ='". $metaDataAssocArr['title'] 
                ."' WHERE idAssociation = ".$metaDataAssocArr['idAssociation']. " AND"
                . " idCertificate = ".$metaDataAssocArr['idCertificate'];
        
        $rs = sql_query($query);
        return $rs;

    } 
    

     
    function saveCertAggregatedCert($assocArr) {
        

        $this->_id_cert = Get::req('id_certificate', DOTY_INT);
        $this->_title = addslashes(Get::req('title', DOTY_STRING));
        $this->_description = addslashes( Get::req('description', DOTY_STRING));
        $this->_idAssoc = intval(Get::req('id_assoc', DOTY_INT));
        $this->_type_assoc = Get::req('type_assoc', DOTY_INT, -1);
        
        if ($this->_type_assoc == COURSE) {
            $table = $this->table_cert_meta_association_courses;
        } else {
            $table = $this->table_cert_meta_association_coursepath;            
        }
        if ($this->_idAssoc == 0 ) {
            $sql1 = "INSERT INTO ".$this->table_cert_meta_association." (idCertificate, title, description) VALUES ("
                    .$this->_id_cert.',\''.$this->_title.'\',\''.$this->_description.'\')';
            if (sql_query($sql1)) {
                $sql2 = "SELECT LAST_INSERT_ID() as idAssociation";                 
                $row = sql_fetch_assoc(sql_query($sql2));
                $this->_idAssoc = $row['idAssociation'];
                if ($this->_type_assoc == COURSE) {
                    $sql1 = "INSERT INTO ".$table." (idAssociation, idUser, idCourse, idCourseEdition) VALUES ";
                    // insert placeholder for no user selected case
                    $array_course = explode(",", Get::req('selected_courses', DOTY_NUMLIST));
                    $r = Array();
                    foreach ($array_course as $the_course) {
                        $r[] = '('.$this->_idAssoc.',0,'.$the_course.', 0)';
                    }
                } else {
                    $sql1 = "INSERT INTO ".$table." (idAssociation, idUser, idCoursePath) VALUES ";
                    // insert placeholder for no user selected case
                    $array_path = explode(",", Get::req('selected_idsCoursePath', DOTY_NUMLIST));
                    $r = Array();
                    foreach ($array_path as $the_path) {
                        $r[] = '('.$this->_idAssoc.',0,'.$the_path.')';
                    }
                }
                $sql1 .= implode(',',$r);
                if (!sql_query($sql1)) 
                    return false;
            } else {
               return false ;
            }
        } else { // update, deleting old association 
            $sql0 = "UPDATE ".$this->table_cert_meta_association." SET "
                    ." title = '".$this->_title."', "
                    ." description ='".$this->_description."'"
                    ." WHERE idAssociation = ".$this->_idAssoc;
            $sql1 = "DELETE FROM ".$table." WHERE idAssociation =".$this->_idAssoc;
            if($this->_type_assoc == COURSE){
                $sql2 = "INSERT INTO ".$table." (idAssociation, idUser, idCourse, idCourseEdition) VALUES ";
                // insert placeholder for no user selected case
                $array_course = explode(",", Get::req('selected_courses', DOTY_NUMLIST));
                $r = Array();
                foreach ($array_course as $the_course) {
                    $r[] = '('.$this->_idAssoc.',0,'.$the_course.', 0)';
                }
            } else {
                $sql2 = "INSERT INTO ".$table." (idAssociation, idUser, idCoursePath) VALUES ";
                // insert placeholder for no user selected case
                $array_path = explode(",", Get::req('selected_idsCoursePath', DOTY_NUMLIST));
                $r = Array();
                foreach ($array_path as $the_path) {
                    $r[] = '('.$this->_idAssoc.',0,'.$the_path.')';
                }
            }

            $sql2 .= implode(',',$r);
            sql_query('START TRANSACTION');
            if (sql_query($sql0) && sql_query($sql1) && sql_query($sql2)){
                sql_query('COMMIT');
            } else {
                sql_query('ROLLBACK');
                return false;
            }
        }
        
        if (count($assocArr) > 0) {
            if ($this->_type_assoc == COURSE) {
                return $this->saveCertRowCourse($assocArr);
            } else {
                return $this->saveCertRowPath($assocArr);
            }
        }
        return true;
          
    }
     
     
      
    private function saveCertRowPath($assocArr){
        $table = $this->table_cert_meta_association_coursepath;            
        $sql1 = "INSERT INTO " .$table
                . " ( idAssociation, idUser, idCoursePath)"
                . " VALUES ";


        foreach ($assocArr as $pathId => $idUsers) {
            foreach ($idUsers as $id => $assoc) {
                if ($assoc) {
                    $q[] = '('.$this->_idAssoc. ','.$id.','.$pathId.')';
                } else {
                    $q[] = '('.$this->_idAssoc. ','.$id.',0)';
                }                        
            }
        }    
        $sql1 .= implode(',', $q);
        sql_query('START TRANSACTION');
        if (sql_query($sql1)){
            sql_query('COMMIT');
            return true;
        } else {
            sql_query('ROLLBACK');
            return false;
        }
    }
    
    
    /**
    * Inserting association
    * 
    * @param mixed $type_assoc
    * @param array $assocArr
    * 
    * assocArr[]
    * @return reouce_id
    */
    private function saveCertRowCourse($assocArr){
    
        $table = $this->table_cert_meta_association_courses;
        $sql1 = "INSERT INTO " . $table
                . " ( idAssociation, idUser, idCourse, idCourseEdition )"
                . " VALUES ";

        /**
        * TODO: course edition management
        */        
        foreach ($assocArr as $courseId => $idUsers) {
            foreach ($idUsers as $id => $assoc) {
                if ($assoc) {
                    $q[] = '('.$this->_idAssoc. ','.$id.','.$courseId.',0)';
                } else {
                    $q[] = '('.$this->_idAssoc. ','.$id.',0,0)';
                }                        
            }
        }    
        $sql1 .= implode(',', $q);
        sql_query('START TRANSACTION');
        if (sql_query($sql1)){
            sql_query('COMMIT');
            return true;
        } else {
            sql_query('ROLLBACK');
            return false;
        }

    }
    


    // ---------------- Deleting queries 
    
    
    
    /**
    *  Delete certificate from table_cert
    * 
    * @param mixed $idCert
    */
    function deleteCert($idCert) {
        
        $query = " DELETE FROM ".$this->table_cert
                 ." WHERE id_certificate = ".$idCert;

        return sql_query($query);
        
    }

    function deleteReleasedCert($id_user, $id_cert){
        $query =    "DELETE FROM ". $this->table_assign_agg_cert
            ." WHERE idUser = ".$id_user
            ." AND idCertificate = ".$id_cert;

        return sql_query($query);
    }

    /**
     * @param $idsArr can be single int or array of int ids
     *
     * @return reouce_id
     */
    function deleteAssociationsMetadata($idsArr){

        $query = "DELETE FROM ".$this->table_cert_meta_association
            ." WHERE idAssociation "
            . ( is_array($idsArr) ? " IN (" . implode( ", ", $idsArr) . ") " : " = " . $idsArr);


        return sql_query($query);

    }

    /**
     * Deleting associations from cert_table_meta_assoc_course or coursepath
     *
     * @param array of integer | int $idsArr
     * @param $idLinksArr
     *
     * @return reouce_id
     */
    function deleteAssociations($idsArr, $type_assoc = -1, $idLinksArr = []) {

       $rs = $this->deleteAssociationsMetadata($idsArr);
        
       if($rs) {

           if($type_assoc != -1){

               $rs = $this->deleteAssociationLinks($idsArr, $type_assoc);

           } else {

           }
           foreach ($idsArr as $id_assoc) {

               $type_association = $this->getTypeAssoc($id_assoc);

                if($type_association != -1) // Exists at least one link assoc.
                    $rs = $this->deleteAssociationLinks($id_assoc, $type_association, $idLinksArr);

           }

        }

        return $rs;
    }

    /**
     * @param       $id_association (mandatory)
     * @param       $type_assoc     (mandatory)
     * @param array $usersIdsArr    If i want to delete passing an array of user ids, then i need to pass this param
     * @param array $LinkIdsArr     Instead if i want to delete by the association (courses, coursepath...) ...
     *
     * @return      result of the delete query
     */
    function deleteAssociationLinks($id_association, $type_assoc, $userIdsArr = [],  $linkIdsArr = []) {

        switch($type_assoc) {
            case COURSE:
                $table = $this->table_cert_meta_association_courses;
                $field_link = "idCourse";
                break;
            case COURSE_PATH:
                $table = $this->table_cert_meta_association_coursepath;
                $field_link = "idCoursePath";
                break;
            default:
                return;
        }

        $query2 =   "DELETE FROM ".$table
                    . " WHERE idAssociation = " . $id_association
                    . ( !empty($userIdsArr) ? " AND idUser IN (" . implode(", ", $userIdsArr) . ")" : '')
                    . ( !empty($linkIdsArr) ? " AND " .$field_link." IN (" . implode(", ", $linkIdsArr) . ")" : '');


        $rs = sql_query($query2);
        return $rs;

    }

  
    
    // ------------ Updating queries
    
    function updateLayout($templateArr) {
        

        $query =    "UPDATE ".$this->table_cert
                    ." SET ";
                    foreach($templateArr as $column_header => $value){

                            $query .= $column_header . " = " . $value .",";

                    }

        $query = substr($query, 0, -1); //Removing last comma


        $query .=  " WHERE id_certificate = " . $templateArr['id_certificate'];            
       

        $rs = sql_query($query);
        return $rs;

    }
    
    function releaseNewCertificate($id_user, $id_certificate, $id_association){
        $query = "INSERT INTO ".$this->table_assign_agg_cert
        ." ( idUser, idCertificate, idAssociation ) "
        ." VALUES "
        ." ( "
            .intval($id_user). ","
            .intval($id_certificate).", "
            .intval($id_association)
        . ") ";
        $rs = sql_query($query);
        return $rs;

    }
    
    function isCertIssued($user, $cert, $association){
        $q = "SELECT COUNT(*) FROM ".$this->table_assign_agg_cert
              ." WHERE idUSer=".$user
              ." AND idCertificate=".$cert
              ." AND idAssociation=".$association;
        $row = sql_fetch_row(sql_query($q));
        return $row[0] > 0;
    }
    
    
        /**
    * given an id_cours and a username, returns all the association and certificates that
    * containing both user and course - used to check if a assign has been completed
    * input: userid, id course
    * output array[id_association] => courses associated
    */
    function getIdAssocFromUserCourse($id_user, $id_course){
        if ($id_user == null || $id_course  == null) return 0;
        $id_associations_courses = array();
        
        $q = "select assoc_meta.idCertificate, assoc_course.idAssociation, assoc_course.idCourse  from "
        .$this->table_cert_meta_association_courses." as assoc_course,".$this->table_cert_meta_association. " as assoc_meta "
        ." where assoc_course.idUser = ".intval($id_user)." and assoc_course.idCourse <> 0 and assoc_course.idAssociation in 
        (select idAssociation from ".$this->table_cert_meta_association_courses." where idUser =".intval($id_user)." and  idCourse = ".intval($id_course).")"
        ." and assoc_course.idAssociation = assoc_meta.idAssociation" ;
        $rs = sql_query($q);
        while ($row = sql_fetch_assoc($rs)) {
            $id_associations_courses[$row['idCertificate']][$row['idAssociation']][] = $row['idCourse']; 
        }

        return $id_associations_courses;
        
    }
    
    function getIssuedCertificates($certificate) {
        $q = "SELECT ".$this->table_assign_agg_cert.".idCertificate," 
             .$this->table_assign_agg_cert.".idAssociation, (CASE WHEN cert_file = '' then false else true END) as released,
                firstname, lastname, TRIM(LEADING '/' FROM userid ) as userid, title FROM  %adm_user,".$this->table_assign_agg_cert.",".$this->table_cert_meta_association
             ." WHERE ".$this->table_assign_agg_cert.".idCertificate=".intval($certificate)  
             . " AND idst = idUser"
             . " AND ".$this->table_assign_agg_cert.".idAssociation = ".$this->table_cert_meta_association.".idAssociation";
        $rs = sql_query($q);
        while($r =sql_fetch_assoc($rs))     
                $row[] = $r;
        return $row;
             
        
    }

   
  


}

