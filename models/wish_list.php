<?

	class Wish_List extends Db_ActiveRecord {
		public $strings = array(
			'model_title' => 'List',
			'model_name' => 'list',
			'model_code' => 'wish_list',
			'table_name' => 'wish_lists',
			'module_name' => 'wish'
		);
		
		public $implement = 'Db_AutoFootprints';
		public $auto_footprints_visible = true;
		public $auto_footprints_default_invisible = false;
		
		public $has_many = array(
			'images' => array('class_name' => 'Db_File', 'foreign_key' => 'master_object_id', 'conditions' => "master_object_class='Wish_List' and field='images'", 'order' => 'sort_order, id', 'delete' => true)
		);
		
		public $has_and_belongs_to_many = array(
			'items' => array('class_name' => 'Wish_List_Item', 'join_table' => 'wish_lists_items', 'primary_key' => 'wish_list_id', 'foreign_key' => 'wish_list_item_id'),
			'customers' => array('class_name' => 'Shop_Customer', 'join_table' => 'wish_lists_customers', 'primary_key' => 'wish_list_id', 'foreign_key' => 'shop_customer_id')
		);
		
		public $calculated_columns = array(
			'items_count' => array(
				'sql' => '(
					select 
						count(wish_lists_items.id) 
					from 
						wish_lists_items 
					where 
						wish_lists.id=wish_lists_items.wish_list_id
				)', 
				'type' => db_number
			)
		);
		
		protected $api_added_columns = array();
		
		public function __construct() {
			$this->table_name = $this->strings['table_name'];
			$this->module_name = $this->strings['module_name'];
			$this->model_name = $this->strings['model_name'];
			
			parent::__construct();
		}
		
		public static function create() {
			return new self();
		}

		public function define_columns($context = null) {
			$this->define_column('title', 'Title')->order('asc');
			$this->define_column('slug', 'Slug');
			$this->define_column('description', 'Description')->invisible()->validation()->fn('trim');
			$this->define_column('sort_order', 'Sort Order')->validation()->fn('trim')->unique("Chosen sort order is already in use.");
			$this->define_column('is_enabled', 'Enabled');
			$this->define_column('items_count', 'Item Count');
			$this->define_multi_relation_column('customers', 'customers', 'Customers', '@id')->invisible();
			$this->define_multi_relation_column('images', 'images', 'Images', '@name')->invisible();
			$this->define_multi_relation_column('items', 'items', 'Items', '@id')->invisible();

			$this->defined_column_list = array();
			Backend::$events->fireEvent("{$this->strings['module_name']}:onExtend{$this->strings['model_title']}Model", $this, $context);
			$this->api_added_columns = array_keys($this->defined_column_list);
		}

		public function define_form_fields($context = null) {
			$this->add_form_field('is_enabled')->tab($this->strings['model_title'])->renderAs(frm_checkbox);
			$this->add_form_field('title', 'left')->tab($this->strings['model_title'])->renderAs(frm_text);
			$this->add_form_field('slug', 'right')->tab($this->strings['model_title'])->renderAs(frm_text);
			$this->add_form_field('customers')->tab('Customers')->renderAs('related');

			$editor_config = System_HtmlEditorConfig::get($this->module_name, "{$this->strings['model_code']}_description");
			$field = $this->add_form_field('description')->tab($this->strings['model_title']);
			$field->renderAs(frm_html)->size('small');
			$editor_config->apply_to_form_field($field);
			
			$this->add_form_field('items')->tab($this->strings['model_title']);
			
			$this->add_form_field('images')->renderAs(frm_file_attachments)->renderFilesAs('image_list')->addDocumentLabel('Add image(s)')->tab('Images')->noAttachmentsLabel('There are no images uploaded')->noLabel()->imageThumbSize(555)->fileDownloadBaseUrl(url('ls_backend/files/get/'));
			
			Backend::$events->fireEvent("{$this->strings['module_name']}:onExtend{$this->strings['model_title']}Form", $this, $context);
			
			foreach($this->api_added_columns as $column_name) {
				$form_field = $this->find_form_field($column_name);
				
				if($form_field)
					$form_field->optionsMethod('get_added_field_options');
			}
		}
		
		public function find_by_customer($customer) {
			return $customer->wishlist_lists;
		}
		
		public static function sort($first, $second) {
			if($first->sort_order == $second->sort_order)
				return 0;
				
			if($first->sort_order > $second->sort_order)
				return 1;
				
			return -1;
		}
		
		public static function set_orders($item_ids, $item_orders) {
			if(is_string($item_ids))
				$item_ids = explode(',', $item_ids);
				
			if(is_string($item_orders))
				$item_orders = explode(',', $item_orders);

			foreach($item_ids as $index => $id) {
				$order = $item_orders[$index];
				
				Db_DbHelper::query("update {$this->strings['table_name']} set sort_order=:sort_order where id=:id", array(
					'sort_order' => $order,
					'id' => $id
				));
			}
		}
		
		public function after_create() {
			Db_DbHelper::query("update {$this->strings['table_name']} set sort_order=:sort_order where id=:id", array(
				'sort_order' => $this->id,
				'id' => $this->id
			));

			$this->sort_order = $this->id;
		}
		
		public function product_exists($product) {
			foreach($this->items as $item) {
				if($item->product->id === $product->id)
					return true;
			}
			
			return false;
		}
	}