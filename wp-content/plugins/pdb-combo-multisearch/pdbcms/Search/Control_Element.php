<?php

/**
 * class for generating a search control, given the name of field in the Participants 
 * Database database
 * 
 * @category   Plugins
 * @package    WordPress
 * @author     Roland Barker <webdesign@xnau.com>
 * @copyright  2011 - 2020  xnau webdesign
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GPL2
 * @version    2.8
 */

namespace pdbcms\Search;

class Control_Element {

  /**
   * @var \PDb_Form_Field_Def the field definition object
   */
  private $field_def;

  /**
   * @var \pdbcms\multifields\search_field object
   */
  private $search_field;

  /**
   * the current value of the field
   * @var string
   */
  private $value;

  /**
   * @var string an additional class value, added to the base value
   */
  private $add_class = '';

  /**
   * @var array holds the configuration array for the element
   */
  private $config;
  
  /**
   * @var string element id
   * 
   * this is only used for "chosen" elements
   */
  private $chosen_id = false;

  /**
   * instantiates the search control object
   * 
   * @param \pdbcms\multifields\search_field $search_field
   */
  public function __construct( \pdbcms\multifields\search_field $search_field )
  {
    $this->search_field = $search_field;
    $this->field_def = new \PDb_Form_Field_Def( $search_field->name() );
    $this->value = $this->_get_field_value();

    $this->filter_chosen_selectors();

    $list_query = list_query_stash::get();

    if ( $list_query )
    {
      $this->filter_dropdown_values( $list_query );
    }
    
    if ( $this->search_field->is_chosen() )
    {
      add_filter( 'pdbcde-element_id', [$this,'chosen_element_id'], 10, 2);
    }
  }

  /**
   * provides an instance for the combo field
   * 
   * @param string $fieldname
   * @return Control_Element instance
   */
  public static function combo_search_control( $fieldname )
  {
    $config = \pdbcms\multifields\added_field::config( $fieldname );
    
    $config->attributes['class'] = 'combo-search-field';
    
    return new self( new \pdbcms\multifields\search_field( $config ) );
  }

  /**
   * provides an element ID value
   * 
   * @return string
   */
  private function id()
  {
    return 'pdb-' . $this->field_def->name() . '-control-multisearch-' . $this->instance_index();
  }

  /**
   * sets the values of a search field based on the current filter
   * 
   * first tries to get it from the post array, then looks in the current filter 
   * for the field value 
   * 
   * @return mixed the field value
   */
  protected function _get_field_value()
  {
    $value = '';
    $postvalue = $this->prep_value();

    if ( !empty( $postvalue ) && $postvalue !== 0 )
    {
      if ( is_string( $postvalue ) )
      {
        $value = filter_var( $postvalue, FILTER_DEFAULT, \Participants_Db::string_sanitize() );
      } elseif ( is_array( $postvalue ) )
      {
        $value = $this->sanitize_array( $postvalue );
      }
    } else
    {

      $saved_query = \Participants_Db::$session->get( self::list_query_session_name() );

//      error_log(__METHOD__.' session name: '.$this->list_query_session_name().' got query: '.print_r($saved_query,1));

      $field_filter = isset( $saved_query['where_clauses'][$this->field_def->name()] ) ? $saved_query['where_clauses'][$this->field_def->name()] : false;

//      if ( $field_filter ) error_log(__METHOD__.' field: '.$this->field_def->name().' field filter: '.print_r($field_filter[0],1));

      $value = $this->get_field_filter_value( $field_filter );
      
      if ( $value === '' ) {
        $value = apply_filters( 'pdbcms-get_preload_value', '', $this->field_def->name() );
      }
    }

    return $value;
  }

  /**
   * gets the field value from the session value
   * 
   * @param string|array $field_filter
   * @return string|array the extracted field value 
   */
  private function get_field_filter_value( $field_filter )
  {
    $value = '';
    
    if ( $field_filter )
    {
      if ( $this->field_def->is_value_set() )
      {
        $value = array();
        foreach ( $field_filter as $filter )
        {
          $value[] = $filter->term;
        }
      } else
      {
        $value = $field_filter[0]->term;
      }
    }
    
    return $value;
  }

  /**
   * makes a value array for multi-select submissions
   * 
   * leaves other types of values alone
   * 
   * @return string|array the prepared value 
   */
  private function prep_value()
  {
    $value = $this->field_get_value();

    $is_get_value = strlen( $value ) > 0;

    if ( !$is_get_value && array_key_exists( $this->field_def->name(), $_POST ) )
    {

      $value = \pdbcms\Plugin::sanitize( $_POST[$this->field_def->name()] );
    }

    if ( is_string( $value ) && $this->field_def->is_multi() )
    {
      $value = explode( ',', trim( $value, ',' ) );
    }

    /*
     * if the value is coming in from the GET array, get the equivalent value 
     * title if it is a value set field to avoid a case mismatch
     */
    if ( $is_get_value && $this->field_def->is_value_set() )
    {

      if ( is_array( $value ) )
      {
        $valuelist = $value;
        $value = array();
        foreach ( $valuelist as $subvalue )
        {
          if ( strlen( $subvalue ) > 0 )
          {
            $value[] = \PDb_FormElement::get_title_value( $subvalue, $this->field_def->name() );
          }
        }
      } elseif ( strlen( $value ) > 0 )
      {
        $value = \PDb_FormElement::get_title_value( $value, $this->field_def->name() );
      }
    }

    return $value;
  }

  /**
   * provides a field value from the GET array if available
   * 
   * this is to implement an URL with a filter built into it
   * 
   * for multiple fields/values, it needs to use a syntax like this:
   * ?search_field[]=city&value[]=Huntsville&search_field[]=country&value[]=USA
   * 
   * @return string|bool value if found, bool false if not
   */
  private function field_get_value()
  {
    if ( !\pdbcms\Plugin::is_get_search() )
    {
      return false;
    }

    $values = \pdbcms\Plugin::sanitize_array( array_combine( (array) $_GET['search_field'], (array) $_GET['value'] ) );

    return isset( $values[$this->field_def->name()] ) ? $values[$this->field_def->name()] : false;
  }

  /**
   * sanitizes a post array value
   * 
   * @param array $array unsanitized array
   * @return array
   */
  private function sanitize_array( $array )
  {
    $sanitized = array();
    foreach ( $array as $value )
    {
      $sanitized[] = filter_var( $value, FILTER_DEFAULT, \Participants_Db::string_sanitize() );
    }
    return $sanitized;
  }

  /**
   * provides the name of the list query session
   * 
   * @global \pdbcms\Plugin $PDb_Combo_Multi_Search
   * @param int $instance an optional instance number, defaults to the current instance
   * @return string the name
   */
  public static function list_query_session_name( $instance = false )
  {
    if ( !$instance )
    {
      global $PDb_Combo_Multi_Search;
      $instance = $PDb_Combo_Multi_Search->instance_index;
    }
    return 'list_query-' . $instance;
  }

  /**
   * provides the element class
   * 
   * @return string the control element classname
   */
  private function control_class()
  {
    return apply_filters( 'pdbcms_element_control_class', 'pdb-' . $this->field_def->name() . '-select ' . $this->add_class . ' ' );
  }

  /**
   * sets the extra class string
   * 
   * @param string $class the class to add to the element
   */
  public function add_class( $class )
  {
    $this->add_class = $class;
  }

  /**
   * tells if the "any" option should be applied
   * 
   * @return bool
   */
  private function apply_any_option()
  {
    return $this->search_field->any_option;
  }

  /**
   * sets up a search control given a field name
   * 
   * this function takes the field, and depending on the type, generates an appropriate 
   * search control
   * 
   * returns an object with properties:
   *    name - the name of the field
   *    title - display title
   *    control - HTML of the control
   *    wrap_class - class to use in the control wrapper
   *    help_text - help text to show with the control
   * 
   * @return stdClass object
   */
  public function get_search_control()
  {
    if ( !$this->field_def )
      return false;

    $control = (object) array(
                'name' => $this->field_def->name(),
                'title' => $this->label(),
                'group' => $this->field_def->group(),
                'id' => $this->id(),
                'wrap_class' => $this->field_def->form_element() . '-search',
                'control' => $this->search_control_html(),
                'help_text' => $this->help_text(),
                'form_element' => $this->field_def->form_element(),
    );

    /**
     * @filter pdb-combo-multisearch-search_control
     * 
     * @param object control properties
     * @param \PDb_Form_Field_Def the control element
     * @return object
     */
    $search_control = \Participants_Db::apply_filters( 'combo-multisearch-search_control', $control, $this->field_def );
    
    return $search_control;
  }
  
  /**
   * generates the element ID for a chosen selector element
   * 
   * @param string $id
   * @param string $name
   * @return string ID
   */
  public function chosen_element_id( $id, $name )
  {
    $atts = $this->search_field->attributes();
    
    if ( isset( $atts['class'] ) && strpos( $atts['class'],'combo-search-field' ) !== false )
    {
      return $id;
    }
    
    $join = '__';
    
    if ( strpos( $id, $join ) === false )
    {
      add_filter( 'pdb-form_element_html', [$this, 'set_chosen_element_id'], 10, 2 );
      $id = 'pdb-' . $name . $join . uniqid();
      $this->chosen_id = $id;
    }
    
    return $id;
  }
  
  /**
   * sets the element id for a chosen element
   * 
   * @param string $html
   * @param \PDb_FormElement $field
   * @return string html
   */
  public function set_chosen_element_id( $html, $field )
  {
    if ( $this->chosen_id && strpos( $html, $field->name . '-control') !== false )
    {
      $html = preg_replace( '/id="[^"]+"/', 'id="' . $this->chosen_id . '"', $html );
    }
    return $html;
  }

  /**
   * provides the search control type
   * 
   * @return string
   */
  protected function search_control_type()
  {
    return $this->field_def->form_element();
  }

  /**
   * provides the search control html
   * 
   * @return string HTML
   */
  private function search_control_html()
  {
    $html = '';

    $base_config = $this->base_element_config();
    $dropdown_wrap = '<div class="pdb-multisearch-control custom-select' . ($this->search_field->is_chosen() ? ' chosen-select' : '') . '">%s</div>'; // this is to give dropdowns a custom style

    $control_type = \Participants_Db::apply_filters( 'combo-multisearch-search_control_type', $this->search_control_type(), $this->field_def );

    switch ( $control_type ) {
      case 'checkbox':
        $second_option = count( $this->checkbox_options() ) > 1;
        $html = $this->element_html( array(
            'type' => $second_option ? 'radio' : 'checkbox',
            'options' => $second_option ? array_merge( $this->checkbox_options(), $this->null_list() ) : $this->checkbox_options(),
                ) + $base_config
        );
        break;

      case 'radio':
      case 'select-other':
        $html = $this->element_html( $base_config );
        break;

      case 'dropdown':
      case 'dropdown-other':
        $html = sprintf( $dropdown_wrap, $this->element_html( $base_config ) );
        break;

      case 'multi-checkbox':
      case 'multi-select-other':
        $html = $this->element_html( $base_config );
        break;

      case 'date':
      case 'timestamp':
        $type = $this->field_def->form_element() === 'date5' ? 'date5' : 'text-line';

        $default_date = date( $control_type === 'timestamp' ? 'Y-m-d H:i:s' : get_option( 'date_format' ) );

        $start = isset( $this->value['start'] ) ? $this->value['start'] : '';
        $end = isset( $this->value['end'] ) ? $this->value['end'] : '';

        $attributes = array_merge(
                array('id' => $base_config['attributes']['id'] . '_start'),
                $this->search_field->attributes(),
                array('class' => 'date_field date-start-range')
        );

        if ( !isset( $attributes['required'] ) )
        {
          $attributes['data-default'] = $default_date;
        }

        $html = $this->element_html( array(
            'type' => $type,
            'value' => $start,
            'attributes' => $attributes,
            'name' => $this->field_def->name() . ( $this->field_uses_ranged_search() ? '[start]' : '' ),
                ) + $base_config
        );

        if ( $this->field_uses_ranged_search() )
        {
          $attributes = array_merge(
                  array('id' => $base_config['attributes']['id'] . '_end'),
                  $this->search_field->attributes(),
                  array('class' => 'date_field date-end-range')
          );
          if ( !isset( $attributes['required'] ) )
          {
            $attributes['data-default'] = $default_date;
          }
          $html .= '<span class="inline-label"> ' . _x( 'through', 'between two values to indicate a range of values', 'pdb-combo-multisearch' ) . ' </span>';
          $html .= $this->element_html( array(
              'type' => $type,
              'value' => $end,
              'attributes' => $attributes,
              'name' => $this->field_def->name() . '[end]',
                  ) + $base_config
          );
        }
        break;

      case 'placeholder':
        break;

      case 'hidden':
        // dynamic hidden field should not show default value
        if ( $this->field_def->is_dynamic_hidden_field() )
        {
          $base_config['value'] = '';
        }
      case 'link':
        if ( $control_type === 'link' )
        {
          $base_config['value'] = $base_config['value'][0];
          $this->value = $this->value[0];
        }
      case 'text-line':
      case 'text-area':
      case 'rich-text':
      case 'string-combine':
      case 'date-calc':
        if ( $this->search_field->uses_text_as_dropdown() )
        {
          $this->search_field->any_option = true;
          $options = $this->null_list() + $this->get_unique_recorded_values( true, true );

          // try to find the matching value even if the case doesn't match
          if ( !array_key_exists( $base_config['value'], $options ) )
          {
            $match_options = array_change_key_case( $options, CASE_LOWER );
            if ( array_key_exists( strtolower( $this->value ), $match_options ) )
            {
              $base_config['value'] = $match_options[$this->value];
            }
          }

          $html = sprintf( $dropdown_wrap, $this->element_html( array(
                      'type' => $this->search_field->form_element(),
                      'options' => $options,
                          ) + $base_config
                  ) );
        } else
        {
          if ( $base_config['value'] === 'any' )
          {
            unset( $base_config['value'] );
          }
          unset( $base_config['options'] ); // not needed here
          $html = $this->element_html( array(
              'type' => 'text-line',
                  ) + $base_config
          );
        }
        break;

      case 'numeric':
      case 'currency':
      case 'decimal':
      case 'numeric-calc':

        if ( $this->field_uses_ranged_search() )
        {

          $this->setup_ranged_value();

          $start = $this->value['start'] === '' && isset( $this->field_def->attributes['min-default'] ) ? $this->field_def->attributes['min-default'] : '';
          $end = $this->value['end'] === '' && isset( $this->field_def->attributes['max-default'] ) ? $this->field_def->attributes['max-default'] : '';

          $control = array('<span class="ranged-search-control">');

          $control[] = $this->element_html( array(
              'value' => $start,
              'attributes' => array_merge( array('id' => $base_config['attributes']['id'] . '_start'), $this->search_field->attributes(), array('class' => 'numeric-start-range') ),
              'name' => $this->field_def->name() . '[start]',
                  ) + $base_config
          );
          $control[] = '<span class="inline-label"> ' . _x( 'to', 'between two values to indicate a range of values', 'pdb-combo-multisearch' ) . ' </span>';
          $control[] = $this->element_html( array(
              'value' => $end,
              'attributes' => array_merge( array('id' => $base_config['attributes']['id'] . '_end'), $this->search_field->attributes(), array('class' => 'numeric-end-range') ),
              'name' => $this->field_def->name() . '[end]',
                  ) + $base_config
          );
          $control[] = '</span>';

          $html = implode( PHP_EOL, $control );
        } else
        {
          $html = $this->element_html( $base_config );
        }
        break;

      default:

        $html = $this->element_html( $base_config );
    }

    return $html;
  }

  /**
   * provides the base element config array
   * 
   * @return array
   */
  private function base_element_config()
  {
    return array(
        'value' => $this->display_value(),
        'name' => $this->field_def->name(),
        'title' => $this->search_field->label(),
        'type' => $this->search_field->form_element(),
        'validation' => 'no',
        'class' => $this->control_class(),
        'attributes' => array_merge( $this->search_field->attributes(), array('id' => $this->id()) ),
        'options' => $this->field_def->is_value_set() ? $this->option_list() : array(),
    );
  }

  /**
   * provides the field's value for the display configuration
   * 
   * @return string
   */
  private function display_value()
  {
    $value = $this->value === '' ? $this->search_field->default_value() : $this->value;

    return $value;
  }

  /**
   * does a string replace on the HTML to add an identifier to element ids
   * 
   * @param string $html
   * @return string html
   */
  private function add_identifier( $html )
  {
    preg_match( '/(id|for)="(.+?)"/', $html, $matches );

    return str_replace( $matches[2], $matches[2] . '-multisearch-' . $this->instance_index(), $html );
  }

  /**
   * provides the instance index value for the control element
   * 
   * 
   * @global \pdbcms\Plugin $PDb_Combo_Multi_Search
   * @return int
   */
  private function instance_index()
  {
    global $PDb_Combo_Multi_Search;
    return $PDb_Combo_Multi_Search->instance_index;
  }

  /**
   * provides the field options and adds the null select if defined
   * 
   * @return array with null select added if defined
   */
  private function option_list()
  {
    return $this->null_list() + $this->field_options();
  }

  /**
   * provides the null list array
   * 
   * this array is used to add an "any" selector to the dropdown or multi checkbox
   * 
   * @return array
   */
  private function null_list()
  {
    $null_select_key = \pdbcms\Plugin::null_select_key();

    $any_option = $this->field_def->is_value_set() ? array($null_select_key => 'false', $this->search_field->any_option_label() => 'any') : array($null_select_key => '');

    return $this->search_field->any_option ? $any_option : array($null_select_key => 'false');
  }

  /**
   * provides the options for a checkbox field
   * 
   * this is to remove the null select option if present in the field def
   * 
   * @return array
   */
  private function checkbox_options()
  {
    $null_select_key = \PDb_FormElement::null_select_key();
    $clean_options = $this->field_def->options();

    if ( array_key_exists( $null_select_key, $clean_options ) )
    {
      unset( $clean_options[$null_select_key] );
    }

    return $clean_options;
  }

  /**
   * sets up the start/end values for a ranged search
   */
  private function setup_ranged_value()
  {
    if ( !is_array( $this->value ) )
    {
      $this->value = (array) $this->value;
    }

    if ( !isset( $this->value['start'] ) )
    {
      $this->value['start'] = isset( $this->value[0] ) ? $this->value[0] : '';
    }
    if ( !isset( $this->value['end'] ) )
    {
      $this->value['end'] = isset( $this->value[1] ) ? $this->value[1] : '';
    }
  }

  /**
   * supplies the form element HTML
   * 
   * @param array $config the element configuration
   * @return string element HTML
   */
  private function element_html( $config )
  {
    $this->config = $config;

    add_filter( 'pdb-form_field_attributes', array($this, 'override_attributes'), 30, 2 );
    
    /**
     * @filter pdbcms-search_element_configuration
     * @param array of element configuration values
     * @return array
     */
    $html = \PDb_FormElement::get_element( apply_filters( 'pdbcms-search_element_configuration', $this->config ) );

    remove_filter( 'pdb-form_field_attributes', array($this, 'override_attributes') );

    return $html;
  }

  /**
   * overrides the field def attributes with the multi-search-field defined attributes
   * 
   * @param array $attributes the field attributes
   * @param \PDb_Form_Field_Def $field  current instance
   * @return array as $name => $value
   */
  public function override_attributes( $attributes, $field )
  {
    if ( $this->search_field->name() === $field->name() )
    {
      $attributes = $this->config['attributes'];
    }
    return $attributes;
  }

  /**
   * provides the options list for the column
   * 
   * @return array
   */
  public function field_options()
  {
    return $this->search_field->db_values ? $this->get_unique_recorded_values( true, false ) : $this->_dropdown_options();
  }

  /**
   * sets a help text value
   * @var string the help text value
   */
  public function set_help_text( $string )
  {
    $this->help_text = $string;
  }

  /**
   * gets the defined option values of a field
   * 
   * retuns an array suitable for defining a selection control
   * 
   * @return array
   */
  public function get_field_values()
  {
    return $this->_dropdown_options();
  }

  /**
   * gets a list of unique values from a specified field
   * 
   * @global \wpdb $wpdb
   * @param bool $make_title if true, makes a title out of the values, if false the 
   *                         values and titles will be the same
   * @param bool $include_any if true, includes an "any" value at the top of the list
   * @return array of values: $title => $value
   */
  public function get_unique_recorded_values( $make_title = false, $include_any = true )
  {
    $cache = new field_value_cache( $this->field_def->name() );

    $options = $cache->get_list();

    if ( !is_array( $options ) )
    {
      global $wpdb;
      $sql = "SELECT DISTINCT p." . $this->field_def->name() . " FROM " . \Participants_Db::participants_table() . " p ";

      /**
       * @filter pdbcms_get_unique_values_query
       * 
       * @param string query
       * @param \PDb_Form_field_Def the field
       * @return string query
       */
      $values = $wpdb->get_col( apply_filters( 'pdbcms_get_unique_values_query', $sql, $this->field_def ) );

      \Participants_Db::debug_log( __METHOD__ . ' query: ' . $wpdb->last_query, 2 );

      if ( $values === array(null) || !is_array( $values ) )
      {
        \Participants_Db::debug_log( __METHOD__ . ' values could not be obtained from field "' . $this->field_def->name() . '"' );

        return $this->get_field_values();
      }

      $options = $this->_dropdown_options( array_filter( $values ) );

      $cache->update_field_values( $options );
    }

    return $options;
  }

  /**
   * provide the field options
   * 
   * if the data is stored as serialized values, or even comma-separated values, 
   * we need to collect all the terms and create a new array of unique terms from 
   * that list
   * 
   * @param array $options optional values array to prep
   * 
   * @return array of $name => $title values
   */
  private function _dropdown_options( $options = false )
  {

    if ( $options === false )
    {

      $dropdown_options = $this->field_def->options();
    } else
    {

      $dropdown_options = array();
      foreach ( $options as $title => $db_entry )
      {
        if ( is_int( $title ) && !$this->field_def->is_multi() )
        {
          $title = $this->field_def->value_title( $db_entry );
        }
        if ( $this->field_def->is_multi() )
        {
          foreach ( $this->multi_field_values( $db_entry ) as $sub_entry )
          {
            $sub_entry_value = trim( (string) $sub_entry );
            $dropdown_options[$this->field_def->value_title( $sub_entry_value )] = $sub_entry_value;
          }
        } else
        {
          $dropdown_options[$title] = (string) $db_entry;
        }
      }
    }

    // alpha sort only if there are no optgroups
    if ( $this->plugin_option( 'alpha_sort' ) === '1' && array_search( 'optgroup', $dropdown_options ) === false && strpos( $this->field_def->form_element(), 'radio' ) === false )
    {
      $dropdown_options = array_flip( $dropdown_options );
      natcasesort( $dropdown_options );
      $dropdown_options = array_flip( $dropdown_options );
    }

    return $dropdown_options;
  }

  /**
   * provides a multi-field saved value in array form
   * 
   * can adapt to several different ways of saving multi-field data
   * 
   * @param string $db_entry the entry from the database
   * @return array of values
   */
  private function multi_field_values( $db_entry )
  {
    $db_entry = \Participants_Db::unserialize_array( $db_entry, false );

    return is_array( $db_entry ) ? $db_entry : explode( ',', $db_entry );
  }

  /**
   * provides an array with all empty elements removed
   * 
   * won't touch elements with a value of 0
   * 
   * @param array $array
   * @return array
   */
  public static function clean_array( $array )
  {
    return array_filter( $array, function ( $v ) {
      return $v !== '';
    } );
  }

  /**
   * gets the column's attributes array
   * 
   * @return array  attributes as name => value
   */
  public function column_attributes()
  {
    return $this->field_def->attributes();
  }

  /**
   * tests an array as associative or indexed
   * 
   * @param array $array
   * @return bool true if indexed
   */
  public function is_associative( $array )
  {
    return array_keys( $array ) !== range( 0, count( $array ) - 1 );
  }

  /**
   * supplies the search field help text
   * 
   * @return string
   */
  public function label()
  {
    return \Participants_Db::apply_filters( 'translate_string', $this->search_field->label() );
  }

  /**
   * supplies the search field help text
   * 
   * @return string
   */
  public function help_text()
  {
    return \Participants_Db::apply_filters( 'translate_string', $this->search_field->help_text() );
  }

  /**
   * applies the shortcode filter to the dropdown values for a search selector
   * 
   * @param \PDb_List_Query $list_query
   */
  public function filter_dropdown_values( $list_query )
  {
    $filters = $list_query->get_field_filters();
    $where_clause_list = array();

    foreach ( $list_query->get_field_filters() as $field => $filter_list )
    {
      foreach ( $filter_list as $fieldname => $filter )
      {
        /** @var \PDb_List_Query_Filter $filter */
        if ( $filter->is_shortcode() && $fieldname !== $this->field_def->name() )
        {
          $where_clause_list[] = (object) array('name' => $fieldname, 'sql' => $filter->statement(), 'logic' => $filter->logic());
        }
      }
    }

    if ( count( $where_clause_list ) )
    {
      $query_filter = new unique_values_query( $where_clause_list );
      add_filter( 'pdbcms_get_unique_values_query', array($query_filter, 'add_where_clauses') );
    }
  }

  /**
   * gets a plugin option value
   * 
   * @global \pdbcms\Plugin $PDb_Combo_Multi_Search
   * @param string  $name of the option
   * @param mixed   $default value for the option
   * 
   * @return mixed the option value
   */
  private function plugin_option( $name, $default = '' )
  {
    global $PDb_Combo_Multi_Search;
    return $PDb_Combo_Multi_Search->plugin_option( $name, $default );
  }

  /**
   * tells if the current field uses a renged search control
   * 
   * @return bool true if a ranged search should be used
   */
  public function field_uses_ranged_search()
  {
    /**
     * @filter pdbcms-field_uses_ranged_search
     * 
     * this is only available to numeric field types
     * 
     * @param bool the gobal preference
     * @param \PDb_Form_Field_Def the current field definition
     * @return bool true if the control should use a ranged search
     */
    return apply_filters( 'pdbcms-field_uses_ranged_search', $this->search_field->uses_ranged_control(), $this->field_def );
  }

  /**
   * sets up a filter for chosen selectors
   * 
   * this is to make sure the field does not try to display as readonly
   */
  private function filter_chosen_selectors()
  {
    add_action( 'pdbcde-before_element_rendered', function ( $field ) {
      $field->readonly = false;
    } );
  }

}

class unique_values_query {

  /**
   * @var string the where clauses
   */
  private $where_clauses;

  /**
   * @var string stores the first clause logic
   */
  private $first_logic;

  /**
   * @param array $where_clause_list
   */
  public function __construct( $where_clause_list )
  {
    $where_clause = ' ';
    $i = 0;
    do {
      if ( $i > 0 )
      {
        $where_clause .= $where_clause_list[$i]->logic . ' ';
      } else
      {
        $this->first_logic = $where_clause_list[$i]->logic;
      }
      $where_clause .= $where_clause_list[$i]->sql . ' ';
      $i++;
    } while ( $i < count( $where_clause_list ) );

    $this->where_clauses = $where_clause;
  }

  /**
   * appends the where clauses
   * 
   * @param string $query
   * @return string
   */
  public function add_where_clauses( $query )
  {
    remove_filter( 'pdbcms_get_unique_values_query', array($this, 'add_where_clauses') );

    $join = strpos( $query, 'WHERE' ) === false ? 'WHERE' : $this->first_logic;

    return $query . ' ' . $join . $this->where_clauses;
  }

}
