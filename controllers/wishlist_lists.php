<?

	class Wishlist_Lists extends Backend_Controller {
		public $implement = 'Db_ListBehavior, Db_FormBehavior, Db_FilterBehavior';
		public $list_model_class = 'Wishlist_List';
		public $list_record_url = null;
		public $list_name;
		
		public $form_preview_title = 'Wishlist Lists';
		public $form_create_title = 'New List';
		public $form_edit_title = 'Edit List';
		public $form_model_class = 'Wishlist_List';
		public $form_not_found_message = 'Record not found';
		public $form_redirect = null;
		
		public $form_edit_save_flash = 'The record has been successfully saved';
		public $form_create_save_flash = 'The record has been successfully added';
		public $form_edit_delete_flash = 'The record has been successfully deleted';
		public $form_edit_save_auto_timestamp = true;
		
		public $list_search_enabled = true;
		public $list_search_fields = array('@title');
		public $list_search_prompt = 'find lists by title';
		public $list_no_setup_link = false;
		public $list_no_interaction = false;
		public $list_no_sorting = false;
		public $list_columns = array();
		public $list_custom_body_cells = null;
		public $list_custom_head_cells = null;
		
		public $url = '/wishlist/lists';
		public $title = 'Lists';
		public $name = 'lists';
		public $model_name = 'list';
		public $model_title = 'List';
		public $module_path = '/modules/wishlist';
		public $module_name = 'wishlist';
		public $module_title = 'Wishlist';
		
		protected $required_permissions = array('wishlist:lists');
		
		protected $globalHandlers = array(
			'onLoadAddCustomersForm',
			'onAddCustomers',
			'onUpdateCustomers',
			'onRemoveCustomer',
			'onLoadAddItemsForm',
			'onAddItems',
			'onUpdateItems',
			'onRemoveItem'
		);
		
		public function __construct() {
			parent::__construct();
			
			$this->app_tab = 'wishlist';
			$this->app_module_name = 'Wishlist';

			$this->list_record_url = url('/wishlist/lists/edit/');
			$this->list_columns = array('title', 'slug', 'items_count', 'is_enabled');
			$this->form_redirect = url('/wishlist/lists/');
			$this->app_page = 'wishlist_lists';

			if(Phpr::$router->action == 'reorder') {
				$this->list_record_url = null;
				$this->list_search_enabled = false;
				$this->list_no_interaction = true;
				$this->list_custom_body_cells = PATH_APP . '/modules/wishlist/controllers/wishlist_lists/_body_cells.htm';
				$this->list_custom_head_cells = PATH_APP . '/modules/wishlist/controllers/wishlist_lists/_head_cells.htm';
			}
			else if(post('add_items_mode')) {
				$this->list_record_url = '#';
				$this->list_name = 'Wishlist_List_Items_' . Phpr::$router->action . '_list';
				$this->list_data_context = 'items';
				$this->list_model_class = 'Wishlist_List_Item';
				$this->list_search_fields = array('product_calculated_join.name');
				$this->list_search_prompt = 'find items by title';
				$this->list_top_partial = false;
				$this->list_columns = array('title');
				$this->list_custom_prepare_func = null;
				$this->list_search_enabled = true;
				$this->list_no_setup_link = true;
				$this->list_no_form = true;
				$this->list_top_partial = false;
				$this->list_items_per_page = 10;
				$this->list_custom_body_cells = PATH_APP . '/phproad/modules/db/behaviors/db_listbehavior/partials/_list_body_cb.htm';
				$this->list_custom_head_cells = PATH_APP . '/phproad/modules/db/behaviors/db_listbehavior/partials/_list_head_cb.htm';
			}
			else if(post('add_customers_mode')) {
				$this->list_record_url = url('/shop/customers/edit/');
				$this->list_name = 'Shop_Customers_' . Phpr::$router->action . '_list';
				$this->list_data_context = 'customers';
				$this->list_model_class = 'Shop_Customer';
				$this->list_search_fields = array('@first_name', '@last_name');
				$this->list_search_prompt = 'find customers by name';
				$this->list_top_partial = false;
				$this->list_columns = array('first_name', 'last_name');
				$this->list_custom_prepare_func = null;
				$this->list_search_enabled = true;
				$this->list_no_setup_link = true;
				$this->list_no_form = true;
				$this->list_top_partial = false;
				$this->list_items_per_page = 10;
				$this->list_custom_body_cells = PATH_APP . '/phproad/modules/db/behaviors/db_listbehavior/partials/_list_body_cb.htm';
				$this->list_custom_head_cells = PATH_APP . '/phproad/modules/db/behaviors/db_listbehavior/partials/_list_head_cb.htm';
			}
		}
		
		public function index() {
			$this->app_page_title = 'Lists';
		}
		
		public function reorder() {
			$this->app_page_title = 'Manage List Order';
		}
		
		public function listPrepareData() {
			if(post('add_items_mode')) {
				$item = new Wishlist_List_item();
				$this->filterApplyToModel($item, 'items');
				
				return $item;
			}
			else if(post('add_customers_mode')) {
				$customer = new Shop_Customer();
				$this->filterApplyToModel($customer, 'customers');
				
				return $customer;
			}
			
			$list = new Wishlist_List();
			
			return $list;
		}
		
		public function listOverrideSortingColumn($sorting_column) {
			if(Phpr::$router->action === 'reorder') {
				$result = array('field' => 'sort_order', 'direction' => 'asc');
				
				return (object)$result;
			}

			return $sorting_column;
		}
		
		protected function reorder_onSetOrders() {
			try {
				SiteManagement_Site::set_orders(post('item_ids'), post('sort_orders'));
			}
			catch(Exception $ex) {
				Phpr::$response->ajaxReportException($ex, true, true);
			}
		}
		
		public function formCreateProductModelObject() {
			$context = $this->formGetUniquePrefix();
			
			if ($context == 'csv_import')
				return $this->csvImportGetModelObj();
			
			if ($context == 'csv_grid_import') {
				$obj = new Shop_Product();
				$obj->define_form_fields();
				return $obj;
			}

			if ($context != 'grouped') {
				$obj = Shop_Product::create();
				$obj->init_columns_info();
				$obj->define_form_fields($context);
				$obj->tax_class_id = Shop_TaxClass::get_default_class_id();
			}
			else
				$obj = $this->initGroupedProduct(null);

			return $obj;
		}

		public function formFindProductModelObject($id, $copy_relations = false) {
			$context = $this->formGetUniquePrefix();

			if($context != 'grouped') {
				$obj = Shop_Product::create()->find($id);
				if ($obj)
					$obj->define_form_fields($context);
			}
			else
			 	$obj = $this->initGroupedProduct($id, $copy_relations);
			
			if (!$obj)
				throw new Phpr_ApplicationException($this->form_not_found_message);

			return $obj;
		}
		
		/* 
		 * Custom events
		 */
		
		protected function onCustomEvent($id = null) {
			$product = null;
			
			if(Phpr::$router->action === 'edit' || Phpr::$router->action === 'create')
				$customer = $this->get_customer($id);

			Backend::$events->fireEvent(post('custom_event_handler'), $this, $customer);
		}
		

		/**
		 * Customers
		 */
		protected function onLoadAddCustomersForm($parent_id = null) {
			try {
				$this->viewData['edit_session_key'] = post('edit_session_key');
			}
			catch(Exception $ex) {
				$this->handlePageError($ex);
			}
			
			$this->renderPartial('add_customers_form');
		}

		protected function onAddCustomers($parent_id = null) {
			try {
				$ids = post('list_ids', array());
				
				if(!count($ids))
					throw new Phpr_ApplicationException('Please select customer(s) to add.');

				$list = Wishlist_List::create()->find($parent_id);
				$customers = Shop_Customer::create()->where('id in (?)', array($ids))->find_all();

				foreach($customers as $customer) { 
					$list->customers->add($customer, post('edit_session_key'));
				}
				
				$list->save(null, post('edit_session_key'));
				$list = Wishlist_List::create()->find($parent_id);
			}
			catch(Exception $ex) {
				Phpr::$response->ajaxReportException($ex, true, true);
			}
		}
		
		protected function onUpdateCustomers($parent_id = null) {
			try {
				$this->viewData['form_model'] = Wishlist_List::create()->find($parent_id);
				$this->renderPartial('customers');
			}
			catch(Exception $ex) {
				Phpr::$response->ajaxReportException($ex, true, true);
			}
		}
		
		protected function onRemoveCustomer($parent_id = null) {
			try {
				$list = Wishlist_List::create()->find($parent_id);

				$id = post('shop_customer_id');
				$customer = Shop_Customer::create()->find($id);
				
				if($customer)
					$list->customers->delete($customer, $this->formGetEditSessionKey());

				$this->viewData['form_model'] = $list;
				$this->renderPartial('customers');
			}
			catch(Exception $ex) {
				Phpr::$response->ajaxReportException($ex, true, true);
			}
		}
		
		/**
		 * Items
		 */
		protected function onLoadAddItemsForm($parent_id = null) {
			try {
				$this->viewData['edit_session_key'] = post('edit_session_key');
			}
			catch(Exception $ex) {
				$this->handlePageError($ex);
			}
			
			$this->renderPartial('add_items_form');
		}

		protected function onAddItems($parent_id = null) {
			try {
				$ids = post('list_ids', array());
				
				if(!count($ids))
					throw new Phpr_ApplicationException('Please select item(s) to add.');

				$list = Wishlist_List::create()->find($parent_id);
				$items = Wishlist_List_Item::create()->where('id in (?)', array($ids))->find_all();

				foreach($items as $item) { 
					$list->items->add($item, post('edit_session_key'));
				}
				
				$list->save(null, post('edit_session_key'));
				$list = Wishlist_List::create()->find($parent_id);
			}
			catch(Exception $ex) {
				Phpr::$response->ajaxReportException($ex, true, true);
			}
		}
		
		protected function onUpdateItems($parent_id = null) {
			try {
				$this->viewData['form_model'] = Wishlist_List::create()->find($parent_id);
				$this->renderPartial('items');
			}
			catch(Exception $ex) {
				Phpr::$response->ajaxReportException($ex, true, true);
			}
		}
		
		protected function onRemoveItem($parent_id = null) {
			try {
				$list = Wishlist_List::create()->find($parent_id);

				$id = post('wishlist_list_item_id');
				$item = Wishlist_List_Item::create()->find($id);
				
				if($item)
					$list->items->delete($item, $this->formGetEditSessionKey());

				$this->viewData['form_model'] = $list;
				$this->renderPartial('items');
			}
			catch(Exception $ex) {
				Phpr::$response->ajaxReportException($ex, true, true);
			}
		}
	}