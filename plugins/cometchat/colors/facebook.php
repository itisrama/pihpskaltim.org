<?php

$parentColor = 'facebook';

/* SETTINGS START */

$themeSettings['bar_background'] = setColorValue('bar_background','#F6F6F8',$parentColor);
$themeSettings['bar_gradient_start'] = setColorValue('bar_gradient_start','#F6F6F8',$parentColor);
$themeSettings['bar_gradient_center'] = setColorValue('bar_gradient_center','#F2F3F4',$parentColor);
$themeSettings['bar_gradient_end'] = setColorValue('bar_gradient_end','#EEEFF0',$parentColor);
$themeSettings['bar_border'] = setColorValue('bar_border','#B3BAC7',$parentColor);
$themeSettings['bar_color'] = setColorValue('bar_color','#333333',$parentColor);
$themeSettings['bar_color_disabled'] = setColorValue('bar_color_disabled','#A7A7A7',$parentColor);
$themeSettings['bar_border_light'] = setColorValue('bar_border_light','#E5E5E5',$parentColor);
$themeSettings['bar_font_family'] = setColorValue('bar_font_family','Helvetica, Arial, "lucida grande",tahoma,verdana,arial,sans-serif',$parentColor);
$themeSettings['bar_font_size'] = setColorValue('bar_font_size','12px',$parentColor);
$themeSettings['bar_text_background'] = setColorValue('bar_text_background','#FFFFFF',$parentColor);
$themeSettings['tab_background'] = setColorValue('tab_background','#EDEFF4',$parentColor);
$themeSettings['tab_color'] = setColorValue('tab_color','#111111',$parentColor);
$themeSettings['tab_border'] = setColorValue('tab_border','#A2A3A5',$parentColor);
$themeSettings['tab_font_family'] = setColorValue('tab_font_family','Helvetica, Arial, "lucida grande",tahoma,verdana,arial,sans-serif',$parentColor);
$themeSettings['tab_font_size'] = setColorValue('tab_font_size','12px',$parentColor);
$themeSettings['tab_font_size_small'] = setColorValue('tab_font_size_small','11px',$parentColor);
$themeSettings['tab_color_self'] = setColorValue('tab_color_self','#3E454C',$parentColor);
$themeSettings['tab_title_color'] = setColorValue('tab_title_color','#FFFFFF',$parentColor);
$themeSettings['tab_title_backgroud_light'] = setColorValue('tab_title_backgroud_light','#E1E2E5',$parentColor);
$themeSettings['tab_title_background'] = setColorValue('tab_title_background','#4E6AAB',$parentColor);
$themeSettings['tab_title_gradient_start'] = setColorValue('tab_title_gradient_start','#4E6AAB',$parentColor);
$themeSettings['tab_title_gradient_center'] = setColorValue('tab_title_gradient_center','#4966A7',$parentColor);
$themeSettings['tab_title_gradient_end'] = setColorValue('tab_title_gradient_end','#4B67A8',$parentColor);
$themeSettings['tab_title_border'] = setColorValue('tab_title_border','#2E4588',$parentColor);
$themeSettings['tab_title_font_family'] = setColorValue('tab_title_font_family','Helvetica, Arial, "lucida grande",tahoma,verdana,arial,sans-serif',$parentColor);
$themeSettings['tab_title_font_size'] = setColorValue('tab_title_font_size','12px',$parentColor);
$themeSettings['tab_title_font_size_large'] = setColorValue('tab_title_font_size_large','12px',$parentColor);
$themeSettings['tab_title_text_background'] = setColorValue('tab_title_text_background','#3EA9BD',$parentColor);
$themeSettings['tab_sub_background'] = setColorValue('tab_sub_background','#FFFFFF',$parentColor);
$themeSettings['tab_sub_color'] = setColorValue('tab_sub_color','#111111',$parentColor);
$themeSettings['tab_border_light'] = setColorValue('tab_border_light','#D1D2D4',$parentColor);
$themeSettings['tab_border_lighter'] = setColorValue('tab_border_lighter','#C9D0DA',$parentColor);
$themeSettings['tooltip_background'] = setColorValue('tooltip_background','#282828',$parentColor);
$themeSettings['tooltip_color'] = setColorValue('tooltip_color','#FFFFFF',$parentColor);
$themeSettings['tooltip_color_light'] = setColorValue('tooltip_color_light','#EEEEEE',$parentColor);
$themeSettings['tooltip_font_family'] = setColorValue('tooltip_font_family','Helvetica, Arial, "lucida grande",tahoma,verdana,arial,sans-serif',$parentColor);
$themeSettings['tooltip_font_size'] = setColorValue('tooltip_font_size','12px',$parentColor);
$themeSettings['tooltip_break'] = setColorValue('tooltip_break','#D3D7DC',$parentColor);
$themeSettings['tab_link_color'] = setColorValue('tab_link_color','#288597',$parentColor);
$themeSettings['messagecount_background'] = setColorValue('messagecount_background','#96281B',$parentColor);

/* SETTINGS END */

global $color;
if(!defined('CCADMIN')){
	$themeSettings = setNewColorValue($themeSettings,$color);
}