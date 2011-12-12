<?

	class Wish_List_Item extends Db_ActiveRecord {
		public $table_name = 'wish_list_items';
		public $name = 'list item';
		public $title = 'List Item';
		
		public $calculated_columns = array(
			'title' => array('sql' => "product_calculated_join.name", 'type' => db_text) // , 'join' => array('auto_make'=>'auto_make.id=auto_model.make_id')
		);
		
		public $belongs_to = array(
			'product' => array('class_name' => 'Shop_Product', 'foreign_key' => 'shop_product_id')
		);
		
		public function define_columns($context = null) {
			$this->define_relation_column('product', 'product', 'Product', db_varchar, "@name");
			$this->define_column('title', 'Title')->validation()->fn('trim');
			$this->define_column('quantity', 'Quantity')->validation()->fn('trim');
			$this->define_column('extras', 'Extras')->validation()->fn('trim');
			$this->define_column('options', 'Options')->validation()->fn('trim');
			$this->define_column('sort_order', 'Sort Order')->validation()->fn('trim');
		}
	
		public function define_form_fields($context = null) {
			$this->add_form_field('product')->tab($this->title);
			$this->add_form_field('quantity', 'left')->tab($this->title)->renderAs(frm_text);
			
			$options = $this->get_options();
			if($options)
				$this->add_form_custom_area('item_options')->tab('Options');

			$extras = $this->get_extra_options();
			if($extras)
				$this->add_form_custom_area('item_extras')->tab('Extras');
		}
		
		public function get_product_options($key = -1) {
			$options = array();
			$options[0] = '<choose>';

			$types = Db_DbHelper::objectArray('select * from shop_products order by name');
			
			foreach($types as $type)
				$options[$type->id] = $type->name;

			return $options;
		}
		
		public static function create() {
			return new self();
		}
	}