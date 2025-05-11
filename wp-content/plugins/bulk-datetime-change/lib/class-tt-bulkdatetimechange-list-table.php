<?php
/**
 * Bulk Datetime Change
 *
 * @package    Bulk Datetime Change
 * @subpackage BulkDatetimeChange List Table
 * reference   Custom List Table Example
 *             https://wordpress.org/plugins/custom-list-table-example/
/*
	Copyright (c) 2020- Katsushi Kawamori (email : dodesyoswift312@gmail.com)
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; version 2 of the License.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
	require_once ABSPATH . 'wp-admin/includes/screen.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
	require_once ABSPATH . 'wp-admin/includes/template.php';
}

/** ==================================================
 * List table
 */
class TT_BulkDatetimeChange_List_Table extends WP_List_Table {

	/** ==================================================
	 * Max items
	 *
	 * @var $max_items  max_items.
	 */
	public $max_items;

	/** ==================================================
	 * Construct
	 *
	 * @since 1.00
	 */
	public function __construct() {

		global $status, $page;
		/* Set parent defaults */
		parent::__construct(
			array(
				'singular'  => 'bulk_date_update',
				'ajax'      => false,
			)
		);
	}

	/** ==================================================
	 * Read data
	 *
	 * @since 1.00
	 */
	private function read_data() {

		$search_text = get_user_option( 'bulkdatetimechange_search_text', get_current_user_id() );
		if ( isset( $_POST['bulk-datetime-change-filter'] ) && ! empty( $_POST['bulk-datetime-change-filter'] ) ) {
			if ( check_admin_referer( 'bdtc_filter', 'bulk_datetime_change_filter' ) ) {
				if ( ! empty( $_POST['post_type'] ) ) {
					$post_type_text = sanitize_text_field( wp_unslash( $_POST['post_type'] ) );
					$post_type = explode( ',', $post_type_text );
					update_user_option( get_current_user_id(), 'bulkdatetimechange_filter_post_type', $post_type );
				}
				if ( ! empty( $_POST['user_id'] ) ) {
					update_user_option( get_current_user_id(), 'bulkdatetimechange_filter_user', absint( $_POST['user_id'] ) );
				} else {
					update_user_option( get_current_user_id(), 'bulkdatetimechange_filter_user', null );
				}
				if ( ! empty( $_POST['monthly'] ) ) {
					$monthly = sanitize_text_field( wp_unslash( $_POST['monthly'] ) );
					$monthly_arr = explode( ',', $monthly );
					update_user_option( get_current_user_id(), 'bulkdatetimechange_filter_monthly', $monthly_arr );
				} else {
					update_user_option( get_current_user_id(), 'bulkdatetimechange_filter_monthly', null );
				}
				if ( ! empty( $_POST['category_id'] ) ) {
					update_user_option( get_current_user_id(), 'bulkdatetimechange_filter_category', absint( $_POST['category_id'] ) );
				} else {
					update_user_option( get_current_user_id(), 'bulkdatetimechange_filter_category', null );
				}
				if ( ! empty( $_POST['mime_type'] ) ) {
					$mime_type = sanitize_text_field( wp_unslash( $_POST['mime_type'] ) );
					update_user_option( get_current_user_id(), 'bulkdatetimechange_filter_mime_type', $mime_type );
				} else {
					update_user_option( get_current_user_id(), 'bulkdatetimechange_filter_mime_type', null );
				}
				if ( ! empty( $_POST['search_text'] ) ) {
					$search_text = sanitize_text_field( wp_unslash( $_POST['search_text'] ) );
					update_user_option( get_current_user_id(), 'bulkdatetimechange_search_text', $search_text );
				} else {
					delete_user_option( get_current_user_id(), 'bulkdatetimechange_search_text' );
					$search_text = null;
				}
			}
		}

		if ( current_user_can( 'manage_options' ) ) {
			$author = get_user_option( 'bulkdatetimechange_filter_user', get_current_user_id() );
		} else {
			$author = get_current_user_id();
		}

		$args = array(
			'post_type'      => get_user_option( 'bulkdatetimechange_filter_post_type', get_current_user_id() ),
			'post_status'    => 'any',
			'author'         => $author,
			'category'       => get_user_option( 'bulkdatetimechange_filter_category', get_current_user_id() ),
			'post_mime_type' => get_user_option( 'bulkdatetimechange_filter_mime_type', get_current_user_id() ),
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);
		$posts = get_posts( $args );

		$listtable_array = array();

		$monthly_archive = get_user_option( 'bulkdatetimechange_filter_monthly', get_current_user_id() );

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				if ( empty( $monthly_archive ) ) {
					$monthly = true;
				} elseif ( in_array( $post->ID, $monthly_archive ) ) {
						$monthly = true;
				} else {
					$monthly = false;
				}
				if ( $monthly ) {
					$categories = get_the_terms( $post->ID, 'category' );
					$category_text = null;
					if ( ! empty( $categories ) ) {
						$category_arr = array();
						foreach ( $categories as $category ) {
							$category_arr[] = $category->name;
						}
						$category_text = implode( ',', $category_arr );
					}
					$display_name = get_the_author_meta( 'display_name', $post->post_author );

					$search = false;
					if ( $search_text ) {
						if ( false !== strpos( $post->post_title, $search_text ) ) {
							$search = true;
						}
						if ( false !== strpos( $display_name, $search_text ) ) {
							$search = true;
						}
						if ( false !== strpos( $category_text, $search_text ) ) {
							$search = true;
						}
						if ( false !== strpos( $post->post_mime_type, $search_text ) ) {
							$search = true;
						}
						if ( false !== strpos( $post->post_date, $search_text ) ) {
							$search = true;
						}
					} else {
						$search = true;
					}

					if ( $search ) {
						$bulkdatetimechange_settings = get_user_option( 'bulkdatetimechange', get_current_user_id() );
						if ( 'modified' === $bulkdatetimechange_settings['method'] ) {
							$date = $post->post_modified;
						} else {
							$date = $post->post_date;
						}
						$date = apply_filters( 'bdtc_exif_date', $date, $post->ID, get_current_user_id() );
						$listtable_array[] = array(
							'ID'         => $post->ID,
							'title'      => $post->post_title,
							'datetime'   => $date,
							'posttype'   => $post->post_type,
							'author'     => $display_name,
							'categories' => $category_text,
						);
					}
				}
			}
		}

		return $listtable_array;
	}

	/** ==================================================
	 * Column default
	 *
	 * @param array  $item  item.
	 * @param string $column_name  column_name.
	 * @since 1.00
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'datetime':
				return sprintf(
					'<input type="text" id=datetimepicker-bdtc name="%1$s[%2$s]" value="%3$s" form="bulkdatetimechange_forms" />',
					/*%1$s*/ $this->_args['singular'],
					/*%2$s*/ $item['ID'],
					/*%3$s*/ $item['datetime']
				);
			case 'posttype':
				$post_types = get_user_option( 'bulkdatetimechange_all_post_type', get_current_user_id() );
				return $post_types[ $item[ $column_name ] ];
			case 'author':
				return $item[ $column_name ];
			case 'categories':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); /* Show the whole array for troubleshooting purposes */
		}
	}

	/** ==================================================
	 * Column title
	 *
	 * @param array $item  item.
	 * @since 1.00
	 */
	public function column_title( $item ) {

		$post_type = get_post_type( $item['ID'] );
		if ( 'attachment' === $post_type ) {
			$image_src = wp_get_attachment_image_src( $item['ID'], 'thumbnail', true );
			$title_html = '<a href="' . get_the_permalink( $item['ID'] ) . '"><img style="margin: 0 5px 0 0; padding: 0 5px 0 0; width: 30px; height: 30px; vertical-align: middle; float: left;" src="' . $image_src[0] . '"><span style="vertical-align: top;">' . $item['title'] . '</span></a>';
		} else {
			$attach_id = get_post_thumbnail_id( $item['ID'] );
			$image_src = wp_get_attachment_image_src( $attach_id, 'thumbnail', true );
			$title_html = '<span class="dashicons dashicons-admin-post" style="vertical-align: middle; float: left;"></span>';
			$title_html = '<a href="' . get_the_permalink( $item['ID'] ) . '"><img style="margin: 0 5px 0 0; padding: 0 5px 0 0; width: 30px; height: 30px; vertical-align: middle; float: left;" src="' . $image_src[0] . '"><span style="vertical-align: top;">' . $item['title'] . '</span></a>';
		}

		$status = get_post_status( $item['ID'] );
		switch ( $status ) {
			case 'publish':
				$status_html = null;
				break;
			case 'pending':
				$status_html = '<b> - ' . __( 'Pending' ) . '</b>';
				break;
			case 'draft':
				$status_html = '<b> - ' . __( 'Draft' ) . '</b>';
				break;
			case 'future':
				$status_html = '<b> - ' . __( 'Scheduled' ) . '</b>';
				break;
			case 'private':
				$status_html = '<b> - ' . __( 'Private' ) . '</b>';
				break;
			default:
				$status_html = null;
		}

		/* Return the title contents */
		return sprintf(
			'%1$s <span style="color:silver"></span>',
			/*$1%s*/ $title_html . $status_html
		);
	}

	/** ==================================================
	 * Column checkbox
	 *
	 * @param array $item  item.
	 * @since 1.00
	 */
	public function column_cb( $item ) {

		return sprintf(
			'<input type="checkbox" name="bulk_date_check[]" value="%1$s" form="bulkdatetimechange_forms" />',
			/*%1$s*/ $item['ID']
		);
	}

	/** ==================================================
	 * Get Columns
	 *
	 * @since 1.00
	 */
	public function get_columns() {

		$bulkdatetimechange_settings = get_user_option( 'bulkdatetimechange', get_current_user_id() );
		if ( 'modified' === $bulkdatetimechange_settings['method'] ) {
			$method = __( 'Last updated' );
		} else {
			$method = __( 'Posted', 'bulk-datetime-change' );
		}

		$columns = array(
			'cb'         => '<input type="checkbox" />', /* Render a checkbox instead of text */
			'title'      => __( 'Title' ),
			'datetime'   => __( 'Date/time' ) . ' ' . $method,
			'posttype'   => __( 'Post type', 'bulk-datetime-change' ),
			'author'     => __( 'Author' ),
			'categories' => __( 'Categories' ),
		);

		return $columns;
	}

	/** ==================================================
	 * Get Sortable Columns
	 *
	 * @since 1.00
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'title'      => array( 'title', false ),
			'datetime'   => array( 'datetime', false ),
			'posttype'   => array( 'posttype', false ),
			'author'     => array( 'author', false ),
			'categories' => array( 'categories', false ),
		);
		return $sortable_columns;
	}

	/** ************************************************************************
	 * REQUIRED! This is where you prepare your data for display. This method will
	 * usually be used to query the database, sort and filter the data, and generally
	 * get it ready to be displayed. At a minimum, we should set $this->items and
	 * $this->set_pagination_args(), although the following properties and methods
	 * are frequently interacted with here...
	 *
	 * @global WPDB $wpdb
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_sortable_columns()
	 * @uses $this->get_pagenum()
	 * @uses $this->set_pagination_args()
	 **************************************************************************/
	public function prepare_items() {

		/**
		 * First, lets decide how many records per page to show
		 */
		$per_page = get_user_option( 'bdtc_per_page', get_current_user_id() );

		/**
		 * REQUIRED. Now we need to define our column headers. This includes a complete
		 * array of columns to be displayed (slugs & titles), a list of columns
		 * to keep hidden, and a list of columns that are sortable. Each of these
		 * can be defined in another method (as we've done here) before being
		 * used to build the value for our _column_headers property.
		 */
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		/**
		 * REQUIRED. Finally, we build an array to be used by the class for column
		 * headers. The $this->_column_headers property takes an array which contains
		 * 3 other arrays. One for all columns, one for hidden columns, and one
		 * for sortable columns.
		 */
		$this->_column_headers = array( $columns, $hidden, $sortable );

		/**
		 * Instead of querying a database, we're going to fetch the example data
		 * property we created for use in this plugin. This makes this example
		 * package slightly different than one you might build on your own. In
		 * this example, we'll be using array manipulation to sort and paginate
		 * our data. In a real-world implementation, you will probably want to
		 * use sort and pagination data to build a custom query instead, as you'll
		 * be able to use your precisely-queried data immediately.
		 */
		$data = $this->read_data();
		do_action( 'bdtc_custompost_update', get_current_user_id() );
		do_action( 'bdtc_filter_form', get_current_user_id() );
		do_action( 'bdtc_bulk_input' );

		/**
		 * This checks for sorting input and sorts the data in our array accordingly.
		 *
		 * In a real-world situation involving a database, you would probably want
		 * to handle sorting by passing the 'orderby' and 'order' values directly
		 * to a custom query. The returned data will be pre-sorted, and this array
		 * sorting technique would be unnecessary.
		 *
		 * @param array $a  a.
		 * @param array $b  b.
		 */
		function usort_reorder( $a, $b ) {
			/* If no sort, default to title */
			if ( isset( $_REQUEST['orderby'] ) && ! empty( $_REQUEST['orderby'] ) ) {
				$orderby = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) );
			} else {
				$orderby = 'datetime';
			}
			/* If no order, default to asc */
			if ( isset( $_REQUEST['order'] ) && ! empty( $_REQUEST['order'] ) ) {
				$order = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) );
			} else {
				$order = 'asc';
			}
			$result = strcmp( $a[ $orderby ], $b[ $orderby ] ); /* Determine sort order */
			return ( 'desc' === $order ) ? $result : -$result; /* Send final sort direction to usort */
		}
		usort( $data, 'usort_reorder' );

		/***********************************************************************
		 * ---------------------------------------------------------------------
		 * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
		 *
		 * In a real-world situation, this is where you would place your query.
		 *
		 * For information on making queries in WordPress, see this Codex entry:
		 * http://codex.wordpress.org/Class_Reference/wpdb
		 *
		 * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
		 * ---------------------------------------------------------------------
		 */

		/**
		 * REQUIRED for pagination. Let's figure out what page the user is currently
		 * looking at. We'll need this later, so you should always include it in
		 * your own package classes.
		 */
		$current_page = $this->get_pagenum();

		/**
		 * REQUIRED for pagination. Let's check how many items are in our data array.
		 * In real-world use, this would be the total number of items in your database,
		 * without filtering. We'll need this later, so you should always include it
		 * in your own package classes.
		 */
		$total_items = count( $data );
		$this->max_items = $total_items;

		/**
		 * The WP_List_Table class does not handle pagination for us, so we need
		 * to ensure that the data is trimmed to only the current page. We can use
		 * array_slice() to
		 */
		$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

		/**
		 * REQUIRED. Now we can add our *sorted* data to the items property, where
		 * it can be used by the rest of the class.
		 */
		$this->items = $data;

		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,                  /* WE have to calculate the total number of items */
				'per_page'    => $per_page,                     /* WE have to determine how many items to show on a page */
				'total_pages' => ceil( $total_items / $per_page ),   /* WE have to calculate the total number of pages */
			)
		);
	}
}
