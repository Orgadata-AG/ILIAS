<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Portfolio Data set class
 *
 * Only for portfolio templates!
 *
 * This class implements the following entities:
 * - portfolio: object data (usr_portfolio)
 * - portfolio_page: data from table usr_portfolio_page
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPortfolioDataSet extends ilDataSet
{
    protected $current_portfolio;
    
    /**
     * Get supported versions
     */
    public function getSupportedVersions() : array
    {
        return array("4.4.0", "5.0.0");
    }
    
    /**
     * Get xml namespace
     */
    public function getXmlNamespace(string $a_entity, string $a_schema_version) : string
    {
        return "http://www.ilias.de/xml/Modules/Portfolio/" . $a_entity;
    }
    
    /**
     * Get field types for entity
     */
    protected function getTypes(string $a_entity, string $a_version) : array
    {
        if ($a_entity == "prtt") {
            switch ($a_version) {
                case "4.4.0":
                case "5.0.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "Comments" => "integer",
                        "BgColor" => "text",
                        "FontColor" => "text",
                        "Img" => "text",
                        "Ppic" => "integer",
                        "Dir" => "directory"
                        );
            }
        }
        
        if ($a_entity == "portfolio_page") {
            switch ($a_version) {
                case "4.4.0":
                case "5.0.0":
                    return array(
                        "Id" => "integer",
                        "PortfolioId" => "integer",
                        "Title" => "integer",
                        "OrderNr" => "integer",
                        "Type" => "text"
                    );
            }
        }
    }

    /**
     * Read data
     * @param
     * @return void
     */
    public function readData(string $a_entity, string $a_version, array $a_ids) : void
    {
        $ilDB = $this->db;

        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }
        
        if ($a_entity == "prtt") {
            switch ($a_version) {
                case "4.4.0":
                    $this->getDirectDataFromQuery("SELECT prtf.id,od.title,od.description," .
                        "prtf.comments,prtf.bg_color,prtf.font_color,prtf.img,prtf.ppic" .
                        " FROM usr_portfolio prtf" .
                        " JOIN object_data od ON (od.obj_id = prtf.id)" .
                        " WHERE " . $ilDB->in("prtf.id", $a_ids, false, "integer") .
                        " AND od.type = " . $ilDB->quote("prtt", "text"));
                    break;
                
                case "5.0.0":
                    $this->getDirectDataFromQuery("SELECT prtf.id,od.title,od.description," .
                        "prtf.bg_color,prtf.font_color,prtf.img,prtf.ppic" .
                        " FROM usr_portfolio prtf" .
                        " JOIN object_data od ON (od.obj_id = prtf.id)" .
                        " WHERE " . $ilDB->in("prtf.id", $a_ids, false, "integer") .
                        " AND od.type = " . $ilDB->quote("prtt", "text"));
                    break;
            }
        }
        
        if ($a_entity == "portfolio_page") {
            switch ($a_version) {
                case "4.4.0":
                case "5.0.0":
                    $this->getDirectDataFromQuery("SELECT id,portfolio_id,title,order_nr,type" .
                        " FROM usr_portfolio_page" .
                        " WHERE " . $ilDB->in("portfolio_id", $a_ids, false, "integer"));
                    break;
            }
        }
    }
    
    /**
     * Determine the dependent sets of data
     */
    protected function getDependencies(
        string $a_entity,
        string $a_version,
        ?array $a_rec = null,
        ?array $a_ids = null
    ) : array {
        switch ($a_entity) {
            case "prtt":
                return array(
                    "portfolio_page" => array("ids" => $a_rec["Id"])
                );
        }
        return [];
    }

    /**
     * Get xml record
     * @param
     * @return array
     */
    public function getXmlRecord(string $a_entity, string $a_version, array $a_set) : array
    {
        if ($a_entity == "prtt") {
            $dir = ilObjPortfolioTemplate::initStorage($a_set["Id"]);
            $a_set["Dir"] = $dir;
            
            $a_set["Comments"] = ilNote::commentsActivated($a_set["Id"], 0, "prtt");
        }

        return $a_set;
    }
    
    /**
     * Import record
     * @param
     * @return void
     */
    public function importRecord(string $a_entity, array $a_types, array $a_rec, ilImportMapping $a_mapping, string $a_schema_version) : void
    {
        switch ($a_entity) {
            case "prtt":

                // container copy
                if ($new_id = $a_mapping->getMapping("Services/Container", "objs", $a_rec["Id"])) {
                    $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
                } else {
                    $newObj = new ilObjPortfolioTemplate();
                    $newObj->create();
                }
                                
                $newObj->setTitle($a_rec["Title"]);
                $newObj->setDescription($a_rec["Description"]);
                $newObj->setPublicComments($a_rec["Comments"]);
                $newObj->setBackgroundColor($a_rec["BgColor"]);
                $newObj->setFontColor($a_rec["FontColor"]);
                $newObj->setProfilePicture($a_rec["Ppic"]);
                $newObj->setImage($a_rec["Img"]);
                $newObj->update();
                
                // handle image(s)
                if ($a_rec["Img"]) {
                    $dir = str_replace("..", "", $a_rec["Dir"]);
                    if ($dir != "" && $this->getImportDirectory() != "") {
                        $source_dir = $this->getImportDirectory() . "/" . $dir;
                        $target_dir = ilObjPortfolioTemplate::initStorage($newObj->getId());
                        ilUtil::rCopy($source_dir, $target_dir);
                    }
                }

                $a_mapping->addMapping("Modules/Portfolio", "prtt", $a_rec["Id"], $newObj->getId());
                $a_mapping->addMapping("Services/Object", "obj", $a_rec["Id"], $newObj->getId());
                break;

            case "portfolio_page":
                $prtt_id = (int) $a_mapping->getMapping("Modules/Portfolio", "prtt", $a_rec["PortfolioId"]);
                if ($prtt_id) {
                    $newObj = new ilPortfolioTemplatePage();
                    $newObj->setPortfolioId($prtt_id);
                    $newObj->setTitle($a_rec["Title"]);
                    $newObj->setType($a_rec["Type"]);
                    $newObj->setOrderNr($a_rec["OrderNr"]);
                    $newObj->create(false, true);
                    
                    $a_mapping->addMapping("Services/COPage", "pg", "prtt:" . $a_rec["Id"], "prtt:" . $newObj->getId());
                }
                break;
        }
    }
}
