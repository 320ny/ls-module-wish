<?

	class Wishlist_Actions extends Cms_ActionScope {
		public function on_addToList($ajax_mode = true) {
			if($ajax_mode)
				$this->action();
				
			$quantity = trim(post('product_quantity', 1));

			if(!strlen($quantity) || !preg_match('/^[0-9]+$/', $quantity))
				throw new Cms_Exception('Invalid quantity value.');

			if($product_id = post('product_id')) {
				$product = Shop_Product::create()->find($product_id);
				
				if(!$product)
					throw new Cms_Exception('Product not found.');
			}
			
			if($list_id = post('wishlist_list_id')) {
				$list = Wishlist_List::create()->find($list_id);
				
				if(!$list)
					throw new Cms_Exception('Wishlist not found.');
			}
			
			$extras = array();

			foreach(post('product_extras', array()) as $key => $value) {
				if($value == -1)
					continue;

				$extras[$key] = 1;
			}
			
			$extras = base64_encode(serialize($extras));
			
			$options = base64_encode(serialize(post('product_options', array())));
			
			$item = Wishlist_List_Item::create()
				->join('wishlist_lists_items as items', 'items.wishlist_list_item_id=wishlist_list_items.id')
				->where('shop_product_id=:product_id and extras=:extras and options=:options and items.wishlist_list_id=:list_id', array(
					'product_id' => $product->id,
					'extras' => $extras,
					'options' => $options,
					'list_id' => $list->id
				))->find();
			
			if($item) {
				throw new Cms_Exception('Item already exists in that wishlist.');
			}
			
			$item = Wishlist_List_Item::create();
			$item->product = $product;
			$item->quantity = $quantity;
			$item->extras = $extras;
			$item->options = $options;
			$item->save();
			
			$list->items[] = $item;
			$list->save();

			if(!post('no_flash')) {
				$message = post('message', '%s item(s) added to your wishlist.');
				
				Phpr::$session->flash['success'] = sprintf($message, $quantity);
			}
			
			$this->customer = Shop_Customer::create()->find($this->customer->id);

			$redirect = post('redirect');
			
			if($redirect)
				Phpr::$response->redirect($redirect);
		}
		
		public function on_removeFromList($ajax_mode = true) {
			if($ajax_mode)
				$this->action();
			
			if($product_id = post('product_id')) {
				$product = Shop_Product::create()->find($product_id);
				
				if(!$product)
					throw new Cms_Exception('Product not found.');
			}
			
			$items = Wishlist_List_Item::create()->where('shop_product_id=:product_id', array(
				'product_id' => $product->id
			))->find_all();
			
			if(count($items)) {
				foreach($items as $item) {
					$item->delete();
				}
			}
			
			$this->customer = Shop_Customer::create()->find($this->customer->id);
		}
		
		public function on_createList($ajax_mode = true) {
			if($ajax_mode)
				$this->action();
				
			if(!$this->customer)
				throw new Phpr_ApplicationException('Please log in before creating a list.');
				
			$title = $_POST['title']; 
			$slug = Phpr_Inflector::slugify($title);
			
			if(Wishlist_List::create()->find_by_slug($slug)) {
				throw new Phpr_ApplicationException('Chosen title is taken.');
			}

			$list = Wishlist_List::create();
			$list->disable_column_cache();
			$list->init_columns_info();
			$list->validation->focusPrefix = null;
			$list->customers[] = $this->customer;
			$list->slug = $slug;
			$list->is_enabled = 1;
			$list->save($_POST);
			
			Phpr::$session->flash['success'] = 'Your list has been successfully added.';
		}
		
		public function on_updateList($ajax_mode = true) {
			$this->listt(true, true);

			$list = $this->data['list'];
			$list->disable_column_cache();
			$list->init_columns_info();
			$list->validation->focusPrefix = null;
			$list->slug = Phpr_Inflector::slugify($list->title);
			$list->save($_POST);

			Phpr::$session->flash['success'] = 'Your list has been updated.';
		}
		
		public function on_deleteList($ajax_mode = true) {
			$this->listt(true, true);
			
			$list = $this->data['list'];
			$list->delete();

			Phpr::$session->flash['success'] = 'Your list has been deleted.';
		}
		
		public function listt($ajax = false, $edit_mode = false) {
			$this->data['error'] = null;
			
			try {
				$slug = trim($this->request_param(0));
				
				if(!strlen($id))
					throw new Phpr_ApplicationException('List not found.');

				$list = Wishlist_List::create()->find_by_slug($id);
				
				if(!$list)
					throw new Phpr_ApplicationException('List not found.');

				if($edit_mode) {
					if(!$this->customer)
						throw new Phpr_ApplicationException('You have no rights to edit this list.');
				}

				$this->data['list'] = $list;
				$this->page->title = $list->title;
			}
			catch(exception $ex) {
				$this->data['error'] = $ex->getMessage();
				
				if($ajax)
					throw $ex;
			}
		}
		
		public function lists($ajax = false) {
			if($this->customer) {
				$this->data['lists'] = $this->customer->wishlists;
			}
			else {
				$customer_id = $this->request_param(0);
				$customer = Shop_Customer::create()->find($customer_id);
				
				if(!$customer)
					throw new Cms_Exception('Customer not found.');
					
				$this->data['lists'] = $customer->wishlists;
			}
		}
	}
	