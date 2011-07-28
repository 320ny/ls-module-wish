# ls-module-wishlist
Extends your store with wishlist functionality.

## Installation
1. Download Wishlist
1. Create a folder named `wishlist` in the `modules` directory.
1. Extract all files into the `modules/wishlist` directory (`modules/wishlist/readme.md` should exist).
1. Done!

## Usage
Add code to your product page:

	<div class="wishlist">
	  <? $this->render_partial('shop:product:wishlist') ?>
	</div>

Create a partial named `shop:product:wishlist` with this code:

	<? if($this->customer && count($this->customer->wishlists)): ?>
	  <? $wishlist = $this->customer->wishlists[0] ?>
	  <? if($wishlist->product_exists($product)): ?>
	  <?= open_form(array('class' => 'wishlist', 'onsubmit' => "return $(this).sendRequest('wishlist:on_removeFromList', {
	    extraFields: {
	      product_id: $('[name=product_id]').val()
	    },
	    update: {
	      '.wishlist': 'shop:product:wishlist'
	    }
	  })")) ?>
	    <input type="hidden" name="wishlist_list_id" value="<?= $wishlist->id ?>" />
	    
	    <input type="submit" class='alt ' value="Remove from Wishlist" />
	  <?= close_form() ?>
	  <? else: ?>
	  <?= open_form(array('class' => 'wishlist', 'onsubmit' => "return $(this).sendRequest('wishlist:on_addToList', {
	    extraFields: {
	      product_id: $('[name=product_id]').val(),
	      product_quantity: $('[name=product_cart_quantity]').val(),
	      product_extras: $('[name=product_extras]').val(),
	      product_options: $('[name=product_options]').val()
	    },
	    update: {
	      '.wishlist': 'shop:product:wishlist'
	    }
	  })")) ?>
	    <input type="hidden" name="wishlist_list_id" value="<?= $wishlist->id ?>" />
	    
	    <input type="submit" class='alt ' value="Add to Wishlist" />
	  <?= close_form() ?>
	  <? endif ?>
	<? endif ?>
  
Create a `Wishlist` page that uses the `wishlist:lists` page action, and use this content:

	<? foreach($lists as $list): ?>
	  <?= h($list->title) ?>
	  
	  <? foreach($list->items as $item): ?>
	    <?= h($item->product->name) ?>
	  <? endforeach ?>
	<? endforeach ?>
	<?= open_form() ?>
	  <input name="title" value="" type="text" />
	  <textarea name="description"></textarea>
	
	  <a href="#" onclick="$(this).getForm().sendRequest('wishlist:on_createList', {
	    
	  })">Create list</a>
	<?= close_form() ?>​
