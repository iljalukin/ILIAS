<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* Class ilPCPlugged
* Plugged content object (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCPlugged extends ilPageContent
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilPluginAdmin
     */
    protected $plugin_admin;

    public $dom;
    public $plug_node;

    /**
    * Init page content component.
    */
    public function init()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->plugin_admin = $DIC["ilPluginAdmin"];
        $this->setType("plug");
    }

    /**
    * Set node
    */
    public function setNode($a_node)
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->plug_node = $a_node->first_child();		// this is the Plugged node
    }

    /**
    * Create plugged node in xml.
    *
    * @param	object	$a_pg_obj		Page Object
    * @param	string	$a_hier_id		Hierarchical ID
    */
    public function create(
        $a_pg_obj,
        $a_hier_id,
        $a_pc_id,
        $a_plugin_name,
        $a_plugin_version
    ) {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->plug_node = $this->dom->create_element("Plugged");
        $this->plug_node = $this->node->append_child($this->plug_node);
        $this->plug_node->set_attribute("PluginName", $a_plugin_name);
        $this->plug_node->set_attribute("PluginVersion", $a_plugin_version);
    }

    /**
    * Set properties of plugged component.
    *
    * @param	array	$a_properties		component properties
    */
    public function setProperties($a_properties)
    {
        if (!is_object($this->plug_node)) {
            return;
        }
        
        // delete properties
        $children = $this->plug_node->child_nodes();
        for ($i = 0; $i < count($children); $i++) {
            $this->plug_node->remove_child($children[$i]);
        }
        // set properties
        foreach ($a_properties as $key => $value) {
            $prop_node = $this->dom->create_element("PluggedProperty");
            $prop_node = $this->plug_node->append_child($prop_node);
            $prop_node->set_attribute("Name", $key);
            if ($value != "") {
                $prop_node->set_content($value);
            }
        }
    }

    /**
    * Get properties of plugged component
    *
    * @return	string		characteristic
    */
    public function getProperties()
    {
        $properties = array();
        
        if (is_object($this->plug_node)) {
            // delete properties
            $children = $this->plug_node->child_nodes();
            for ($i = 0; $i < count($children); $i++) {
                if ($children[$i]->node_name() == "PluggedProperty") {
                    $properties[$children[$i]->get_attribute("Name")] =
                        $children[$i]->get_content();
                }
            }
        }
        
        return $properties;
    }
    
    /**
    * Set version of plugged component
    *
    * @param	string	$a_version		version
    */
    public function setPluginVersion($a_version)
    {
        if (!empty($a_version)) {
            $this->plug_node->set_attribute("PluginVersion", $a_version);
        } else {
            if ($this->plug_node->has_attribute("PluginVersion")) {
                $this->plug_node->remove_attribute("PluginVersion");
            }
        }
    }

    /**
    * Get version of plugged component
    *
    * @return	string		version
    */
    public function getPluginVersion()
    {
        if (is_object($this->plug_node)) {
            return $this->plug_node->get_attribute("PluginVersion");
        }
    }

    /**
    * Set name of plugged component
    *
    * @param	string	$a_name		name
    */
    public function setPluginName($a_name)
    {
        if (!empty($a_name)) {
            $this->plug_node->set_attribute("PluginName", $a_name);
        } else {
            if ($this->plug_node->has_attribute("PluginName")) {
                $this->plug_node->remove_attribute("PluginName");
            }
        }
    }

    /**
    * Get name of plugged component
    *
    * @return	string		name
    */
    public function getPluginName()
    {
        if (is_object($this->plug_node)) {
            return $this->plug_node->get_attribute("PluginName");
        }
    }

    /**
     * Handle copied plugged content. This function must, e.g. create copies of
     * objects referenced within the content (e.g. question objects)
     *
     * @param ilPageObject	$a_page			the current page object
     * @param DOMDocument 	$a_domdoc 		dom document
     */
    public static function handleCopiedPluggedContent(ilPageObject $a_page, DOMDocument $a_domdoc)
    {
        global $DIC;
        $ilPluginAdmin = $DIC['ilPluginAdmin'];

        $xpath = new DOMXPath($a_domdoc);
        $nodes = $xpath->query("//Plugged");

        /** @var DOMElement $node */
        foreach ($nodes as $node) {
            $plugin_name = $node->getAttribute('PluginName');
            $plugin_version = $node->getAttribute('PluginVersion');

            if ($ilPluginAdmin->isActive(IL_COMP_SERVICE, "COPage", "pgcp", $plugin_name)) {
                /** @var ilPageComponentPlugin $plugin_obj */
                $plugin_obj = $ilPluginAdmin->getPluginObject(IL_COMP_SERVICE, "COPage", "pgcp", $plugin_name);
                $plugin_obj->setPageObj($a_page);

                $properties = array();
                /** @var DOMElement $child */
                foreach ($node->childNodes as $child) {
                    $properties[$child->getAttribute('Name')] = $child->nodeValue;
                }

                // let the plugin copy additional content
                // and allow it to modify the saved parameters
                $plugin_obj->onClone($properties, $plugin_version);

                foreach ($node->childNodes as $child) {
                    $node->removeChild($child);
                }
                foreach ($properties as $name => $value) {
                    $child = new DOMElement('PluggedProperty',
                        str_replace("&", "&amp;", $value)
                    );
                    $node->appendChild($child);
                    $child->setAttribute('Name', $name);
                }
            }
        }
    }

    /**
     * Handle deleted plugged content. This function must, e.g. delete
     * objects referenced within the content (e.g. question objects)
     *
     * @param ilPageObject	$a_page			the current page object
     * @param DOMDocument 	$a_node 		dom node
     */
    public static function handleDeletedPluggedNode(ilPageObject $a_page, DOMNode $a_node)
    {
        global $DIC;
        $ilPluginAdmin = $DIC['ilPluginAdmin'];

        $plugin_name = $a_node->getAttribute('PluginName');
        $plugin_version = $a_node->getAttribute('PluginVersion');

        if ($ilPluginAdmin->isActive(IL_COMP_SERVICE, "COPage", "pgcp", $plugin_name)) {
            /** @var ilPageComponentPlugin $plugin_obj */
            $plugin_obj = $ilPluginAdmin->getPluginObject(IL_COMP_SERVICE, "COPage", "pgcp", $plugin_name);
            $plugin_obj->setPageObj($a_page);

            $properties = array();
            /** @var DOMElement $child */
            foreach ($a_node->childNodes as $child) {
                $properties[$child->getAttribute('Name')] = $child->nodeValue;
            }

            // let the plugin delete additional content
            $plugin_obj->onDelete($properties, $plugin_version);
        }
    }


    /**
     * @inheritDoc
     */
    public function modifyPageContentPostXsl($a_html, $a_mode, $a_abstract_only = false)
    {
        $lng = $this->lng;
        $ilPluginAdmin = $this->plugin_admin;
        
        $c_pos = 0;
        $start = strpos($a_html, "{{{{{Plugged<pl");
        //echo htmlentities($a_html)."-";
        if (is_int($start)) {
            $end = strpos($a_html, "}}}}}", $start);
        }
        $i = 1;

        while ($end > 0) {
            $param = substr($a_html, $start + 5, $end - $start - 5);
            $param = str_replace(' xmlns:xhtml="http://www.w3.org/1999/xhtml"', "", $param);
            $param = explode("<pl/>", $param);
            //var_dump($param); exit;
            $plugin_name = $param[1];
            $plugin_version = $param[2];
            $properties = array();

            for ($i = 3; $i < count($param); $i += 2) {
                $properties[$param[$i]] = $param[$i + 1];
            }
            
            // get html from plugin
            if ($a_mode == "edit") {
                $plugin_html = '<div class="ilBox">' . $lng->txt("content_plugin_not_activated") . " (" . $plugin_name . ")</div>";
            }
            if ($ilPluginAdmin->isActive(IL_COMP_SERVICE, "COPage", "pgcp", $plugin_name)) {
                $plugin_obj = $ilPluginAdmin->getPluginObject(
                    IL_COMP_SERVICE,
                    "COPage",
                    "pgcp",
                    $plugin_name
                );
                $plugin_obj->setPageObj($this->getPage());
                $gui_obj = $plugin_obj->getUIClassInstance();
                $plugin_html = $gui_obj->getElementHTML($a_mode, $properties, $plugin_version);
            }
            
            $a_html = substr($a_html, 0, $start) .
                $plugin_html .
                substr($a_html, $end + 5);

            if (strlen($a_html) > $start + 5) {
                $start = strpos($a_html, "{{{{{Plugged<pl", $start + 5);
            } else {
                $start = false;
            }
            $end = 0;
            if (is_int($start)) {
                $end = strpos($a_html, "}}}}}", $start);
            }
        }
                
        return $a_html;
    }
    
    /**
     * Get javascript files
     */
    public function getJavascriptFiles($a_mode)
    {
        $ilPluginAdmin = $this->plugin_admin;
        
        $js_files = array();
        
        $pl_names = $ilPluginAdmin->getActivePluginsForSlot(
            IL_COMP_SERVICE,
            "COPage",
            "pgcp"
        );
        foreach ($pl_names as $pl_name) {
            $plugin = $ilPluginAdmin->getPluginObject(
                IL_COMP_SERVICE,
                "COPage",
                "pgcp",
                $pl_name
            );
            $plugin->setPageObj($this->getPage());
            $pl_dir = $plugin->getDirectory();
            
            $pl_js_files = $plugin->getJavascriptFiles($a_mode);
            foreach ($pl_js_files as $pl_js_file) {
                if (!is_int(strpos($pl_js_file, "//"))) {
                    $pl_js_file = $pl_dir . "/" . $pl_js_file;
                }
                if (!in_array($pl_js_file, $js_files)) {
                    $js_files[] = $pl_js_file;
                }
            }
        }
        //var_dump($js_files);
        return $js_files;
    }
    
    /**
     * Get css files
     */
    public function getCssFiles($a_mode)
    {
        $ilPluginAdmin = $this->plugin_admin;
        
        $css_files = array();
        
        $pl_names = $ilPluginAdmin->getActivePluginsForSlot(
            IL_COMP_SERVICE,
            "COPage",
            "pgcp"
        );
        foreach ($pl_names as $pl_name) {
            $plugin = $ilPluginAdmin->getPluginObject(
                IL_COMP_SERVICE,
                "COPage",
                "pgcp",
                $pl_name
            );
            $plugin->setPageObj($this->getPage());
            $pl_dir = $plugin->getDirectory();
            
            $pl_css_files = $plugin->getCssFiles($a_mode);
            foreach ($pl_css_files as $pl_css_file) {
                if (!is_int(strpos($pl_css_file, "//"))) {
                    $pl_css_file = $pl_dir . "/" . $pl_css_file;
                }
                if (!in_array($pl_css_file, $css_files)) {
                    $css_files[] = $pl_css_file;
                }
            }
        }

        return $css_files;
    }
}
