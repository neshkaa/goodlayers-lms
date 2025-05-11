	// accordion item
	if( !function_exists('gdlr_get_accordion_item') ){
		function gdlr_get_accordion_item( $settings ){
			$item_id = empty($settings['page-item-id'])? '': ' id="' . $settings['page-item-id'] . '" ';
	
			global $gdlr_spaces;
			$margin = (!empty($settings['margin-bottom']) && 
				$settings['margin-bottom'] != $gdlr_spaces['bottom-item'])? 'margin-bottom: ' . $settings['margin-bottom'] . ';': '';
			$margin_style = (!empty($margin))? ' style="' . $margin . '" ': '';
			
			$accordion = is_array($settings['accordion'])? $settings['accordion']: json_decode($settings['accordion'], true);
			
			$current_tab = 0;
			$settings['title-type'] = (empty($settings['title-type']))? 'none': $settings['title-type'];
			$settings['title'] = (empty($settings['title']))? '': $settings['title'];
			$settings['caption'] = (empty($settings['caption']))? '': $settings['caption'];
			$settings['icon'] = (empty($settings['icon']))? '': $settings['icon'];
			
			$ret .= gdlr_get_item_title(array(
				'title' => $settings['title'],
				'caption' => $settings['caption'],
				'icon' => $settings['icon'],
				'type' => $settings['title-type']
			));				
			$ret .= '<div class="gdlr-item gdlr-accordion-item '  . $settings['style'] . '" ' . $item_id . $margin_style . ' >';
			foreach( $accordion as $tab ){  $current_tab++;
				$ret .= '<div class="accordion-tab';
				$ret .= ($current_tab == intval($settings['initial-state']))? ' active" >': '" >';
				$ret .= '<h4 class="accordion-title" ';
				$ret .= empty($tab['gdl-tab-title-id'])? '': 'id="' . $tab['gdl-tab-title-id'] . '" ';
				$ret .= '><i class="';
				$ret .= ($current_tab == intval($settings['initial-state']))? 'icon-minus fa fa-minus': 'icon-plus fa fa-plus';
				$ret .= '" ></i><span>' . gdlr_text_filter($tab['gdl-tab-title']) . '</span></h4>';
				$ret .= '<div class="accordion-content">' . gdlr_content_filter($tab['gdl-tab-content']) . '</div>';
				$ret .= '</div>';				
			}
			$ret .= '</div>';
			
			return $ret;
		}
	}	

	// toggle box item
	if( !function_exists('gdlr_get_toggle_box_item') ){
		function gdlr_get_toggle_box_item( $settings ){
			$item_id = empty($settings['page-item-id'])? '': ' id="' . $settings['page-item-id'] . '" ';
			
			global $gdlr_spaces;
			$margin = (!empty($settings['margin-bottom']) && 
				$settings['margin-bottom'] != $gdlr_spaces['bottom-item'])? 'margin-bottom: ' . $settings['margin-bottom'] . ';': '';
			$margin_style = (!empty($margin))? ' style="' . $margin . '" ': '';

			$accordion = is_array($settings['toggle-box'])? $settings['toggle-box']: json_decode($settings['toggle-box'], true);
			$settings['title-type'] = (empty($settings['title-type']))? 'none': $settings['title-type'];
			$settings['title'] = (empty($settings['title']))? '': $settings['title'];
			$settings['caption'] = (empty($settings['caption']))? '': $settings['caption'];
			$settings['icon'] = (empty($settings['icon']))? '': $settings['icon'];
			
			$ret .= gdlr_get_item_title(array(
				'title' => $settings['title'],
				'caption' => $settings['caption'],
				'icon' => $settings['icon'],
				'type' => $settings['title-type']
			));	
			$ret .= '<div class="gdlr-item gdlr-accordion-item gdlr-multiple-tab '  . $settings['style'] . '" ' . $item_id . $margin_style . ' >';
			foreach( $accordion as $tab ){ 
				$ret .= '<div class="accordion-tab';
				$ret .= ($tab['gdl-tab-active'] == 'yes')? ' active" >': '" >';
				$ret .= '<h4 class="accordion-title" ';
				$ret .= empty($tab['gdl-tab-title-id'])? '': 'id="' . $tab['gdl-tab-title-id'] . '" ';
				$ret .= '><i class="';
				$ret .= ($tab['gdl-tab-active'] == 'yes')? 'icon-minus fa fa-minus': 'icon-plus fa fa-plus';
				$ret .= '" ></i><span>' . gdlr_text_filter($tab['gdl-tab-title']) . '</span></h4>';
				$ret .= '<div class="accordion-content">' . gdlr_content_filter($tab['gdl-tab-content']) . '</div>';
				$ret .= '</div>';
			}
			$ret .= '</div>';
			
			return $ret;
		}
	}		