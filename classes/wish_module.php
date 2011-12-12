<?

	define('PATH_MOD_WISH', realpath(dirname(__FILE__) . '/../'));
	
	class Wish_Module extends Core_ModuleBase {
		const PATH = PATH_MOD_WISH;
		
		protected function create_module_info() {
			return new Core_ModuleInfo(
				"Wish",
				"Provides wish lists for your store customers.",
				"Limewheel Creative Inc."
			);
		}
		
		public function subscribe_events() {
			Backend::$events->addEvent('shop:onExtendCustomerModel', $this, 'extend_customer_model');
			Backend::$events->addEvent('shop:onExtendCustomerForm', $this, 'extend_customer_form');
		}
		
		public function extend_customer_model($customer) {
			$customer->add_relation('has_and_belongs_to_many', 'wish_lists', array('class_name' => 'Wish_List', 'join_table' => 'wish_lists_customers', 'primary_key' => 'shop_customer_id', 'foreign_key' => 'wish_list_id'));
			$customer->define_multi_relation_column('wish_lists', 'wish_lists', 'Wish Lists', '@id')->invisible();
		}
		
		public function extend_customer_form($customer) {
			$customer->add_form_partial(self::PATH . '/partials/_form_field_wish_lists.htm')->tab('Wish Lists');
		}
		
		public function build_ui_permissions($host) {
			$host->add_field($this, 'manage_lists', 'Manage lists', 'left')->renderAs(frm_checkbox)->comment('View and manage the lists.', 'above');
			$host->add_field($this, 'manage_settings', 'Manage settings', 'left')->renderAs(frm_checkbox)->comment('View and manage the settings.', 'above');
		}
		
		public function list_tabs($tab_collection) {
			$user = Phpr::$security->getUser();
			
			$tabs = array(
				'lists' => array('lists', 'Lists', 'manage_lists'),
				'settings' => array('settings', 'Settings', 'manage_settings')
			);

			$first_tab = null;
			
			foreach($tabs as $tab_id => $tab_info) {
				if(($tabs[$tab_id][3] = $user->get_permission('wish', $tab_info[2])) && !$first_tab)
					$first_tab = $tab_info[0];
			}

			if($first_tab) {
				$tab = $tab_collection->tab('wish', 'Wish', $first_tab, 30);
				
				foreach($tabs as $tab_id => $tab_info) {
					if($tab_info[3])
						$tab->addSecondLevel($tab_id, $tab_info[1], $tab_info[0]);
				}
			}
		}
		
		public function list_html_editor_configs() {
			return array(
				'wish_list_description' => 'List description'
			);
		}
		
		/**
		 * Awaiting deprecation
		 */
		
		protected function createModuleInfo() {
			return $this->create_module_info();
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
