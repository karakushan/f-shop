<div class="fs-select-product" x-show="showSelectProduct"
     x-data="{
                searchQuery: '',
                searchItems: [],
                selected: []
            }
       "
     x-init="()=>{
         $watch('searchQuery', () => {
             $store.FS.post('fs_search_product_admin', {
                 search: searchQuery
             }).then(r=>r.json()).then(result=>{
                if(result.success && typeof result.data!=='undefined'){
                searchItems = result.data
                }
             })
         })
        }"
     x-transition.opacity>
    <div class="fs-select-product__body">
        <div class="fs-select-product__header">
            <h3 class="fs-select-product__title">
                <?php _e('Select product', 'f-shop'); ?>
            </h3>
            <button x-on:click.prevent="showSelectProduct=false"><span class="dashicons dashicons-no-alt"></span></button>
        </div>
        <div class="fs-select-product__search">
            <input type="text"
                   x-model.debounce="searchQuery"
                   class="fs-select-product__search-input" placeholder="<?php _e('Enter product name, ID, SKU','f-shop'); ?>">
        </div>
        <div class="fs-select-product__list">
            <template x-for="searchItem in searchItems" :key="'searchProduct-'+searchItem.id">
                <div class="fs-select-product__item">
                    <div class="fs-select-product__item-checkbox">
                        <input type="checkbox" x-model="selected" :value="JSON.stringify(searchItem)"/>
                    </div>
                    <div class="fs-select-product__item-photo">
                        <img :src="searchItem.thumbnail_url" width="50">
                    </div>
                    <div class="fs-select-product__item-name">
                        <span x-html="searchItem.title"></span>
                    </div>
                </div>
            </template>
        </div>
        <div class="fs-select-product__footer" x-show="selected.length">
            <div x-text="'Выбрано '+selected.length"></div>
            <div>
                <button type="button" class="fs-select-product__add-btn button button-primary button-large"
                        x-on:click.prevent="$dispatch('attach-products', { items: selected.map((item)=>{ return JSON.parse(item) }) })"
                ><?php _e('Add', 'f-shop'); ?></button>
            </div>
        </div>
    </div>
</div>