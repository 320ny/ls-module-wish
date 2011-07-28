<?

	define('PATH_MOD_WISHLIST', PATH_APP . '/modules/wishlist');
	
	class Wishlist_Module extends Core_ModuleBase {
		const PATH = PATH_MOD_WISHLIST;
		
		protected function get_info() {
			$info = new Core_ModuleInfo(
				"Wishlist",
				"Adds wishlist functionality to your store.",
				"Limewheel Creative, Inc."
			);
			
			return $info;
		}
		
		public function subscribe_events() {
			Backend::$events->addEvent('shop:onExtendCustomerModel', $this, 'extend_customer_model');
			Backend::$events->addEvent('shop:onExtendCustomerForm', $this, 'extend_customer_form');
		}
		
		public function extend_customer_model($customer) {
			$customer->add_relation('has_and_belongs_to_many', 'wishlists', array('class_name' => 'Wishlist_List', 'join_table' => 'wishlist_lists_customers', 'primary_key' => 'shop_customer_id', 'foreign_key' => 'wishlist_list_id'));
			$customer->define_multi_relation_column('wishlists', 'wishlists', 'Wishlists', '@id')->invisible();
		}
		
		public function extend_customer_form($customer) {
			$customer->add_form_partial(self::PATH . '/partials/_form_field_wishlists.htm')->tab('Wishlists');
		}
		
		public function build_ui_permissions($host) {
			$host->add_field($this, 'lists', 'Manage lists', 'left')->renderAs(frm_checkbox)->comment('View and manage the lists.', 'above');
		}
		
		public function list_tabs($tab_collection) {
			$user = Phpr::$security->getUser();
			
			$tabs = array(
				'lists' => array('lists', 'Lists', 'lists'),
				'settings' => array('settings', 'Settings', 'settings')
			);

			$first_tab = null;
			
			foreach($tabs as $tab_id => $tab_info) {
				if(($tabs[$tab_id][3] = $user->get_permission('wishlist', $tab_info[2])) && !$first_tab)
					$first_tab = $tab_info[0];
			}

			if($first_tab) {
				$tab = $tab_collection->tab('wishlist', 'Wishlist', $first_tab, 30);
				
				foreach($tabs as $tab_id => $tab_info) {
					if($tab_info[3])
						$tab->addSecondLevel($tab_id, $tab_info[1], $tab_info[0]);
				}
			}
		}
		
		public function list_html_editor_configs() {
			return array(
				'wishlist_list_description' => 'List description'
			);
		}
		
		/* awaiting deprecation */
		
		protected function createModuleInfo() {
			return $this->get_info();
		}
		
		public function subscribeEvents() {
			return $this->subscribe_events();
		}
		
		public function buildPermissionsUi($host) {
			return $this->build_ui_permissions($host);
		}
		
		public function listTabs($tab_collection) {
			return $this->list_tabs($tab_collection);
		}
		
		public function listHtmlEditorConfigs() {
			return $this->list_html_editor_configs();
		}
	}
