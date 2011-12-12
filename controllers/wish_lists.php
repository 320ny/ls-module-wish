<?

	class Wish_Lists extends Backend_Controller {
		public $strings = array(
			'model_title' => 'List',
			'model_name' => 'list',
			'model_code' => 'wish_list',
			'model_class' => 'Wish_List',
			'controller_table_name' => 'wish_lists',
			'controller_name' => 'lists',
			'controller_title' => 'Lists',
			'controller_url' => '/wish/lists',
			'controller_code' => 'wish_lists',
			'controller_class' => 'Wish_Lists',
			'module_name' => 'wish',
			'module_title' => 'Wish',
			'module_path' => '/modules/wish'
		);
		
		public $implement = 'Db_ListBehavior, Db_FormBehavior, Db_FilterBehavior';
		
		public $form_preview_title = 'Wish Lists';
		public $form_create_title = 'New List';
		public $form_edit_title = 'Edit List';
		public $form_model_class = 'Wish_List';
		public $form_not_found_message = 'Record not found';
		public $form_redirect = null;
		
		public $form_edit_save_flash = 'The record has been successfully saved';
		public $form_create_save_flash = 'The record has been successfully added';
		public $form_edit_delete_flash = 'The record has been successfully deleted';
		public $form_edit_save_auto_timestamp = true;

		public $list_model_class = 'Wish_List';
		public $list_record_url = null;
		public $list_name;
		public $list_search_enabled = true;
		public $list_search_fields = array('@title');
		public $list_search_prompt = 'find lists by title';
		public $list_no_setup_link = false;
		public $list_no_interaction = false;
		public $list_no_sorting = false;
		public $list_columns = array();
		public $list_custom_body_cells = null;
		public $list_custom_head_cells = null;
		public $list_no_form = false;
		public $list_render_filters = false;
		
		protected $required_permissions = array('wish:manage_lists');
		
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
			
			$this->app_tab = 'wish';
			$this->app_module_name = 'Wish';

			$this->list_record_url = url('/wish/lists/edit/');
			$this->list_columns = array('title', 'slug', 'items_count', 'is_enabled');
			$this->form_redirect = url('/wish/lists/');
			$this->app_page = 'wish_lists';

			if(Phpr::$router->action == 'reorder') {
				$this->list_record_url = null;
				$this->list_search_enabled = false;
				$this->list_no_interaction = true;
				$this->list_custom_body_cells = PATH_APP . '/modules/wish/controllers/wish_lists/_body_cells.htm';
				$this->list_custom_head_cells = PATH_APP . '/modules/wish/controllers/wish_lists/_head_cells.htm';
			}
			else if(post('add_items_mode')) {
				$this->list_record_url = '#';
				$this->list_name = 'Wish_List_Items_' . Phpr::$router->action . '_list';
				$this->list_data_context = 'items';
				$this->list_model_class = 'Wish_List_Item';
				$this->list_search_fields = array('item_calculated_join.title');
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
				$item = new Wish_List_item();
				$this->filterApplyToModel($item, 'items');
				
				return $item;
			}
			else if(post('add_customers_mode')) {
				$customer = new Shop_Customer();
				$this->filterApplyToModel($customer, 'customers');
				
				return $customer;
			}
			
			$list = new Wish_List();
			
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
				SiteManagement_Site::set_orders(post('list_ids'), post('sort_orders'));
			}
			catch(Exception $ex) {
				Phpr::$response->ajaxReportException($ex, true, true);
			}
		}
		
		public function get_list($id = null) {
			if($id) {
				$list = Wish_List::create()->find($id);
			}
			else {
				$context = $this->formGetUniquePrefix();

				$list = Wish_List::create();
				$list->init_columns_info();
				$list->define_form_fields($context);
			}
			
			return $list;
		}
		
		/* 
		 * Custom events
		 */
		
		protected function onCustomEvent($id = null) {
			$list = null;
			
			if(Phpr::$router->action === 'edit' || Phpr::$router->action === 'create')
				$list = $this->get_list($id);

			Backend::$events->fireEvent(post('custom_event_handler'), $this, $list);
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

				$list = $this->get_list($parent_id);

				$customers = Shop_Customer::create()->where('id in (?)', array($ids))->find_all();

				foreach($customers as $customer) { 
					$list->customers->add($customer, post('edit_session_key'));
				}
			}
			catch(Exception $ex) {
				Phpr::$response->ajaxReportException($ex, true, true);
			}
		}
		
		protected function onUpdateCustomers($parent_id = null) {
			try {
				$list = $this->get_list($parent_id);
				
				$this->viewData['form_model'] = $list;
				
				$this->renderPartial('customers');
			}
			catch(Exception $ex) {
				Phpr::$response->ajaxReportException($ex, true, true);
			}
		}
		
		protected function onRemoveCustomer($parent_id = null) {
			try {
				$list = $this->get_list($parent_id);

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

				$list = $this->get_list($parent_id);
				
				$items = Wish_List_Item::create()->where('id in (?)', array($ids))->find_all();

				foreach($items as $item) { 
					$list->items->add($item, post('edit_session_key'));
				}
			}
			catch(Exception $ex) {
				Phpr::$response->ajaxReportException($ex, true, true);
			}
		}
		
		protected function onUpdateItems($parent_id = null) {
			try {
				$this->viewData['form_model'] = Wish_List::create()->find($parent_id);
				$this->renderPartial('items');
			}
			catch(Exception $ex) {
				Phpr::$response->ajaxReportException($ex, true, true);
			}
		}
		
		protected function onRemoveItem($parent_id = null) {
			try {
				$list = $this->get_list($parent_id);

				$id = post('wish_list_item_id');
				$item = Wish_List_Item::create()->find($id);
				
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