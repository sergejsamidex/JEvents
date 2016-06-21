<?php
/**
 * JEvents Component for Joomla! 3.x
 *
 * @version     $Id: edit16.php 2983 2011-11-10 14:02:23Z geraintedwards $
 * @package     JEvents
 * @copyright   Copyright (C)  2008-2016 GWE Systems Ltd
 * @license     GNU/GPLv2, see http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://www.jevents.net
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.html.html.bootstrap');
// We need to get the params first

//JHtml::_('formbehavior.chosen', '#adminForm select:not(.notchosen)');
JHtml::_('formbehavior.chosen', '#adminForm select.chosen');

use Joomla\String\StringHelper;

$version = JEventsVersion::getInstance();

$haslayouts = false;
foreach (JEV_CommonFunctions::getJEventsViewList() as $viewfile) {
    $config = JPATH_SITE . "/components/" . JEV_COM_COMPONENT . "/views/" . $viewfile . "/config.xml";
    if (file_exists($config)) {
        $haslayouts = true;
    }
}
$hasPlugins = false;
$db = JFactory::getDbo();
$query = $db->getQuery(true)
        ->select('folder AS type, element AS name, params, enabled, manifest_cache ')
        ->from('#__extensions')
        // include unpublished plugins
        //->where('enabled = 1')        
        ->where('type =' . $db->quote('plugin'))
        ->where('state IN (0,1)')
        ->where('(folder="jevents" OR element="gwejson")')
        ->order('enabled desc, ordering asc');

$jevplugins = $db->setQuery($query)->loadObjectList();
//echo $db->getQuery();
//$jevplugins = JPluginHelper::getPlugin("jevents");
if (count($jevplugins)){
    $hasPlugins = true;
}
?>
<!-- Set Difficulty : -->
<div id="jev_adminui" class="jev_adminui skin-blue sidebar-mini">
    <header class="main-header">
        <?php echo JEventsHelper::addAdminHeader(); ?>
    </header>
    <!-- =============================================== -->
    <!-- Left side column. contains the sidebar -->
    <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <?php echo JEventsHelper:: addAdminSidebar(); ?>
        <!-- /.sidebar -->
    </aside>
    <!-- =============================================== -->
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper" style="min-height: 1096px;">
        <form action="index.php" method="post" name="adminForm" autocomplete="off" id="adminForm">
            <fieldset class='jevconfig'>
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                <?php echo JText::_('JEV_EVENTS_CONFIG'); ?>
            </h1>
            <small>
                <?php
                // difficulty rating is outside the tabs!
                $fieldSets = $this->form->getFieldsets();
                foreach ($fieldSets as $name => $fieldSet) {
                    foreach ($this->form->getFieldset($name) as $field) {
                        if ($field->fieldname == "com_difficulty") {
                            ?>
                            <table class="settings_level">
                                <tr class=" difficulty1">
                                    <?php
                                    echo '<td class="paramlist_value"><span class="editlinktip">' . $field->label . '</span>' . $field->input . '</td>';
                                    ?>
                                </tr>
                            </table>
                            <?php
                        }
                    }
                }
                ?>
            </small>
        </section>
        <!-- Main content -->
        <section class="content ov_info">

            <!-- Default box -->
            <div class="box">
                <div class="box-body jev_config">

                            <?php
                            echo JHtml::_('bootstrap.startPane', 'myParamsTabs', array('active' => 'JEV_TAB_COMPONENT'));
                            $fieldSets = $this->form->getFieldsets();

                            foreach ($fieldSets as $name => $fieldSet) {
                                if ($name == "permissions") {
                                    continue;
                                }
                                $label = empty($fieldSet->label) ? $name : $fieldSet->label;
                                echo JHtml::_('bootstrap.addPanel', "myParamsTabs", $name);

                                $html = array();

                                $html[] = '<table class="paramlist admintable" >';

                                if (isset($fieldSet->description) && !empty($fieldSet->description)) {
                                    $desc = JText::_($fieldSet->description);
                                    $html[] = '<tr><td class="paramlist_description" colspan="2">' . $desc . '</td></tr>';
                                }

                                foreach ($this->form->getFieldset($name) as $field) {

                                    if ($field->fieldname == "clubcode_spacer") {

                                        echo "<tr><td colspan='2'>" . $field->input . "<br/><br/></td></tr>";

                                        $field->hidden = true;
                                    }

                                    if ($field->hidden || $field->fieldname == "com_difficulty") {
                                        continue;
                                    }

                                    $maxjoomlaversion = $this->form->getFieldAttribute($field->fieldname, "maxjoomlaversion", false);
                                    if ($maxjoomlaversion && version_compare(JVERSION, $maxjoomlaversion, ">")) {
                                        continue;
                                    }
                                    $minjoomlaversion = $this->form->getFieldAttribute($field->fieldname, "minjoomlaversion", false);
                                    if ($minjoomlaversion && version_compare(JVERSION, $minjoomlaversion, "<")) {
                                        continue;
                                    }

                                    // Hide club update field if no club addons are installed
                                    //if ($field->fieldname=="clubcode_spacer" || $field->fieldname=="clubcode"){
                                    //	// disable if no club addons are installed
                                    //	$plugins = JPluginHelper::getPlugin("jevents");
                                    //	if (count($plugins)==0 && !$haslayouts){
                                    //		continue;
                                    //	}
                                    //}

                                    $class = isset($field->class) ? $field->class : "";

                                    $difficultyClass = "difficulty" . $this->form->getFieldAttribute($field->fieldname, "difficulty");
                                    if ($this->component->params->get("com_difficulty", 1) < $this->form->getFieldAttribute($field->fieldname, "difficulty")) {
                                        $difficultyClass .= " hiddenDifficulty";
                                    }

                                    if (StringHelper::strlen($class) > 0) {
                                        $class = " class='$class $difficultyClass'";
                                    } else {
                                        $class = " class=' $difficultyClass'";
                                    }

                                    $html[] = "<tr $class>";
                                    if (!isset($field->label) || $field->label == "") {
                                        $html[] = '<td class="paramlist_key"><span class="editlinktip">' . $field->label . '</span></td>';
                                        $html[] = '<td class="paramlist_value">' . $field->input . '</td>';
                                    } else {
                                        $html[] = '<td class="paramlist_value" colspan="2">' . $field->input . '</td>';
                                    }

                                    $html[] = '</tr>';
                                }

                                if ($name == "JEV_PERMISSIONS") {
                                    $name = "permissions";
                                    foreach ($this->form->getFieldset($name) as $field) {
                                        $class = isset($field->class) ? $field->class : "";

                                        if (StringHelper::strlen($class) > 0) {
                                            $class = " class='$class'";
                                        }
                                        $html[] = "<tr $class>";
                                        $html[] = '<td class="paramlist_value" colspan="2">' . $field->input . '</td>';
					if (StringHelper::strlen($class) > 0)
					{
						$class = " class='$class'";
					}
					$html[] = "<tr $class>";
					$html[] = '<td class="paramlist_value" colspan="2">' . $field->input . '</td>';

                                        $html[] = '</tr>';
                                    }
                                }

                                $html[] = '</table>';

                                echo implode("\n", $html);
                                ?>

                                <?php
                                echo JHtml::_('bootstrap.endPanel');
                            }

                            if ($haslayouts) {

                                // Now get layout specific parameters
                                //JForm::addFormPath(JPATH_COMPONENT ."/views/");
                                foreach (JEV_CommonFunctions::getJEventsViewList() as $viewfile) {

                                    $config = JPATH_SITE . "/components/" . JEV_COM_COMPONENT . "/views/" . $viewfile . "/config.xml";
                                    if (file_exists($config)) {

                                        $layoutform = JForm::getInstance("com_jevent.config.layouts." . $viewfile, $config, array('control' => 'jform', 'load_data' => true), true, "/config");
                                        $layoutform->bind($this->component->params);

                                        if (JFile::exists(JPATH_ADMINISTRATOR . "/manifests/files/$viewfile.xml")) {
                                            $xml = simplexml_load_file(JPATH_ADMINISTRATOR . "/manifests/files/$viewfile.xml");
                                            $layoutname = (string)$xml->name;
                                            $langfile = 'files_' . str_replace('files_', '', strtolower(JFilterInput::getInstance()->clean((string)$layoutname, 'cmd')));
                                            $lang = JFactory::getLanguage();
                                            $lang->load($langfile, JPATH_SITE, null, false, true);
                                        }

                                        $fieldSets = $layoutform->getFieldsets();
                                        $html = array();
                                        $hasconfig = false;
                                        foreach ($fieldSets as $name => $fieldSet) {
                                            $html[] = '<table class="paramlist admintable" >';

                                            if (isset($fieldSet->description) && !empty($fieldSet->description)) {
                                                $desc = JText::_($fieldSet->description);
                                                $html[] = '<tr><td class="paramlist_description" colspan="2">' . $desc . '</td></tr>';
                                            }

                                            foreach ($layoutform->getFieldset($name) as $field) {
                                                if ($field->hidden) {
                                                    continue;
                                                }

                                                $maxjoomlaversion = $this->form->getFieldAttribute($field->fieldname, "maxjoomlaversion", false);
                                                if ($maxjoomlaversion && version_compare(JVERSION, $maxjoomlaversion, ">")) {
                                                    continue;
                                                }
                                                $minjoomlaversion = $this->form->getFieldAttribute($field->fieldname, "minjoomlaversion", false);
                                                if ($minjoomlaversion && version_compare(JVERSION, $minjoomlaversion, "<")) {
                                                    continue;
                                                }

                                                $hasconfig = true;
                                                $class = isset($field->class) ? $field->class : "";
	                                            
                                                if (StringHelper::strlen($class) > 0) {
                                                    $class = " class='$class'";
                                                }
                                                $html[] = "<tr $class>";
                                                if (!isset($field->label) || $field->label == "") {
                                                    $html[] = '<td class="paramlist_key"><span class="editlinktip">' . $field->label . '</span></td>';
                                                    $html[] = '<td class="paramlist_value">' . $field->input . '</td>';
                                                } else {
                                                    $html[] = '<td class="paramlist_value" colspan="2">' . $field->input . '</td>';
                                                }

                                                $html[] = '</tr>';
                                            }
                                            $html[] = '</table>';
                                        }

                                        if (!$hasconfig) {
                                            $x = 1;
                                        }
                                        if ($hasconfig) {
                                            echo JHtml::_('bootstrap.addPanel', 'myLayoutTabs', $viewfile);
                                            //echo JHtml::_('bootstrap.addPanel', 'myParamsTabs', $viewfile);

                                            echo implode("\n", $html);

                                            echo JHtml::_('bootstrap.endPanel');
                                            //echo JHtml::_('bootstrap.endPanel');
                                        }
                                    }
                                }
                            }
                            if ($hasPlugins)
                            {
                                echo JHtml::_('bootstrap.addPanel', "myParamsTabs", "plugin_options");
                                echo JHtml::_('bootstrap.startAccordion', 'myPluginAccordion', array('active' => 'collapsexx', 'parent' => 'plugin_options'));
                                $i = 0;
                                foreach ($jevplugins as $plugin)
                                {
                                    $config = JPATH_SITE . "/plugins/" . $plugin->type . "/" . $plugin->name . "/" . $plugin->name . ".xml";
                                    if (file_exists($config))
                                    {
                                        // Load language file
                                        $lang     = JFactory::getLanguage();
                                        $langfile = "plg_" . $plugin->type . "_" . $plugin->name . ".sys";
                                        $lang->load($langfile, JPATH_ADMINISTRATOR, null, false, true);
                                        $langfile = "plg_" . $plugin->type . "_" . $plugin->name;
                                        $lang->load($langfile, JPATH_ADMINISTRATOR, null, false, true);

                                        // Now get plugin specific parameters
                                        //JFactory::getApplication()->setUserState('com_plugins.edit.plugin.data', array());
                                        $pluginform = JForm::getInstance("com_jevents.config.plugins." . $plugin->name, $config, array('control' => 'jform_plugin[' . $plugin->type . '][' . $plugin->name . ']', 'load_data' => true), true, "/extension/config/fields");
                                        //$pluginform = JForm::getInstance('com_plugins.plugin', $config, array('control' => 'jform_plugin['.$plugin->name.']', 'load_data' => true), true, "/extension/config/fields");
                                        $pluginparams = new JRegistry($plugin->params);

                                        // Load the whole XML config file to get the plugin name in plain english
                                        $xml = new SimpleXMLElement($config, 0, true);
                                        // TODO Consider adding enabled/disabled method here for plugins inclusing unpublished ones!
                                        // TODO handle unpublished plugins too
                                        $label = JText::_($xml->name);

                                        $checked1 = $plugin->enabled ? 'checked="checked" ' : '';
                                        $checked0 = !$plugin->enabled ? 'checked="checked" ' : '';
                                        $label .= '<fieldset class="btn-group radio"  style="float:right;">'
                                            . '<input type="radio"  ' . $checked1 . '  value="1" name="jform_plugin[' . $plugin->type . '][' . $plugin->name . '][enabled]"  id="jform_plugin_' . $plugin->type . '_' . $plugin->name . '_params_enabled1" class="btn">'
                                            . '<label for="jform_plugin_' . $plugin->type . '_' . $plugin->name . '_params_enabled1" class="btn">'
                                            . JText::_('JENABLED')
                                            . '</label>'
                                            . '<input type="radio" ' . $checked0 . ' value="0" name="jform_plugin[' . $plugin->type . '][' . $plugin->name . '][enabled]"  id="jform_plugin_' . $plugin->type . '_' . $plugin->name . '_params_enabled0" class="btn">'
                                            . '<label for="jform_plugin_' . $plugin->type . '_' . $plugin->name . '_params_enabled0" class="btn">'
                                            . JText::_('JDISABLED')
                                            . '</label>'
                                            . '</fieldset>';

                                        $hasfields = false;
                                        $fieldSets = $pluginform->getFieldsets();
                                        foreach ($fieldSets as $name => $fieldSet)
                                        {
                                            if ($pluginform->getFieldset($name))
                                            {
                                                $hasfields = true;
                                            }
                                        }

                                        if ($hasfields)
                                        {
                                            echo JHtml::_('bootstrap.addSlide', 'myPluginAccordion', JText::_($label), 'collapse' . ($i++));

                                            $fieldSets = $pluginform->getFieldsets();
                                            $html      = array();
                                            $hasconfig = false;
                                            foreach ($fieldSets as $name => $fieldSet)
                                            {
                                                if (!$pluginform->getFieldset($name))
                                                {
                                                    continue;
                                                }

                                                $html[] = '<table class="paramlist admintable" >';

                                                if (isset($fieldSet->description) && !empty($fieldSet->description))
                                                {
                                                    $desc   = JText::_($fieldSet->description);
                                                    $html[] = '<tr><td class="paramlist_description" colspan="2">' . $desc . '</td></tr>';
                                                }

                                                foreach ($pluginform->getFieldset($name) as $field)
                                                {
                                                    if ($field->hidden)
                                                    {
                                                        continue;
                                                    }

                                                    // Set the value for the form
                                                    $field->value = $pluginparams->get($field->fieldname, $field->default);

                                                    $maxjoomlaversion = $this->form->getFieldAttribute($field->fieldname, "maxjoomlaversion", false);
                                                    if ($maxjoomlaversion && version_compare(JVERSION, $maxjoomlaversion, ">"))
                                                    {
                                                        continue;
                                                    }
                                                    $minjoomlaversion = $this->form->getFieldAttribute($field->fieldname, "minjoomlaversion", false);
                                                    if ($minjoomlaversion && version_compare(JVERSION, $minjoomlaversion, "<"))
                                                    {
                                                        continue;
                                                    }

                                                    $hasconfig = true;
                                                    $class     = isset($field->class) ? $field->class : "";

                                                    if (StringHelper::strlen($class) > 0)
                                                    {
                                                        $class = " class='$class'";
                                                    }
                                                    $html[] = "<tr $class>";
                                                    if (!isset($field->label) || $field->label == "")
                                                    {
                                                        $html[] = '<td class="paramlist_key"><span class="editlinktip">' . $field->label . '</span></td>';
                                                        $html[] = '<td class="paramlist_value">' . $field->input . '</td>';
                                                    }
                                                    else
                                                    {
                                                        $html[] = '<td class="paramlist_value" colspan="2">' . $field->input . '</td>';
                                                    }

                                                    $html[] = '</tr>';
                                                }
                                                $html[] = '</table>';
                                                echo implode("\n", $html);
                                            }
                                            echo JHtml::_('bootstrap.endSlide');
                                        }
                                        else
                                        {
                                            ?>
                                            <div class="accordion-group">
                                                <div class="accordion-heading">
                                                    <strong>
                                                    <span class="accordion-toggle">
                                                    <?php echo $label; ?>
                                                    </span>
                                                    </strong>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                    }
                                    else
                                    {
                                        //echo $plugin->name;
                                    }
                                }
                                echo JHtml::_('bootstrap.endAccordion');
                                echo JHtml::_('bootstrap.endPanel');
                            }

                            echo JHtml::_('bootstrap.endPane', 'myParamsTabs');
                            ?>
                </div>
            </div>
        </section>
    </div>
    <input type="hidden" name="id" value="<?php echo $this->component->id; ?>"/>
    <input type="hidden" name="component" value="<?php echo $this->component->option; ?>"/>

    <input type="hidden" name="controller" value="component"/>
    <input type="hidden" name="option" value="<?php echo JEV_COM_COMPONENT; ?>"/>
    <input type="hidden" name="task" value=""/>
    <?php echo JHTML::_('form.token'); ?>
    </fieldset>
    </form>
</div>


