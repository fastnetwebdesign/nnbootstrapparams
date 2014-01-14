<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * JDocument Modules renderer
 *
 * @package     Joomla.Platform
 * @subpackage  Document
 * @since       11.1
 */
class JDocumentRendererModules extends JDocumentRenderer
{
	/**
	 * Renders multiple modules script and returns the results as a string
	 *
	 * @param   string  $position  The position of the modules to render
	 * @param   array   $params    Associative array of values
	 * @param   string  $content   Module content
	 *
	 * @return  string  The output of the script
	 *
	 * @since   11.1
	 */
	public function render($position, $params = array(), $content = null)
	{
		$renderer = $this->_doc->loadRenderer('module');
		$buffer = '';

		$app = JFactory::getApplication();
		$frontediting = $app->get('frontediting', 1);
		$user = JFactory::getUser();

		$canEdit = $user->id && $frontediting && !($app->isAdmin() && $frontediting < 2) && $user->authorise('core.edit', 'com_modules');
		$menusEditing = ($frontediting == 2) && $user->authorise('core.edit', 'com_menus');

        $modules = JModuleHelper::getModules($position);
        $count = count($modules);
        $counter = 0;
        foreach ( $modules as $mod)
		{
            //Plugin Change
            $mod = $this->changeparams($mod,$count,$counter,$params['style']);

			$moduleHtml = $renderer->render($mod, $params, $content);

			if ($app->isSite() && $canEdit && trim($moduleHtml) != '' && $user->authorise('core.edit', 'com_modules.module.' . $mod->id))
			{
				$displayData = array('moduleHtml' => &$moduleHtml, 'module' => $mod, 'position' => $position, 'menusediting' => $menusEditing);
				JLayoutHelper::render('joomla.edit.frontediting_modules', $displayData);
			}

			$buffer .= $moduleHtml;
            $counter++;
		}
		return $buffer;
	}

    public function changeparams($module,$count,$counter,$style = null) {

        $params = new JRegistry;
        $params->loadString($module->params);

        $moduleclass_sfx = $params->get('moduleclass_sfx');

        //Check the first and last Module on Position
        switch($counter) {
            case '0':
                $moduleclass_sfx .= ' first';
                break;
            case ($counter == $count-1):
                $moduleclass_sfx .= '  last';
                break;
        }

        //Set an Modulecounter to CSS
        $moduleclass_sfx .= ' box'.$counter;


        $paramsChromeStyle = $params->get('style');

        //Check Module Style Parameter is not set jdoc style is default
        if($paramsChromeStyle) $style = $paramsChromeStyle;

        if($style!='table' and $style!='horz' and $style!='none' and $style!='outline'){

            //Standard col-xs-12 when nothing is set
            if(!$params->get('extra_small_devices_grid') and !$params->get('small_devices_grid') and !$params->get('medium_devices_grid') and !$params->get('large_devices_grid')){
                if($params->get('bootstrap_size'))
                    $moduleclass_sfx .=' col-xs-'.$params->get('bootstrap_size');
                else
                   $moduleclass_sfx .=' col-xs-12';
            }

            //Bootstrap Grid
            if($params->get('extra_small_devices_grid'))
                $moduleclass_sfx .=' col-xs-'.$params->get('extra_small_devices_grid');

            if($params->get('small_devices_grid'))
                $moduleclass_sfx .=' col-sm-'.$params->get('small_devices_grid');

            if($params->get('medium_devices_grid'))
                $moduleclass_sfx .=' col-md-'.$params->get('medium_devices_grid');

            if($params->get('large_devices_grid'))
                $moduleclass_sfx .=' col-lg-'.$params->get('large_devices_grid');

            //visible and hidden
            if($params->get('extra_small_devices_available') == 1)
                $moduleclass_sfx .=' hidden-xs';

            if($params->get('extra_small_devices_available') == 2)
                $moduleclass_sfx .=' visible-xs';

            if($params->get('small_devices_available') == 1)
                $moduleclass_sfx .=' hidden-sm';

            if($params->get('small_devices_available') == 2)
                $moduleclass_sfx .=' visible-sm';

            if($params->get('medium_devices_available') == 1)
                $moduleclass_sfx .=' hidden-md';

            if($params->get('medium_devices_available') == 2)
                $moduleclass_sfx .=' visible-md';

            if($params->get('large_devices_available') == 1)
                $moduleclass_sfx .=' hidden-lg';

            if($params->get('large_devices_available') == 2)
                $moduleclass_sfx .=' visible-lg';

            //Print
            if($params->get('bootstrap_print') == 1)
                $moduleclass_sfx .=' hidden-print';

            if($params->get('bootstrap_print') == 2)
                $moduleclass_sfx .=' visible-print';
        }

        $params->set('moduleclass_sfx',$moduleclass_sfx);

        //set new Parameter
        $module->params = $params;
        return $module;
    }
}