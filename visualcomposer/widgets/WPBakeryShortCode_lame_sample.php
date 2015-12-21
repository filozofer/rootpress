<?php

namespace Rootpress\visualcomposer\widgets;

use Rootpress\visualcomposer\widgets\WidgetVC_Abstract;

/** 
 * Representing the "Lame Sample" Widget
 */
class WPBakeryShortCode_lame_sample extends WidgetVC_Abstract {

    //Name of the widget display in backoffice
    public static $widgetName = 'Lame Sample';
    //Name use for declare the widget as vc widget (must be unique)
    public static $base = 'lame_sample';
    //Class add to the html form
    public static $class = 'lame_sample';
    //Icon use for this widget
    public static $icon = 'icon-wpb-vc_pr_emploi_block';
    //Category in which this widget will be put when choosing widget to add in backoffice
    public static $category = 'Sample Category';
    //Widget Description
    public static $description = 'Block for example';

    /**
     * How the widget is render inside the frontend
     */
    public function content($atts, $content = null, $base = '') {

        //Get Context
        $data = Timber::get_context();

        //Handle default values
        $atts = $this->handleDefaultValues($atts, [
            'title'       => '', 
            'description' => ''
        ]);

        //Get the title
        $data['title'] = $atts['title'];

        //Get the content
        $data['description'] = $this->decode($atts['description']);

        //Render
        return $this->renderView('views/widgets/WPBakeryShortCode_lame_sample.twig', $data);
    }

    /**
     * Declare this widget inside visual composer
     */
    public static function declareAsVisualComposerWidget() {
    	$params = [
			[
				'type'       => 'textfield',
				'heading'    => 'Titre',
				'param_name' => 'title'
			],
			[
				'type'       => 'vc_wysiwyg_field',
				'heading'    => 'Contenu',
				'param_name' => 'description'
			]
		];
		vc_map([
			'base'     => static::$base,
			'name'     => static::$widgetName,
			'class'    => static::$class,
			'icon'     => static::$icon,
            'description' => static::$description,
            'category' => static::$category,
			'params'   => $params
		]);
    }

}