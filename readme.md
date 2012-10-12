# ls-module-wish
Provides wish lists for your customers.

## Installation
1. Download [Wish](https://github.com/limewheel/ls-module-wish/zipball/master).
1. Create a folder named `wish` in the `modules` directory.
1. Extract all files into the `modules/wish` directory (`modules/wish/readme.md` should exist).
1. Done!

## Usage
Add code to your product page:

```php
<div class="wish-list">
  <? $this->render_partial('shop:product:wish_list') ?>
</div>
```

Create a partial named `shop:product:wish_list` with this code:

```php
<? if($this->customer && count($this->customer->wish_lists)): ?>
  <? $wish_list = $this->customer->wish_lists[0] ?>
  <? if($wish_list->product_exists($product)): ?>
  <?= open_form(array('class' => 'wish-list', 'onsubmit' => "return $(this).sendRequest('wish:on_removeFromList', {
    extraFields: {
      product_id: $('[name=product_id]').val()
    },
    update: {
      '.wish-list': 'shop:product:wish_list'
    }
  })")) ?>
    <input type="hidden" name="wish_list_id" value="<?= $wish_list->id ?>" />
    
    <input type="submit" class='alt ' value="Remove from wish list" />
  <?= close_form() ?>
  <? else: ?>
  <?= open_form(array('class' => 'wish-list', 'onsubmit' => "return $(this).sendRequest('wish:on_addToList', {
    extraFields: {
      product_id: $('[name=product_id]').val(),
      product_quantity: $('[name=product_cart_quantity]').val(),
      product_extras: $('[name=product_extras]').val(),
      product_options: $('[name=product_options]').val()
    },
    update: {
      '.wish-list': 'shop:product:wish_list'
    }
  })")) ?>
    <input type="hidden" name="wish_list_id" value="<?= $wish_list->id ?>" />
    
    <input type="submit" class='alt ' value="Add to wish list" />
  <?= close_form() ?>
  <? endif ?>
<? endif ?>
```
 
Create a `Wish Lists` page that uses the `wish:lists` page action, and use this content:

```php
<? foreach($lists as $list): ?>
  <?= h($list->title) ?>
  
  <? foreach($list->items as $item): ?>
    <?= h($item->product->name) ?>
  <? endforeach ?>
<? endforeach ?>
<?= open_form() ?>
  <input name="title" value="" type="text" />
  <textarea name="description"></textarea>

  <a href="#" onclick="$(this).getForm().sendRequest('wish:on_createList', {
    
  })">Create list</a>
<?= close_form() ?>​
```
